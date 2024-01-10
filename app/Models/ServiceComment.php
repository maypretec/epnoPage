<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceComment extends Model
{
    use HasFactory;
    
    protected $table = 'service_comments';
    protected $fillable = ['service_id', 'step_id', 'user_id', 'file','file_name', 'comment', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
    public function user_info()
    {
        return $this->hasOne(UserInfo::class, 'user_id', 'user_id');
    }
}
