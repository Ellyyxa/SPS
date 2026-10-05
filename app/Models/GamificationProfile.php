<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GamificationProfile extends Model { protected $fillable=['user_id','total_xp','current_streak','longest_streak','last_qualifying_activity_date']; protected function casts(): array { return ['last_qualifying_activity_date'=>'date']; } public function user(){return $this->belongsTo(User::class);} }
