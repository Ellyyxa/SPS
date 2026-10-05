<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GamificationStreakDay extends Model { protected $fillable=['user_id','activity_date']; protected function casts(): array { return ['activity_date'=>'date']; } }
