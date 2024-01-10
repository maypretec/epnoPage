<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MroPart extends Model
{
    use HasFactory;

    protected $table = 'mro_parts';
    protected $fillable = ['user_id', 'epno_part_id', 'mro_request_id', 'part_cost', 'qty', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function epnoPart()
    {
        return $this->belongsTo(EpnoPart::class);
    }
    public function mroRequest()
    {
        return $this->belongsTo(MroRequest::class);
    }
    public function PartNos()
    {
        return $this->belongsTo(PartNo::class);
    }
}
