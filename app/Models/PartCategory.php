<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCategory extends Model
{
    use HasFactory;

    protected $table = 'part_categories';
    protected $fillable = ['name', 'image','status'];

    public function epnoParts()
    {
        return $this->hasMany(EpnoPart::class);
    }
    public function PartNos()
    {
        return $this->hasMany(PartNo::class);
    }
}
