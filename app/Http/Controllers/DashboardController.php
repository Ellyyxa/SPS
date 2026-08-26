<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = $user->tasks();

        $totalTasks = (clone $tasks)->count();
        $completedTasks = (clone $tasks)->where('status', 'Completed')->count();
        $pendingTasks = (clone $tasks)->where('status', 'Pending')->count();
        $todayTasks = (clone $tasks)
            ->whereDate('due_date', today())
            ->orderByDesc('priority_score')
            ->get();
        $upcomingTasks = (clone $tasks)
            ->where('status', 'Pending')
            ->whereDate('due_date', '>=', today())
            ->orderByDesc('priority_score')
            ->orderBy('due_date')
            ->take(4)
            ->get();
        $todayMood = $user->moods()->whereDate('date', today())->first();
        $recentNotifications = $user->notifications()->latest()->take(3)->get();
        $notificationCount = $user->notifications()->count();

        return view('dashboard', compact(
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'todayTasks',
            'upcomingTasks',
            'todayMood',
            'recentNotifications',
            'notificationCount'
        ));
    }
}
