<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;

class ProductivityReportController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')
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
            ->get();

        return view('admin.productivity', compact('students'));
    }
}