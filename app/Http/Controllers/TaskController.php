<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $tasks = auth()->user()
        ->tasks()
        ->orderBy('priority_score', 'desc')
        ->get();

    return view('tasks.index', compact('tasks'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'title' => 'required',
        'due_date' => 'required',
    ]);


    $task = auth()->user()->tasks()->create([
    'title' => $request->title,
    'description' => $request->description,
    'due_date' => $request->due_date,
    'difficulty' => $request->difficulty,
    'priority' => $request->priority,
]);


$task->update([
    'priority_score' => $this->calculatePriorityScore($task),
]);


    return redirect()->route('tasks.index');
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
    public function edit(Task $task)
{
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    return view('tasks.edit', compact('task'));
}

    /**
     * Update the specified resource in storage.
     */
    /**
 * Update the specified resource in storage.
 */
public function update(Request $request, Task $task)
{
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'title' => 'required',
        'due_date' => 'required',
    ]);

    $task->update([
        'title' => $request->title,
        'description' => $request->description,
        'due_date' => $request->due_date,
        'priority' => $request->priority,
        'difficulty' => $request->difficulty,
    ]);

    $task->update([
    'priority_score' => $this->calculatePriorityScore($task),
]);

    return redirect()->route('tasks.index')
                     ->with('success', 'Task updated successfully.');
}

private function calculatePriorityScore($task)
{
    $score = 0;


    // 1. Due date scoring
    $daysLeft = now()->diffInDays($task->due_date, false);


    if ($daysLeft <= 1) {
        $score += 50;
    } 
    elseif ($daysLeft <= 3) {
        $score += 30;
    } 
    else {
        $score += 10;
    }



    // 2. Priority level scoring
    if ($task->priority == 'High') {
        $score += 30;
    }
    elseif ($task->priority == 'Medium') {
        $score += 20;
    }
    else {
        $score += 10;
    }



    // 3. Difficulty scoring
    $score += $task->difficulty * 10;



    return $score;
}
/**
 * Remove the specified resource from storage.
 */
public function destroy(Task $task)
{
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $task->delete();

    return redirect()->route('tasks.index')
                     ->with('success', 'Task deleted successfully.');
}


/**
 * Mark task as completed.
 */
public function complete(Task $task)
{
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $task->update([
        'status' => 'Completed',
    ]);

    return redirect()->route('tasks.index')
                     ->with('success', 'Task marked as completed.');
}

}
