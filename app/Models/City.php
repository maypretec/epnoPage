<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';
    protected $fillable = ['name', 'state_id', 'status'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function postalCodes()
    {
        return $this->hasMany(PostalCode::class);
    }
}
