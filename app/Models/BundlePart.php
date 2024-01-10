<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundlePart extends Model
{
    use HasFactory;

    protected $table = 'bundle_parts';
    protected $fillable = ['bundle_id', 'epno_part_id', 'qty', 'status'];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
    public function epnoPart()
    {
        return $this->belongsTo(EpnoPart::class);
    }
}
