<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regions';
    protected $fillable = ['country_id', 'name', 'status'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function states()
    {
        return $this->hasMany(States::class);
    }
}
