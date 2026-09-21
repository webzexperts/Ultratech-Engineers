<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class SupplierDCDetails extends Model
{

   
    protected $table = 'supplier_dc_details';

    protected $primaryKey = 'sup_dcd_id';
    public $timestamps = false;

    protected $fillable = [
        'sup_dcd_dc_id',
        'ser_pod_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'return_qty',
        'rate_unit',
        'amount',
        'aerb_no',
        'application_no',
        'movement_approval',
        'movement_approval_blob',
        'validity',
        'previous_aerb_no',
        'previous_application_no',
        'previous_movement_approval',
        'previous_movement_approval_blob',
        'previous_validity',
        'remark',
        'stock_effect_type',
        'for_calibration',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];

}
