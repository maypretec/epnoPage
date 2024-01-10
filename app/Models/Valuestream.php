<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valuestream extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'valuestreams';
    /** @var array $fillable description */
    protected $fillable = ['name', 'status'];
    // TODO: pending analisys to implement valuestram categorization within clients
}
