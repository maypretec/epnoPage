<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubserviceComplaint extends Model
{
    use HasFactory;
    protected $table = 'subservice_complaints';
    protected $fillable = [
        'complaint_id',
        'subservice_id',
    ];

    public function Queja()
    {
        return $this->belongsTo(Complaint::class,'complaint_id');
    }
   
    public function Suppliers()
    {
        return $this->hasMany(SupplierProposalComplaint::class);
    }

    public function Sub()
    {
        return $this->belongsTo(Subservice::class,'subservice_id');
    }
}
