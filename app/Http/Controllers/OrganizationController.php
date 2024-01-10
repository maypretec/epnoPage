<?php

namespace App\Http\Controllers;
#region Models
use App\Models\OrganizationCategory;
#endregion

#region helpers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
#endregion

/**
 * Organization controller for organization categories
 */
class OrganizationController extends Controller
{
    /**
     * Function for id and name of categories for a requested organization.
     *
     * Function that catches an organization id and returns an array with all categories and its respective id's related with that organization.
     *
     * @param Type $request identifier for the organization
     * @return array categories and id's array for every category related with given organization
     **/
    public function CategoriesList($request)
    {
        if (!Auth::check()) {
            return response('Session not found', 401);
        }
        $organizationCategories = OrganizationCategory::where('organization_id', $request)->with('category')->get();
        $categoriesList=[];
        foreach ($organizationCategories as $category) {
            $categoryData=[
                'category_id'=> $category->category->id,
                'category_name'=>$category->category->name
            ];
            $categoriesList[]=$categoryData;
        } 
        
        return response($categoriesList);
    }
}
