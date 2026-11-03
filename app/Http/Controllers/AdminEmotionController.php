<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminEmotionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'period' => ['sometimes', 'required', 'in:date,7,30,year,custom'],
            'date' => ['exclude_if:period,7,30,year,custom', 'nullable', 'date_format:Y-m-d', 'before_or_equal:'.today('Asia/Kuala_Lumpur')->toDateString()],
            'start_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d'],
            'end_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d', 'after_or_equal:start_date', 'before_or_equal:'.today('Asia/Kuala_Lumpur')->toDateString()],
            'programme' => ['nullable', 'string', 'in:SVM,DVM'],
            'course' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:255'],
        ]);
        $period = $filters['period'] ?? 'date';
        $today = Carbon::today('Asia/Kuala_Lumpur');
        $selectedDate = $filters['date'] ?? $today->toDateString();
        $end = $period === 'date' ? Carbon::parse($selectedDate, 'Asia/Kuala_Lumpur') : $today->copy();
        $start = match ($period) {
            '7' => $today->copy()->subDays(6),
            '30' => $today->copy()->subDays(29),
            'year' => $today->copy()->startOfYear(),
            'custom' => Carbon::parse($filters['start_date'], 'Asia/Kuala_Lumpur'),
            default => $end->copy(),
        };
        if ($period === 'custom') {
            $end = Carbon::parse($filters['end_date'], 'Asia/Kuala_Lumpur');
            if ($end->gt($start->copy()->addYearsNoOverflow(5)->subDay())) {
                throw ValidationException::withMessages(['end_date' => 'Choose a range of no more than five years.']);
            }
        }
        $asOf = now('Asia/Kuala_Lumpur');
        $cohort = User::query()->where('role', 'student');
        foreach (['programme', 'course', 'semester'] as $field) {
            if (! empty($filters[$field])) {
                $cohort->where($field, $filters[$field]);
            }
        }
        $totalStudents = (clone $cohort)->count();
        $moodQuery = Mood::query()->whereIn('user_id', (clone $cohort)->select('id'))
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->copy()->addDay()->toDateString());
        $participants = (clone $moodQuery)->distinct()->count('user_id');
        $overview = [
            'total' => $totalStudents,
            'participants' => $participants,
            'missing' => max(0, $totalStudents - $participants),
            'percentage' => $totalStudents ? round($participants / $totalStudents * 100, 1) : 0,
        ];
        $moods = (clone $moodQuery)
            ->select(['moods.id', 'moods.user_id', 'moods.mood', 'moods.date'])
            ->with('user:id,name,student_id,course,profile_photo_path')
            ->latest('date')->latest('id')->paginate(15)->withQueryString();

        $counts = (clone $moodQuery)->selectRaw('mood, count(*) as total')->groupBy('mood')->pluck('total', 'mood');
        $distribution = collect(['Happy', 'Neutral', 'Sad', 'Stress', 'Angry'])
            ->mapWithKeys(fn ($category) => [$category => (int) ($counts[$category] ?? 0)]);
        $entries = (clone $moodQuery)->count();
        $monthly = $period === 'year' || $start->diffInDays($end) > 30;
        // Only aggregated rows leave SQL. DATE/strftime handle SQLite test date casts;
        // MySQL uses DATE_FORMAT for month buckets. Range predicates remain index-friendly.
        $bucketSql = $monthly
            ? (Mood::query()->getConnection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', date)" : "DATE_FORMAT(date, '%Y-%m')")
            : 'DATE(date)';
        $categoryBuckets = (clone $moodQuery)->selectRaw("$bucketSql as bucket, mood, count(*) as total")
            ->groupByRaw("$bucketSql, mood")->get()->groupBy('bucket');
        $participationBuckets = (clone $moodQuery)->selectRaw("$bucketSql as bucket, count(*) as entries, count(distinct user_id) as participants")
            ->groupByRaw($bucketSql)->get()->keyBy('bucket');
        $series = [];
        $cursor = $monthly ? $start->copy()->startOfMonth() : $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format($monthly ? 'Y-m' : 'Y-m-d');
            $bucketCounts = $categoryBuckets->get($key, collect())->pluck('total', 'mood');
            $participation = $participationBuckets->get($key);
            $series[] = [
                'date' => $key, 'label' => $cursor->format($monthly ? 'M Y' : 'd M Y'),
                'counts' => $distribution->mapWithKeys(fn ($count, $category) => [$category => (int) ($bucketCounts[$category] ?? 0)])->all(),
                'entries' => (int) ($participation?->entries ?? 0),
                'participants' => (int) ($participation?->participants ?? 0),
            ];
            $monthly ? $cursor->addMonth() : $cursor->addDay();
        }
        $analytics = ['mode' => $monthly ? 'monthly' : 'daily', 'categories' => $distribution->keys()->all(), 'series' => $series];
        $courses = User::where('role', 'student')->whereNotNull('course')->where('course', '<>', '')
            ->distinct()->orderBy('course')->pluck('course');
        $semesters = User::where('role', 'student')->whereNotNull('semester')->where('semester', '<>', '')
            ->distinct()->orderBy('semester')->pluck('semester');

        return view('admin.emotions.index', compact('moods', 'distribution', 'overview', 'selectedDate', 'asOf', 'courses', 'semesters', 'period', 'start', 'end', 'entries', 'analytics'));
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'student', 404);
        $moods = $user->moods()->select(['id', 'user_id', 'mood', 'date'])
            ->latest('date')->latest('id')->get();
        $trend = $moods->sortBy('date')->values()->map(fn ($mood) => ['date' => $mood->date->toDateString(), 'mood' => $mood->mood]);

        return view('admin.emotions.show', compact('user', 'moods', 'trend'));
    }
}
