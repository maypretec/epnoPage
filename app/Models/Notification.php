<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $fillable = ['user_id', 'notification_type_id', 'seen', 'table_name', 'table_id', 'status'];

    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
