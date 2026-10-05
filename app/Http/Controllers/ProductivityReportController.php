<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;

class ProductivityReportController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $students = User::where('role', 'student')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($inner) => $inner->where('name', 'like', '%'.$request->string('search').'%')->orWhere('student_id', 'like', '%'.$request->string('search').'%')))
            ->withCount([
                'tasks',
                'tasks as completed_tasks' => function ($query) {
                    $query->where('status', 'Completed');
                },
                'tasks as pending_tasks' => function ($query) {
                    $query->where('status', 'Pending');
                },
            ])
            ->withAvg('tasks', 'priority_score')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'students' => User::where('role', 'student')->count(),
            'tasks' => Task::count(),
            'completed' => Task::where('status', 'Completed')->count(),
            'pending' => Task::where('status', 'Pending')->count(),
        ];

        return view('admin.productivity', compact('students', 'summary'));
    }
}
