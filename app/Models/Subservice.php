<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Laravel model associated with the subservices that describe a service order
 */
class Subservice extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'subservices';
    /** @var array $fillable description */
    protected $fillable = ['service_id', 'name', 'step_id', 'epno_deadline', 'qty', 'category_id','unit_id', 'specs_file', 'status'];
    /**
     * The service owning the subservice
     *
     * A service can have n amount od subservices to better control whats is quoted to the client_po_file
     *
    
     * @return Service
    
     **/
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    /**
     * The current step in which the subservice is
     *
     * Within a subservice process there are several steps that the subservice can take 
     *
     * @return Step
     **/
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
    /**
     * The category of the subservice
     *
     * each subservice is categorized to help the algorithm choose the supplier that better fit the profile for the order to be completed.
     *
    
     * @return Category
    
     **/
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * The supplier proposals assigned to the subservices
     *
     * A single subservice can be served by 1 or many suppliersCount
     *
    
     * @return SupplierProposal
    
     **/
    public function supplierProposal()
    {
        return $this->hasMany(SupplierProposal::class);
    }
    /**
     * The ratings of the subservice
     *
     * When completed, a subservice is valued by the rating given by an EP&O agent to all the supplier that served it
     *
    
     * @return SupplierRating
    
     **/
    public function supplierRating()
    {
        return $this->hasMany(SupplierRating::class);
    }
    /**
     * The logs of the subservice
     *
     * Enables the dynamic variable subserviceLogs within a service model using Laravel relationships between models
     *
    
     * @return array<SubServiceLog>
    
     **/
    public function subserviceLogs()
    {
        return $this->hasMany(SubServiceLog::class);
    }
    /**
     * The unit of thesubservice 
     *
     * subservices use units to help agents and suppliers better undestand the order
     *
    
     * @return Unit
    
     **/
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function SubComplaint()
    {
        return $this->hasOne(SubserviceComplaint::class);
    }
}
