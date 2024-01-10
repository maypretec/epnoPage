<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Laravel model associated with the logs registered when a supplier needs to change its quote
 */

class SupplierProposalLog extends Model
{
    use HasFactory;
    /** @var string $table description */
    protected $table = 'supplier_proposal_logs';
    /** @var array $fillable description */
    protected $fillable = [
        'supplier_proposal_id',
        'rev',
        'total_cost',
        'unitary_subtotal_cost',
        'quote_file',
        'qty',
        'iva',
        'supplier_deadline',
        'status'
    ];
    /**
     * The supplier proposal of the log
     *
     * A log describes the progress of a supplier quotation
     *
    
     * @return SupplierProposal
    
     **/
    public function supplierProposal()
    {
        return $this->belongsTo(SupplierProposal::class);
    }
}
