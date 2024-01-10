<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with all the types of notifications that will alert the user to take action in the aplication
 */
class NotificationType extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'notification_types';
    /** @var array $fillable description */
    protected $fillable = ['desciption', 'status'];

    /**
     * All notifications under a type
     *
     * Notifications are categorized within types and may be retrieved from a collection
     *
    
     * @return Notification
    
     **/
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
