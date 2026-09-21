<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ServicePOShortClose extends Model
{

    protected $table = "service_po_short_close";

    protected $primaryKey = 'ser_po_sc_id';

    public $timestamps = false;

    protected $fillable = [
        
        'ser_po_sc_date',
        'ser_po_sc_pod_id',
        'ser_po_sc_qty',
        'ser_po_sc_reason_id',
        'current_status_transaction_id',
        'previous_status_transaction_id',
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
