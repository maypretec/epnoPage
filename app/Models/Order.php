<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'cot_date',
        'po_date',
        'invoice_date',
        'client_po_file',
        'order_num',
        'concept_order',
        'iva',
        'total_client',
        'subtotal_client',
        'expiration_date',
        'client_name',
        'iva_supplier',
        'total_supplier',
        'subtotal_supplier',
        'return_amount',
        'vendor_commision',
        'capital_commission',
        'net_utility',
        'investor',
        'buyer',
        'supplier',
        'client_org',
        'expiration_date_supplier',
        'supplier_status',
        'invoice_file',
        'is_po',
        'service_type_id',
        'status'
     ];

     public function service()
     {
         return $this->hasOne(Service::class);
     }
     public function Queja()
     {
         return $this->hasOne(Complaint::class);
     }
}
