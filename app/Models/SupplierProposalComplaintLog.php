<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProposalComplaintLog extends Model
{
    use HasFactory;

    protected $table = 'supplier_proposal_complaint_logs';
    protected $fillable = [
        'supplier_proposal_complaint_id',
        'description',
        'cost',
        'user_id',
        'step_id'
    ];

    public function SupplierProposalComplaint()
    {
        return $this->belongsTo(SupplierProposalComplaint::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function Step()
    {
        return $this->belongsTo(Step::class);
    }
}
