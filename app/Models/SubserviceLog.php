<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Laravel model associated with the logs registered whenever a subservice moves in its process
 */

class SubserviceLog extends Model
{
    use HasFactory;
    /** @var string $table the name of the MySQL table pointing from the model to the DB */
    protected $table = 'subservice_logs';
    /** @var array $fillable columns that can be mass assigned if edited a collection of models */
    protected $fillable = ['subservice_id', 'step_id', 'user_id', 'status'];

    /**
     * The subservice that the log reffers to
     *
     * A log describes when and who moved a subservice within its proccess.
     *
    
     * @return Subservice
    
     **/
    public function subservice()
    {
        return $this->belongsTo(Subservice::class);
    }
    /**
     * The step that was logged
     *
     * A log registers when and who moved a subservice to whiwch step
     *
    
     * @return Step
    
     **/
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
    /**
     * The user that moved the subservice
     *
     * A user is responsible, depending on its role, for moving a subservice forward
     *
     * @return User
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
