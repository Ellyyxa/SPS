<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, GamificationService $gamification)
    {
        $user = $request->user();
        $tasks = $user->tasks();

        $totalTasks = (clone $tasks)->count();

        $completedTasks = (clone $tasks)
            ->where('status', 'Completed')
            ->count();

        $pendingTasks = (clone $tasks)
            ->where('status', 'Pending')
            ->count();

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

        $todayMood = $user->moods()
            ->whereDate('date', today())
            ->first();

        $recentNotifications = $user->notifications()
            ->latest()
            ->take(3)
            ->get();

        $notificationCount = $user->notifications()->count();

        /*
        |--------------------------------------------------------------------------
        | Gamification / FocusBuddy
        |--------------------------------------------------------------------------
        */

        $gamificationProgress = $gamification->progress($user);

        $gamificationProfile = $gamificationProgress['profile'];
        $gamificationLevel = $gamificationProgress['level'];
        $gamificationNextLevel = $gamificationProgress['next'];
        $gamificationPercent = $gamificationProgress['percent'];

        $penguinImage = asset(
            'images/gamification/penguin-level-' .
            $gamificationLevel['number'] .
            '.png'
        );

        return view('dashboard', compact(
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'todayTasks',
            'upcomingTasks',
            'todayMood',
            'recentNotifications',
            'notificationCount',

            // Gamification
            'gamificationProfile',
            'gamificationLevel',
            'gamificationNextLevel',
            'gamificationPercent',
            'penguinImage'
        ));
    }
}