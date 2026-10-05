<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\User;
use Illuminate\Http\Request;

class AdminEmotionController extends Controller
{
    public function index(Request $request)
    {
        $moodQuery = Mood::query()->whereHas('user', fn ($query) => $query->where('role', 'student'));
        $moods = (clone $moodQuery)
            ->with('user:id,name,student_id,course,profile_photo_path')
            ->when($request->filled('mood'), fn ($query) => $query->where('mood', $request->string('mood')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('date', $request->date))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->string('search').'%')->orWhere('student_id', 'like', '%'.$request->string('search').'%')))
            ->latest('date')->latest('id')->paginate(15)->withQueryString();

        $distribution = (clone $moodQuery)->selectRaw('mood, count(*) as total')->groupBy('mood')->pluck('total', 'mood');
        $todayDistribution = (clone $moodQuery)->whereDate('date', today())->selectRaw('mood, count(*) as total')->groupBy('mood')->pluck('total', 'mood');
        $students = User::where('role', 'student')->with(['moods' => fn ($query) => $query->latest('date')->latest('id')->take(1)])->orderBy('name')->get();

        return view('admin.emotions.index', compact('moods', 'distribution', 'todayDistribution', 'students'));
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'student', 404);
        $moods = $user->moods()->latest('date')->latest('id')->get();
        $trend = $moods->sortBy('date')->values()->map(fn ($mood) => ['date' => $mood->date->toDateString(), 'mood' => $mood->mood]);

        return view('admin.emotions.show', compact('user', 'moods', 'trend'));
    }
}
