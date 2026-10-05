<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Task $task)
    {
    }

    public function build(): self
    {
        return $this->subject('SPS reminder: '.$this->task->title.' is due in 3 days')
            ->view('mail.task-due-reminder');
    }
}
