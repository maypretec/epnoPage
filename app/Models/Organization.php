<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';
    protected $fillable = [
        'name',
        'rfc',
        'colony_id',
        'street',
        'external_number',
        'internal_number',
        'logo',
        'url',
        'pay_days',
        'status'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function colony()
    {
        return $this->belongsTo(Colony::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'organization_categories');
    }
    public function Queja()
    {
        return $this->hasOne(Complaint::class);
    }
}


//Select * from organizations as a join organization_categories as b on a.id = b.organization_id join categories as c on b.category_id = c.id where a.id=1