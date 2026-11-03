<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');
        $filters = $request->validate([
            'period' => ['sometimes', 'required', Rule::in(['7', '30', 'year', 'custom'])],
            'start_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d'],
            'end_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d',
                'after_or_equal:start_date', 'before_or_equal:'.$today->toDateString()],
        ]);
        $period = $filters['period'] ?? '7';
        $end = $today->copy();
        $start = match ($period) {
            '30' => $today->copy()->subDays(29),
            'year' => $today->copy()->startOfYear(),
            'custom' => Carbon::parse($filters['start_date'], 'Asia/Kuala_Lumpur'),
            default => $today->copy()->subDays(6),
        };
        if ($period === 'custom') {
            $end = Carbon::parse($filters['end_date'], 'Asia/Kuala_Lumpur');
            if ($end->gt($start->copy()->addYears(5)->subDay())) {
                throw ValidationException::withMessages([
                    'end_date' => 'Choose a range of no more than five years.',
                ]);
            }
        }

        $query = $request->user()->moods()
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());
        $moods = (clone $query)->orderByDesc('date')->orderByDesc('id')
            ->paginate(15)->withQueryString();
        // At most five years of date/category pairs; notes never enter chart data.
        $chartRecords = (clone $query)->get(['date', 'mood']);
        $categories = ['Happy', 'Neutral', 'Sad', 'Stress', 'Angry'];
        $monthly = $period === 'year' || $start->diffInDays($end) > 30;
        $series = [];

        if ($monthly) {
            $buckets = [];
            foreach ($chartRecords as $record) {
                $month = $record->date->format('Y-m');
                $buckets[$month][$record->mood] = ($buckets[$month][$record->mood] ?? 0) + 1;
            }
            for ($month = $start->copy()->startOfMonth(); $month->lte($end); $month->addMonth()) {
                $series[] = ['date' => $month->toDateString(), 'label' => $month->format('M Y'),
                    'counts' => array_replace(array_fill_keys($categories, 0), $buckets[$month->format('Y-m')] ?? [])];
            }
        } else {
            $byDate = $chartRecords->keyBy(fn ($record) => $record->date->toDateString());
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $series[] = ['date' => $day->toDateString(), 'label' => $day->format('d M Y'),
                    'mood' => $byDate->get($day->toDateString())?->mood];
            }
        }
        $historyChart = ['mode' => $monthly ? 'monthly' : 'daily', 'categories' => $categories,
            'colors' => ['Happy' => '#7c3aed', 'Neutral' => '#3b82f6', 'Sad' => '#eab308', 'Stress' => '#ea580c', 'Angry' => '#dc2626'],
            'series' => $series];
        $hasTodayMood = $request->user()->moods()->whereDate('date', $today->toDateString())->exists();

        return view('moods.index', compact('moods', 'period', 'start', 'end', 'historyChart', 'hasTodayMood'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');
        $yesterday = $today->copy()->subDay();
        $recordedDates = $request->user()->moods()
            ->whereDate('date', '>=', $yesterday->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->get(['date'])->map(fn ($mood) => $mood->date->toDateString());
        $checkInDates = collect(['Today' => $today, 'Yesterday' => $yesterday])
            ->map(fn ($date, $label) => [
                'value' => $date->toDateString(),
                'label' => $label,
                'display' => $date->format('d M Y'),
                'recorded' => $recordedDates->contains($date->toDateString()),
            ])->values();

        return view('moods.create', compact('checkInDates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, GamificationService $gamification)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'student', 403);

        $today = Carbon::today('Asia/Kuala_Lumpur');
        $validated = $request->validate([
            'date' => ['sometimes', 'required', 'date_format:Y-m-d', Rule::in([
                $today->toDateString(), $today->copy()->subDay()->toDateString(),
            ])],
            'mood' => ['required', 'string', Rule::in(['Happy', 'Neutral', 'Sad', 'Stress', 'Angry'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'date.in' => 'You can check in only for today or yesterday in Malaysia time.',
            'date.date_format' => 'Please provide a check-in date in YYYY-MM-DD format.',
            'mood.in' => 'Please choose Happy, Neutral, Sad, Stress, or Angry.',
        ]);
        $date = $validated['date'] ?? $today->toDateString();

        try {
            $created = DB::transaction(function () use ($user, $validated, $date, $gamification) {
                // Serialize submissions for this student, including different represented days.
                $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();

                if ($user->moods()->whereDate('date', $date)->exists()) {
                    return false;
                }

                $mood = $user->moods()->create([
                    'mood' => $validated['mood'],
                    'note' => $validated['note'] ?? null,
                    'date' => $date,
                ]);

                // Date-based identity survives replacement of the mood record.
                $awarded = $gamification->award(
                    $user, 5, 'Daily Emotion Check-In', 'mood_checkin', $mood->id,
                    'mood_checkin_date:'.$user->id.':'.$date
                );

                if ($awarded) {
                    $gamification->qualifyDay($user, Carbon::parse($date, 'Asia/Kuala_Lumpur'));
                }

                return true;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            // Only translate a real same-user/date collision; expose unrelated failures.
            if (! $user->moods()->whereDate('date', $date)->exists()) {
                throw $exception;
            }
            $created = false;
        }

        return redirect()->route('dashboard')->with(
            $created ? 'success' : 'error',
            $created ? 'Mood saved successfully.' : 'You have already recorded a mood for '.$date.'.'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
