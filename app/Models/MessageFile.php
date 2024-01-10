<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    use HasFactory;
    protected $table = 'message_files';
    protected $fillable = [
        'message_id',
        'file',       
        'file_name',       
            
    ];

    public function Message()
    {
        return $this->belongsTo(Conversation::class);
    }
}
