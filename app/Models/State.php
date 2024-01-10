<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with the country states available for a client or supplier to register.
 */

class State extends Model
{
    use HasFactory;
    
    /** @var string $table  the name of the MySQL table pointing from the model to the DB */
    protected $table = 'states';
    /** @var array $fillable columns that can be mass assigned if edited a collection of models */
    protected $fillable = ['name', 'country_id', 'status'];
    /**
     * The country to whom the state belongs
     *
     * A country is needed for a client or supplier to register their address. 
     *
     * @return Country
     **/
    public function country()
    {
        return $this->belongsTo(Country::class,'region_id');
    }
    /**
     * The cities that belong to the states
     *
     * A list of the cities belonging to a particular state is
     *
     * @return City
     **/
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
