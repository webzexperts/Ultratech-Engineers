<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetails extends Model
{
    protected $table = 'purchase_order_details';

    protected $primaryKey = 'pod_id';

    public $timestamps = false; 

    protected $fillable = [
        'pod_po_id',
        'pod_pid_id',
        'pod_item_id',
        'pod_po_qty',
        'pod_rate_unit',
        'pod_amount',
        'pod_del_date',
        'pod_remark',
        'status',
    ];

}
