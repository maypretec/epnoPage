<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with the units a epno part and a subservice is measured
 */
class Unit extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'units';
    /** @var array $fillable description */
    protected $fillable = ['name', 'status'];
    /**
     * All epno parts measured in the unit
     *
     * Epno parts are categorized in units and may be retrieved from a relationship
     *
    
     * @return EpnoPart
    
     **/
    public function epnoParts()
    {
        return $this->hasMany(EpnoPart::class);
    }
    /**
     * The subservices measured in the unit
     *
     * Subservices are measured in the unit to better describe the order
     *
    
     * @return Subservice
    
     **/
    public function subservices()
    {
        return $this->hasMany(Subservice::class);
    }
}
