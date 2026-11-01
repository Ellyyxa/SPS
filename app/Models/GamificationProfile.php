<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationProfile extends Model
{
    protected $fillable = ['user_id', 'total_xp', 'current_streak', 'longest_streak', 'last_qualifying_activity_date'];

    // Match database defaults on newly created instances before any re-query.
    protected $attributes = ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0];

    protected function casts(): array
    {
        return ['last_qualifying_activity_date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
