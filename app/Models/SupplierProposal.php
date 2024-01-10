<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Laravel model associated with a supplier's paricipation in a order  
 */

class SupplierProposal extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'supplier_proposals';
    /** @var array $fillable description */
    protected $fillable = [
        'service_id', 
        'subservice_id', 
        'user_id', 
        'supplier_code', 
        'unitary_subtotal_cost', 
        'description', 
        'supplier_deadline', 
        'supplier_code', 
        'epno_cost', 
        'quote_file', 
        'total_cost', 
        'qty', 
        'iva', 
        'rev', 
        'epno_po_file', 
        'is_winner', 
        'check', 
        'status'
    ];

    /**
     * The service on which the subservice that the supplier is participating
     *
     * A service has n subservices and a subservice has n supplier proposals. This is the container service
     *
    
     * @return Service
    
     **/
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    /**
     * The subservice that the supplier is participating in
     *
     * A subservice represents an entry on a request for many items in an order from a client
     *
    
     * @return Subservice
    
     **/
    public function subservice()
    {
        return $this->belongsTo(Subservice::class);
    }
    /**
     * The user of the supplier proposal 
     *
     * A supplier proposal must be created bya user with a supplier role
     *
    
     * @return User
    
     **/
    public function UserSupp()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * All the logs of a supplier proposal
     *
     * Whenever a supplier has to change the quote proposition, a new revision is created to keep track of previous quotes
     *
    
     * @return SupplierProposalLog
    
     **/
    public function supplierProposalLog()
    {
        return $this->hasMany(SupplierProposalLog::class);
    }
    public function supplierProposalComplaint()
    {
        return $this->hasOne(SupplierProposalComplaint::class);
    }
    public function User()
    {
        return $this->belongsTo(User::class,'user_id');
      
    }
    /**
     * The rating of the supplier in a given subservice
     *
     * Whenever a supplier completes an order, an EP&O agent MUST evaluate its performance
     *
    
     * @return SupplierRating
    
     **/
    public function supplierRating()
    {
        return $this->hasOne(SupplierRating::class);
    }
}
