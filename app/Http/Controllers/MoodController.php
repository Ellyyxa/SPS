<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $moods = auth()->user()
            ->moods()
            ->latest()
            ->get();

        return view('moods.index', compact('moods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('moods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, GamificationService $gamification)
{
    $request->validate([
        'mood' => 'required',
        'note' => 'nullable',
    ]);

    $user = $request->user();
    $today = now()->toDateString();

    // Friendly check before attempting to create today's mood.
    $todayMood = $user->moods()
        ->whereDate('date', $today)
        ->first();

    if ($todayMood) {
        return redirect()->route('dashboard')
            ->with('error', 'You have already updated your mood today.');
    }

    try {
        DB::transaction(function () use (
            $request,
            $user,
            $today,
            $gamification
        ) {
            $mood = $user->moods()->create([
                'mood' => $request->mood,
                'note' => $request->note,
                'date' => $today,
            ]);

            // Daily Emotion Check-In: +5 XP once.
            $awarded = $gamification->award(
                $user,
                5,
                'Daily Emotion Check-In',
                'mood_checkin',
                $mood->id,
                'mood_checkin:' . $mood->id
            );

            // A legitimate reward qualifies today as a streak day.
            if ($awarded) {
                $gamification->qualifyDay(
                    $user,
                    Carbon::today()
                );
            }
        });
    } catch (QueryException $exception) {

        // MySQL duplicate-entry error.
        if (
            ($exception->errorInfo[1] ?? null) === 1062 ||
            ($exception->errorInfo[0] ?? null) === '23000'
        ) {
            return redirect()->route('dashboard')
                ->with(
                    'error',
                    'You have already updated your mood today.'
                );
        }

        // Do not hide unrelated database errors.
        throw $exception;
    }

    return redirect()->route('dashboard')
        ->with('success', 'Mood saved successfully.');
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