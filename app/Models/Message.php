<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';
    protected $fillable = [
        'conversation_id',
        'user_id',        
        'step_id',        
        'comment',        
    ];

    public function Conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Files()
    {
        return $this->hasMany(MessageFile::class);
    }
}
