<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintClientToEpnoEvidence extends Model
{
    use HasFactory;
    protected $table = 'complaint_client_to_epno_evidence';
    protected $fillable = [
        'complaint_id',
        'user_id',
        'step_id',
        'client_description',
        'client_file',
        'client_file_name',
        'epno_description',
        'epno_file',
        'epno_file_name',
    ];

    public function Complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
