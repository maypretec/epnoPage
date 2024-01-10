<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colony extends Model
{
    use HasFactory;

    protected $table = 'colonies';
    protected $fillable = ['name', 'postal_code_id', 'status'];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
    public function postalCode()
    {
        return $this->belongsTo(PostalCode::class);
    }
}
