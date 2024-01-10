<?php

namespace App\Models;

use App\PartNo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpnoPart extends Model
{
    use HasFactory;

    protected $table = 'epno_parts';
    protected $fillable = ['name', 'part_no','price', 'image', 'unit_id', 'part_category_id','description', 'status'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function partCategory()
    {
        return $this->belongsTo(PartCategory::class);
    }
    public function bundleParts()
    {
        return $this->hasMany(BundlePart::class);
    }
    public function productComments()
    {
        return $this->hasMany(ProductComment::class);
    }
    public function ranges()
    {
        return $this->hasMany(Range::class);
    }
    public function mroParts()
    {
        return $this->hasMany(MroPart::class);
    }
    public function PartNos()
    {
        return $this->hasMany(PartNo::class);
    }
}
