<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['name', 'status'];

    public function organizationCategories()
    {
        return $this->hasMany(OrganizationCategory::class);
    }
    public function subservices()
    {
        return $this->hasMany(Subservice::class);
    }
    /**
     * Organizations to organizationCategories relationships
     *
     * Relationship manager for Organizations to organizationCategories table.
     *
     * @return organizations organizations dictionary joined with organization categories
     **/
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_categories', 'category_id', 'organization_id');
    }
}
