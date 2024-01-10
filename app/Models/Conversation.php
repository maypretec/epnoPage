<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $table = 'conversations';
    protected $fillable = [
        'service_id',
        'first_participant',        
        'first_participant_role',        
        'second_participant',        
        'second_participant_role',        
    ];

    public function Service()
    {
        return $this->belongsTo(Service::class);
    }

    public function Messages()
    {
        return $this->hasMany(Message::class);
    }
}
