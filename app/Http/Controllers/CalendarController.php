<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Show due dates from the authenticated student's existing tasks.
     */
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()
            ->orderBy('due_date')
            ->get(['id', 'title', 'description', 'due_date', 'priority', 'difficulty', 'status', 'priority_score']);

        return view('student.calendar', compact('tasks'));
    }
}
