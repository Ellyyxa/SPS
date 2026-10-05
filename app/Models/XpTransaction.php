<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class XpTransaction extends Model { protected $fillable=['user_id','source_type','source_id','xp_amount','reason','reward_key']; public function user(){return $this->belongsTo(User::class);} }
