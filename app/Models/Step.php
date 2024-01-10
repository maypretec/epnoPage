<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with the status that an order or item on an order can take within the process
 */
class Step extends Model
{
    use HasFactory;
    /** @var string $table the name of the MySQL table pointing from the model to the DB */
    protected $table = 'steps';
    /** @var array $fillable columns that can be mass assigned if edited a collection of models */
    protected $fillable = ['name', 'status'];

    /**
     * All the services currently on the step
     *
     * Services within the app can be on one of several steps. This function/variable will return a collection of the services currently on the step.
     *
     * @return Service
     **/
    public function services()
    {
        return $this->hasMany(Service::class);
    }
    /**
     * All service logs registered on the step.
     *
     * Whenever a services changes its step, a log is created to keep track of time. This function/variable will return a collection of these logss belonging to the step
     *
     * @return ServiceLog
     **/
    public function serviceLogs()
    {
        return $this->hasMany(ServiceLog::class);
    }
    public function Queja()
    {
        return $this->hasOne(Complaint::class);
    }
    public function SupplierProposalComplaintLog()
    {
        return $this->hasOne(SupplierProposalComplaintLog::class);
    }
    /**
     * All the subservices currently on the step.
     *
     * Subservices within the app can be on ne of several steps. This function will return a collection of the subservices currently on the step
     *
     * @return Subservice
     **/
    public function subservices()
    {
        return $this->hasMany(Subservice::class);
    }
    /**
     * All subservice logs registeren on the steps
     *
     * Whenever a subservice changes its steo, a log is created to keep track of time. This function/variable will return a collection of these logs belonging to the step
     *
     * @return SubserviceLog
     **/
    public function subserviceLogs()
    {
        return $this->hasMany(SubserviceLog::class);
    }
    public function complaintLogs()
    {
        return $this->hasMany(ComplaintLog::class);
    }
}
