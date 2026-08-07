<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'title',
        'message',
        'is_read',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}