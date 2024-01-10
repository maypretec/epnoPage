<?php

namespace App\Models;

use App\Models\MroPart;
use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Model;

class PartNo extends Model
{
    protected $table = 'part_nos';
    protected $fillable = ['name', 'supplier_partno', 'max_qty','min_qty','current_qty', 'part_category_id', 'epno_part_id', 'user_id', 'price', 'status'];
    
    public function EpnoPart()
    {
        return $this->belongsTo(EpnoPart::class);
    }
    
    public function MroPart()
    {
        return $this->hasMany(MroPart::class);
    }
   
    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function Category()
    {
        return $this->belongsTo(PartCategory::class);
    }
}
