<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLog extends Model
{
    use HasFactory;

    /** @var string $table the name of the MySQL table pointing from the model to the DB */
    protected $table = 'service_logs';
    /** @var array $fillable columns that can be mass assigned if edited a collection of models */
    protected $fillable = ['step_id', 'user_id', 'service_id', 'status'];

    /**
     * The user associated with the step
     *
     * Each log represents a step on the service flow. The users that edits the service is logged in the table
     *
     * @return User
     * TODO: Define what expections are thrown and when
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * The logged step
     *
     * Each log represents a step on the service flow. Each change is logged for control and analytics
     *
     * @return Step
     **/
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
}
