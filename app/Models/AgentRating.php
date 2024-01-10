<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentRating extends Model
{
    use HasFactory;
    
    protected $table = 'agent_ratings';
    protected $fillable = ['user_id', 'service_id', 'rating', 'comment', 'status'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
