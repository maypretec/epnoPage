<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Laravel model associated with the rating given to a supplier on an order from an EP&O agent
 */
class SupplierRating extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'supplier_ratings';
    /** @var array $fillable description */
    protected $fillable = ['user_id', 'supplier_proposal_id', 'service_id', 'subservice_id',"table_name", 'rating', 'comment', 'status'];

    /**
     * The supplier evaluated
     *
     * A user with a supplier role is evaluated whenever a order is completed or
     *
    
     * @return User
    
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * The supplier proposal in which the user was evaluated
     *
     * A rating is given per order completed and it is associated with the participation of a supplier within a subservice
     *
    
     * @return SupplierProposal
    
     **/
    public function supplierProposal()
    {
        return $this->belongsTo(SupplierProposal::class);
    }
    /**
     * The service in which the supplier user was asked for participation
     *
     * An order is made by one or more subservices, which are made from 1 or more supplier proposals wich are the ones that are used to evaulate
     *
    
     * @return Service
    
     **/
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    /**
     * The subservice in which the supplier user participated
     *
     * A supplier only participates on a service if the subservice in which the supplier user got assigned a percentage of the quantity is accepted by EP&O agents
     *
    
     * @return Subservice
    
     **/
    public function subservice()
    {
        return $this->belongsTo(Subservice::class);
    }
}
