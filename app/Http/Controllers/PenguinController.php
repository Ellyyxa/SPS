<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;

class PenguinController extends Controller
{
    /**
     * Display the student's My Penguin page.
     */
    public function index(GamificationService $gamification)
    {
        $user = auth()->user();

        // Get real gamification progress.
        $progress = $gamification->progress($user);

        $profile = $progress['profile'];
        $level = $progress['level'];
        $next = $progress['next'];
        $percent = $progress['percent'];

        // Select the correct penguin according to the current level.
        $penguinImage = asset(
            'images/gamification/penguin-level-' . $level['number'] . '.png'
        );

        // All achievements shown on My Penguin.
        $achievementDefinitions = [
            'first_step' => [
                'name' => 'First Step',
                'description' => 'Complete your first task.',
            ],

            'getting_things_done' => [
                'name' => 'Getting Things Done',
                'description' => 'Complete 10 tasks.',
            ],

            'consistency_star' => [
                'name' => 'Consistency Star',
                'description' => 'Reach a 7-day productivity streak.',
            ],

            'productivity_master' => [
                'name' => 'Productivity Master',
                'description' => 'Reach Level 5.',
            ],
        ];

        // Get achievements already unlocked by this student.
        $unlockedAchievements = $user->achievements()
            ->get()
            ->keyBy('achievement_key');

        $achievements = collect($achievementDefinitions)
            ->map(function ($achievement, $key) use ($unlockedAchievements) {
                $unlocked = $unlockedAchievements->get($key);

                return [
                    'key' => $key,
                    'name' => $achievement['name'],
                    'description' => $achievement['description'],
                    'unlocked' => $unlocked !== null,
                    'unlocked_at' => $unlocked?->unlocked_at,
                ];
            });

        return view('student.penguin', [
            'profile' => $profile,
            'level' => $level,
            'nextLevel' => $next,
            'progressPercent' => $percent,
            'penguinImage' => $penguinImage,
            'achievements' => $achievements,
        ]);
    }
}