<?php

namespace App\Services;

use App\Models\GamificationProfile;
use App\Models\GamificationStreakDay;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\XpTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public const LEVELS = [
        1 => ['name' => 'Beginner', 'xp' => 0],
        2 => ['name' => 'Learning', 'xp' => 500],
        3 => ['name' => 'Focused', 'xp' => 1500],
        4 => ['name' => 'Achiever', 'xp' => 3500],
        5 => ['name' => 'Productivity Master', 'xp' => 7000],
    ];

    public function profile(User $user): GamificationProfile
    {
        return GamificationProfile::firstOrCreate([
            'user_id' => $user->id,
        ]);
    }

    public function levelFor(int $xp): array
    {
        $levelNumber = 1;

        foreach (self::LEVELS as $number => $level) {
            if ($xp >= $level['xp']) {
                $levelNumber = $number;
            }
        }

        return [
            'number' => $levelNumber,
        ] + self::LEVELS[$levelNumber];
    }

    public function progress(User $user): array
{
    $profile = $this->profile($user);

    // Reset the displayed current streak when a day has been missed.
    // Longest streak is preserved.
    if (
        $profile->current_streak > 0 &&
        $profile->last_qualifying_activity_date &&
        $profile->last_qualifying_activity_date->lt(
            Carbon::today()->subDay()
        )
    ) {
        $profile->current_streak = 0;
        $profile->save();
    }

    $level = $this->levelFor($profile->total_xp);

    $nextNumber = $level['number'] + 1;

    $next = isset(self::LEVELS[$nextNumber])
        ? ['number' => $nextNumber] + self::LEVELS[$nextNumber]
        : null;

    $percent = $next
        ? round(
            (($profile->total_xp - $level['xp']) /
            max(1, $next['xp'] - $level['xp'])) * 100
        )
        : 100;

    return compact('profile', 'level', 'next', 'percent');
}

    public function award(
        User $user,
        int $amount,
        string $reason,
        string $source,
        ?int $sourceId,
        string $rewardKey,
        bool $checkAchievements = true
    ): bool {
        return DB::transaction(function () use (
            $user,
            $amount,
            $reason,
            $source,
            $sourceId,
            $rewardKey,
            $checkAchievements
        ) {
            $profile = $this->profile($user);

            $profile = GamificationProfile::whereKey($profile->id)
                ->lockForUpdate()
                ->first();

            if (XpTransaction::where('reward_key', $rewardKey)->exists()) {
                return false;
            }

            $beforeLevel = $this->levelFor($profile->total_xp)['number'];

            XpTransaction::create([
                'user_id' => $user->id,
                'source_type' => $source,
                'source_id' => $sourceId,
                'xp_amount' => $amount,
                'reason' => $reason,
                'reward_key' => $rewardKey,
            ]);

            $profile->increment('total_xp', $amount);
            $profile->refresh();

            $afterLevel = $this->levelFor($profile->total_xp)['number'];

            if (
                $afterLevel > $beforeLevel &&
                ! app()->runningInConsole()
            ) {
                session()->flash('level_up', [
                    'level' => $afterLevel,
                    'name' => self::LEVELS[$afterLevel]['name'],
                ]);
            }

            if ($checkAchievements) {
                $this->checkAchievements($user, $profile);
            }

            return true;
        });
    }

    public function qualifyDay(User $user, Carbon $date): void
    {
        DB::transaction(function () use ($user, $date) {
            $profile = $this->profile($user);

            $profile = GamificationProfile::whereKey($profile->id)
                ->lockForUpdate()
                ->first();

            $streakDay = GamificationStreakDay::firstOrCreate([
                'user_id' => $user->id,
                'activity_date' => $date->toDateString(),
            ]);

            if (! $streakDay->wasRecentlyCreated) {
                return;
            }

            $previousDate = $profile->last_qualifying_activity_date;

            $profile->current_streak =
                $previousDate &&
                $previousDate->copy()->addDay()->isSameDay($date)
                    ? $profile->current_streak + 1
                    : 1;

            $profile->longest_streak = max(
                $profile->longest_streak,
                $profile->current_streak
            );

            $profile->last_qualifying_activity_date = $date;
            $profile->save();

            if (
                $profile->current_streak >= 7 &&
                $this->unlock($user, 'consistency_star')
            ) {
                $this->award(
                    $user,
                    75,
                    'Consistency Star achievement',
                    'achievement',
                    null,
                    'achievement_consistency_star:' . $user->id,
                    false
                );

                // Refresh because the +75 XP may cross a level threshold.
                $profile->refresh();
            }

            $this->checkAchievements($user, $profile);
        });
    }

    public function checkAchievements(
        User $user,
        ?GamificationProfile $profile = null
    ): void {
        $profile ??= $this->profile($user);

        $completedTaskCount = XpTransaction::where('user_id', $user->id)
            ->where('source_type', 'task_completion')
            ->count();

        if ($completedTaskCount >= 1) {
            $this->unlock($user, 'first_step');
        }

        if ($completedTaskCount >= 10) {
            $this->unlock($user, 'getting_things_done');
        }

        if ($profile->total_xp >= 7000) {
            $this->unlock($user, 'productivity_master');
        }
    }

    private function unlock(User $user, string $achievementKey): bool
    {
        return UserAchievement::firstOrCreate(
            [
                'user_id' => $user->id,
                'achievement_key' => $achievementKey,
            ],
            [
                'unlocked_at' => now(),
            ]
        )->wasRecentlyCreated;
    }
}