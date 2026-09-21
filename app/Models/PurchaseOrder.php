<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
     protected $table = 'purchase_order';

    protected $primaryKey = 'po_id';

    public $timestamps = false; 

    protected $fillable = [
        'po_type_id',
        'po_sequence',
        'current_location_id',
        'po_number',
        'po_date',
        'po_supplier_id',
        'po_kind_attn_id',
        'po_terms_and_conditions',
        'payment_terms',
        'ref_no_date',
        'bill_to_location_id',
        'ship_to_location_id',
        'po_sp_note',
        'basic_amount',
        'gst_type_fix_id',
        'sgst_percentage',
        'sgst_amount',
        'cgst_percentage',
        'cgst_amount',
        'igst_percentage',
        'igst_amount',
        'round_off_val',
        'net_amount',
        'amount_in_word',
        'prepared_by_user_id',
        'assign_format_no',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];
}
