<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VsUser extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'vs_users';
    /** @var array<string> $fillable description */
    protected $fillable = ['user_id', 'vs_id', 'status'];
    /**
     * The user of the valuestream
     *
     * 
     *
    
     * @return User
    
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * The valuestream of the user
     *
     * Undocumented function long description
     *
    
     * @return Valuestream
    
     **/
    public function vs()
    {
        return $this->belongsTo(Valuestream::class, 'vs_id');
    }

}
