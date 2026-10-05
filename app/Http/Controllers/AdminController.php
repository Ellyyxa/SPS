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

        $taskCompletion = [
            'completed' => $completedTasks,
            'pending' => $pendingTasks,
        ];
        $todayMoodDistribution = Mood::whereDate('date', today())
            ->selectRaw('mood, count(*) as total')
            ->groupBy('mood')
            ->pluck('total', 'mood');
        $recentMoods = Mood::with('user:id,name,student_id,profile_photo_path')
            ->whereHas('user', fn ($query) => $query->where('role', 'student'))
            ->latest('date')
            ->latest('id')
            ->take(6)
            ->get();
        $recentNotifications = Notification::with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'totalNotifications',
            'todayMoods',
            'taskCompletion',
            'todayMoodDistribution',
            'recentMoods',
            'recentNotifications'
        ));
    }
}
