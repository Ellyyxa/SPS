<?php

namespace App\Console\Commands;

use App\Mail\TaskDueReminderMail;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTaskDueReminders extends Command
{
    protected $signature = 'sps:send-task-due-reminders';
    protected $description = 'Send private SPS reminders for pending student tasks due in three days.';

    public function handle(): int
    {
        $dueDate = now()->addDays(3)->toDateString();
        $created = 0;

        Task::with('user')->where('status', 'Pending')->whereDate('due_date', $dueDate)
            ->whereHas('user', fn ($query) => $query->where('role', 'student'))
            ->orderBy('id')->chunkById(100, function ($tasks) use (&$created) {
                foreach ($tasks as $task) {
                    $key = 'task_due_3_days:'.$task->id;
                    $notification = Notification::firstOrCreate(
                        ['deduplication_key' => $key],
                        ['admin_id' => null, 'user_id' => $task->user_id, 'task_id' => $task->id, 'notification_type' => 'task_due_reminder', 'title' => 'Task due in 3 days: '.$task->title, 'message' => sprintf('Your %s priority task "%s" is due on %s. Please plan time to complete it.', $task->priority, $task->title, \Carbon\Carbon::parse($task->due_date)->format('d M Y')), 'is_read' => false]
                    );

                    if (! $notification->wasRecentlyCreated) {
                        continue;
                    }

                    $created++;
                    try {
                        Mail::to($task->user->email)->send(new TaskDueReminderMail($task));
                    } catch (\Throwable $exception) {
                        Log::error('SPS task reminder email failed.', ['task_id' => $task->id, 'user_id' => $task->user_id, 'error' => $exception->getMessage()]);
                    }
                }
            });

        $this->info("Created {$created} task due-date reminder(s) for {$dueDate}.");
        return self::SUCCESS;
    }
}
