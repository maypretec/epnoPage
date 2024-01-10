<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MroRequest extends Model
{
    use HasFactory;

    protected $table = 'mro_requests';
    protected $fillable = ['user_id', 'final_cost', 'subtotal', 'iva', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function mroParts()
    {
        return $this->hasMany(MroPart::class);
    }
}
