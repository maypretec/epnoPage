<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProposalComplaint extends Model
{
    use HasFactory;
    protected $table = 'supplier_proposal_complaints';
    protected $fillable = [
        'subservice_complaint_id',
        'supplier_proposal_id',
        'user_id',
    ];

    public function SubserviceComplaint()
    {
        return $this->belongsTo(SubserviceComplaint::class,'subservice_complaint_id');
    }
    public function Proposal()
    {
        return $this->belongsTo(SupplierProposal::class,'supplier_proposal_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function Logs()
    {
        return $this->hasMany(SupplierProposalComplaintLog::class);
    }
   
}
