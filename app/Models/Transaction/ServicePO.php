<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ServicePO extends Model
{

    protected $table = "service_po";

    protected $primaryKey = 'ser_po_id';

    public $timestamps = false;

    protected $fillable = [
        
        'ser_po_id',
        'ser_po_sequence',
        'current_location_id',
        'ser_po_number',
        'ser_po_date',
        'supplier_id',
        'kind_attn_id',
        'purpose',
        'terms_and_conditions',
        'ref_no_date',
        'bill_to_id',
        'for_location_id',
        'payment_terms',
        'sp_note',
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
