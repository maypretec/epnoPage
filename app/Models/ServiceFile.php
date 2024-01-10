<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFile extends Model
{
    use HasFactory;

    protected $table = 'service_files';
    protected $fillable = ['service_id','step_id', 'file','file_name', 'status'];

    public function service()
    {
        return $this->belongsTo(ServiceFile::class);
    }
}
