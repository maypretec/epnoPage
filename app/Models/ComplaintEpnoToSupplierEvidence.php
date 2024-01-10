<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintEpnoToSupplierEvidence extends Model
{
    use HasFactory;
    protected $table = 'complaint_epno_to_supplier_evidence';
    protected $fillable = [
        'complaint_id',
        'user_id',
        'step_id',
        'epno_description',
        'epno_file',
        'epno_file_name',
        'supplier_description',
        'supplier_file',
        'supplier_file_name',
    ];

    public function Complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
