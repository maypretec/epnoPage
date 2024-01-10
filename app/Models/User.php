<?php

namespace App\Models;

use App\Notifications\PasswordResetNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'organization_id',
        'role_id',
        'phone',
        'email',
        'password',
        'iva',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * The organization to which the user belongs to
     *
     * An organization may have one or many users under its register
     *
    
     * @return Organization
    
     **/
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function Messages()
    {
        return $this->hasMany(Message::class);
    }
    
    /**
     * The role of the user
     *
     * The role of a user defines which actions can be performed. TODO: create permission matrix within the app
     *
    
     * @return Role
    
     **/
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    /**
     * The bundles of the user
     *
     * A bundle is where a user stores one or many mro products to easily get to them and order them.
     *
    
     * @return Bundle
    
     **/
    public function bundles()
    {
        return $this->hasMany(Bundle::class);
    }
    /**
     * The comment or question that a user has made into a epno part about it
     *
     * A user may ask questions about a specific producto in the MRO section of the app
     *
    
     * @return ProductComment
    
     **/
    public function productCommentQuestions()
    {
        return $this->hasMany(ProductComment::class, 'user_comment');
    }
    /**
     * The answer of the user to questions
     *
     * A user may answer to certain questions in the MRO section
     *
    
     * @return ProductComment
    
     **/
    public function productCommentAnswers()
    {
        return $this->hasMany(ProductComment::class, 'user_answer');
    }
    /**
     * The notifications that have been issued to a user
     *
     * When a process from within the app moves, the stakeholders are always notified via email and in-app notifications
     *
    
     * @return Notification
    
     **/
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    /**
     * The orders in which a buyer took part on
     *
     * A buyer is meassured and taken into consideration for internal rewards depending on the performance on order resolution
     *
    
     * @return User
    
     **/
    public function buyer()
    {
        return $this->hasMany(User::class);
    }
    /**
     * All mro request created by the user
     *
     * A user, either supplier or client has the ability to order something through the app
     *
    
     * @return MroRequest
    
     **/
    public function mroRequests()
    {
        return $this->hasMany(MroRequest::class);
    }
    /**
     * All services created by the user
     *
     * A user, either supplier or client has the ability to order something through the app
     *
    
     * @return array<Service>
    
     **/
    public function services()
    {
        return $this->hasMany(Service::class);
    }
    // TODO: change all hasMany relationships to array<model>
    /**
     * Every log related to the user within service progress
     *
     * A log is recorded whenever a service moves within its process and it contains the issuing user
     *
    
     * @return array<ServiceLog>
    
     **/
    public function serviceLogs()
    {
        return $this->hasMany(ServiceLog::class);
    }
    /**
     * Comments posted on orders by the user
     *
     * The user may post comments on an integrated chat within the order details page
     *
    
     * @return array<ServiceComment>
    
     **/
    public function serviceComments()
    {
        return $this->hasMany(ServiceComment::class);
    }
    /**
     * All items ordered on the MRO section by the user
     *
     * Enables the dynamic variable mroParts within a user model using Laravel relationships between models
     *
    
     * @return array<MroPart>
    
     **/
    public function mroParts()
    {
        return $this->hasMany(MroPart::class);
    }
    /**
     * The subservice logs created by the user
     *
     * Enables the dynamic variable subserviceLogs within a user model using Laravel relationships between models
     *
    
     * @return array<SubServiceLog>
     
     **/
    public function subserviceLogs()
    {
        return $this->hasMany(SubServiceLog::class);
    }
    /**
     * All supplier proposals created by a supplier role user
     *
     * Enables the dynamic variable supplierProposals within a user model using Laravel relationships between models
     *
    
     * @return array<SupplierProposal>
    
     **/
    public function supplierProposals()
    {
        return $this->hasMany(SupplierProposal::class);
    }
    /**
     * The ratings of a supplier user within its completed subservices
     *
     * Enables the dynamic variable supplierRatings within a user model using Laravel relationships between models
     *
     * @return array<SupplierRatings>
     **/
    public function supplierRatings()
    {
        return $this->hasMany(SupplierRatings::class);
    }
    /**
     * The ratings of an EP&O agent within all orders
     *
     * Enables the dynamic variable agentRatings within a user model using Laravel relationships between models
     *
    
     * @return array<AgentRating>
    
     **/
    public function agentRatings()
    {
        return $this->hasMany(AgentRating::class);
    }

    public function PartNos()
    {
        return $this->hasMany(PartNo::class);
    }

    public function Queja()
    {
        return $this->hasOne(Complaint::class);
    }
    public function Supplier()
    {
        return $this->hasOne(SupplierProposal::class);
    }
    public function supplierProposalComplaint()
    {
        return $this->hasOne(SupplierProposalComplaint::class);
    }
    public function SubComplaintClient()
    {
        return $this->hasOne(ComplaintClientToEpnoEvidence::class);
    }
    public function SubComplaintEpno()
    {
        return $this->hasOne(ComplaintEpnoToSupplierEvidence::class);
    }
    public function ComplaintLog()
    {
        return $this->hasOne(ComplaintLog::class);
    }
    public function SupplierProposalComplaintLog()
    {
        return $this->hasOne(SupplierProposalComplaintLog::class);
    }
    // TODO: change every long description on models to the Enables text
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }
}
