<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ServicePODetails extends Model
{
    protected $table = "service_po_details";

    protected $primaryKey = 'ser_pod_id';

    public $timestamps = false;

    protected $fillable = [
        
        'ser_pod_id',
        'ser_pod_po_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'po_qty',
        'rate_unit',
        'amount',
        'del_date',
        'remark',
        'for_calibration',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}
