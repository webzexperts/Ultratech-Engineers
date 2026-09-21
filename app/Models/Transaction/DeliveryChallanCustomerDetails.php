<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChallanCustomerDetails extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'delivery_challan_customer_details';

    protected $primaryKey = 'dc_detail_id';

    protected $fillable = [
        
        'dc_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'dc_qty',
        'rate_unit',
        'amount',
        'remark',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}