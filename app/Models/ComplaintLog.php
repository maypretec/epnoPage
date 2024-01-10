<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintLog extends Model
{
    use HasFactory;
    protected $table = 'complaint_logs';
    protected $fillable = [
        'complaint_id',
        'description',
        'cost',
        'user_id',
        'step_id'
    ];

    public function Queja()
    {
        return $this->belongsTo(Complaint::class);
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
