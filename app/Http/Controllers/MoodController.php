<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
