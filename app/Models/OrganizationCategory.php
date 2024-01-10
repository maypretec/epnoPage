<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationCategory extends Model
{
    use HasFactory;

    protected $table = 'organization_categories';
    protected $fillable = ['organization_id', 'category_id', 'status'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
