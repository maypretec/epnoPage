<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;
    protected $table = 'complaints';
    protected $fillable = [
        'order_id',
        'service_id',
        'title',
        'user_id',
        'organization_id',
        'complaint_num',
        'responsible_user',
        'order_num',
        'type',
        'client_cost',
        'supplier_cost',
        'return_amount',
        'rework_cost',
        'step_id',
        'root_cause',
        'lesson_learned',
        'close_date',
    ];

    public function Order()
    {
        return $this->belongsTo(Order::class);
    }
    public function Step()
    {
        return $this->belongsTo(Step::class);
    }
    public function Subservices()
    {
        return $this->hasMany(SubserviceComplaint::class);
    }
    public function Service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }
    public function Logs()
    {
        return $this->hasMany(ComplaintLog::class);
    }
    public function Rework_cost()
    {
        return $this->hasMany(ComplaintLog::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function SubserviceComplaintClient()
    {
        return $this->hasMany(ComplaintClientToEpnoEvidence::class);
    }
    public function SubserviceComplaintEpno()
    {
        return $this->hasMany(ComplaintEpnoToSupplierEvidence::class);
    }
    
}
