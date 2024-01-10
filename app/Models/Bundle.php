<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $table = 'bundles';
    protected $fillable = ['user_id', 'name', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parts()
    {
        return $this->hasMany(BundlePart::class);
    }
}
