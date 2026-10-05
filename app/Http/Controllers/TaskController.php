<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Calculate Smart Priority Score.
     */
    private function calculatePriorityScore($task)
    {
        $score = 0;

        // 1. Due date scoring
        $daysLeft = now()->diffInDays($task->due_date, false);

        if ($daysLeft <= 1) {
            $score += 50;
        } elseif ($daysLeft <= 3) {
            $score += 30;
        } else {
            $score += 10;
        }

        // 2. Priority level scoring
        if ($task->priority == 'High') {
            $score += 30;
        } elseif ($task->priority == 'Medium') {
            $score += 20;
        } else {
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
     * Mark task as completed and award gamification XP.
     */
    public function complete(Task $task, GamificationService $gamification)
{
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $user = auth()->user();
    $alreadyCompleted = false;

    DB::transaction(function () use (
        $task,
        $user,
        $gamification,
        &$alreadyCompleted
    ) {
        // Retrieve and lock the latest version of this task.
        $lockedTask = Task::whereKey($task->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Ownership check again using the locked record.
        if ($lockedTask->user_id !== $user->id) {
            abort(403);
        }

        // Another request may already have completed this task.
        if ($lockedTask->status === 'Completed') {
            $alreadyCompleted = true;
            return;
        }

        $lockedTask->update([
            'status' => 'Completed',
        ]);

        // Base reward: +30 XP once per task.
        $awarded = $gamification->award(
            $user,
            30,
            'Task completed',
            'task_completion',
            $lockedTask->id,
            'task_completion:' . $lockedTask->id
        );

        // Early completion: additional +15 XP.
        // "Early" means strictly before the due date.
        if (
            $awarded &&
            Carbon::today()->lt(
                Carbon::parse($lockedTask->due_date)->startOfDay()
            )
        ) {
            $gamification->award(
                $user,
                15,
                'Task completed before due date',
                'task_early_bonus',
                $lockedTask->id,
                'task_early_bonus:' . $lockedTask->id
            );
        }

        // Only the first legitimate completion qualifies today.
        if ($awarded) {
            $gamification->qualifyDay(
                $user,
                Carbon::today()
            );
        }
    });

    if ($alreadyCompleted) {
        return redirect()->route('tasks.index')
            ->with('success', 'Task is already completed.');
    }

    return redirect()->route('tasks.index')
        ->with('success', 'Task marked as completed.');
}
}