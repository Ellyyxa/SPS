<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mood;

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
    public function store(Request $request)
{
    $request->validate([
        'mood' => 'required',
        'note' => 'nullable',
    ]);

    $todayMood = auth()->user()->moods()
    ->whereDate('date', today())
    ->first();

if ($todayMood) {
    return redirect()->route('dashboard')
        ->with('error', 'You have already updated your mood today.');
}

    auth()->user()->moods()->create([

        'mood' => $request->mood,

        'note' => $request->note,

        'date' => now()->toDateString(),

    ]);


    return redirect()->route('moods.index')
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
