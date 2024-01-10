<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    use HasFactory;

    protected $table = 'postal_codes';
    protected $fillable = ['name', 'city_id', 'status'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function colonies()
    {
        return $this->hasMany(Colonies::class);
    }
}
