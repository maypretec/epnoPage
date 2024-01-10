<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with service types and the main interface to dynamically process every order on the platform.
 */
class ServiceType extends Model
{
    use HasFactory;
    /** @var string $table the name of the MySQL table pointing from the model to the DB */
    protected $table = 'service_types';
    /** @var array $fillable columns that can be mass assigned if edited a collection of models */
    protected $fillable = ['table_name', 'process_steps', 'type', 'status'];
    /**
     * Orders categorized as a specific service_type
     *
     * Orders are categorized either as fabrication, MRO or service
     *
     * @return Order
     **/
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    /**
     * Comments belonging to the category
     *
     * Do not use; no business case so far.
     *
     * @return ServiceComment
     **/
    public function serviceComments()
    {
        return $this->hasMany(ServiceComment::class);
    }
}
