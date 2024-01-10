<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    // @var string $table_name This model uses a view as a base. No update or delete are allowed */
    protected $table = 'user_info';

    // @var array<int, string> $fillfable columns to be mass asigned */
    protected $fillfable= [
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
        'org_id',
        'org_name',
        'org_rfc',
        'org_address',
        'org_paydays',
        'org_logo',
        'role_id',
        'role_name',
        'vs_id',
        'vs_name',
    ];

    //
     
// Access the relationship from teh user_info view to the users table*
// Undocumented function long description*
// @param null
// @return type
// @throws conditon
// /
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
     
// Acces the relationship from the user_info view to the organizations table*
// Undocumented function long description*
// @param null
// @return type
// @throws conditon
// /
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    //
     
// Access the relationship from the user_info view to the valustreams table*
// Undocumented function long description*
// @param null
// @return type
// @throws conditon
// /
    public function valuestream()
    {
        return $this->belongsToMany(Valuestream::class, 'vs_user');
    }
}
