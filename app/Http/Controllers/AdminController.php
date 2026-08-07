<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Mood;
use App\Models\Notification;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = User::where('role', 'student')->count();

        $totalTasks = Task::count();

        $completedTasks = Task::where('status', 'Completed')->count();

        $pendingTasks = Task::where('status', 'Pending')->count();

        $totalNotifications = Notification::count();

        $todayMoods = Mood::whereDate('date', today())->count();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'totalNotifications',
            'todayMoods'
        ));
    }
}