<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendEveningEmotionReminders extends Command
{
    protected $signature = 'sps:send-evening-emotion-reminders';
    protected $description = 'Send optional private in-app reminders to opted-in students without today’s mood.';

    public function handle(): int
    {
        $date = Carbon::today('Asia/Kuala_Lumpur')->toDateString();
        $created = 0;

        User::where('role', 'student')->where('evening_emotion_reminder', true)
            ->whereDoesntHave('moods', fn ($query) => $query->whereDate('date', $date))
            ->select('id')->chunkById(100, function ($students) use ($date, &$created) {
                foreach ($students as $student) {
                    $created += DB::transaction(function () use ($student, $date) {
                        // Recheck after taking the same user lock used by mood submission.
                        $user = User::whereKey($student->id)->lockForUpdate()->first();
                        if (! $user || $user->role !== 'student' || ! $user->evening_emotion_reminder ||
                            $user->moods()->whereDate('date', $date)->exists()) {
                            return 0;
                        }

                        $notification = Notification::firstOrCreate([
                            'deduplication_key' => 'evening_emotion_reminder:'.$user->id.':'.$date,
                        ], [
                            'admin_id' => null,
                            'user_id' => $user->id,
                            'task_id' => null,
                            'broadcast_id' => null,
                            'notification_type' => 'evening_emotion_reminder',
                            'title' => 'Evening Emotion Check-in',
                            'message' => "How was your day? Take a moment to reflect whenever you're ready.",
                            'is_read' => false,
                        ]);

                        return $notification->wasRecentlyCreated ? 1 : 0;
                    }, 3);
                }
            });

        $this->info("Created {$created} evening emotion reminder(s) for {$date}.");
        return self::SUCCESS;
    }
}
