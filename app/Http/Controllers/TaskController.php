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
        ->latest()
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


    auth()->user()->tasks()->create([
        'title' => $request->title,
        'description' => $request->description,
        'due_date' => $request->due_date,
        'difficulty' => $request->difficulty,
        'priority' => $request->priority,
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
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
    $request->validate([
        'title' => 'required',
        'due_date' => 'required',
    ]);


    $task->update([
        'title' => $request->title,
        'description' => $request->description,
        'due_date' => $request->due_date,
        'priority' => $request->priority,
    ]);


    return redirect()->route('tasks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
{
    // Pastikan hanya pemilik task boleh delete
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $task->delete();

    return redirect()->route('tasks.index')
                     ->with('success', 'Task deleted successfully.');
}

public function complete(Task $task)
{
    // Pastikan hanya pemilik task boleh ubah status
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
