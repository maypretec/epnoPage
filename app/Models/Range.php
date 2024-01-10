<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Range extends Model
{
    use HasFactory;

    protected $table = 'ranges';
    protected $fillable = ['epno_part_id', 'status'];

    public function epnoPart()
    {
        return $this->belongsTo(EpnoPart::class);
    }
}
