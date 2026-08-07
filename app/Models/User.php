<?php

namespace App\Models;
use App\Models\Task;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'student_id',
    'name',
    'email',
    'course',
    'semester',
    'password',
    'role'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function moods()
{
    return $this->hasMany(Mood::class);
}

public function sentNotifications()
{
    return $this->hasMany(Notification::class, 'admin_id');
}

public function notifications()
{
    return $this->hasMany(Notification::class, 'user_id');
}

}
