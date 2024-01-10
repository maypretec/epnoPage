<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\type;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $fillable = [
        'order_id',
        'title',
        'description',
        'user_id',
        'step_id',
        'client_cost',
        'supplier_cost',
        'quote_file',
        'client_deadline',
        'type',
        'rev',
        'prioridad',
        'status'
    ];

    public function agentRating()
    {
        return $this->hasOne(AgentRating::class);
    }
    public function proposals()
    {
        return $this->hasMany(SupplierProposal::class);
    }
    public function subservices()
    {
        return $this->hasMany(Subservice::class);
    }
    public function files()
    {
        return $this->hasMany(ServiceFile::class);
    }
    public function logs()
    {
        return $this->hasMany(ServiceLog::class);
    }
    public function Conversations()
    {
        return $this->hasMany(Conversation::class);
    }
    public function supplierRatings()
    {
        return $this->hasMany(SupplierRating::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
    public function Complaint()
    {
        return $this->hasOne(Complaint::class);
    }
}
