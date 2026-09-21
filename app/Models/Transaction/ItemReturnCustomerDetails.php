<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturnCustomerDetails extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'item_return_customer_details';

    protected $primaryKey = 'return_detail_id';

    protected $fillable = [
        
        'return_id',
        'dc_detail_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'grn_qty',
        'rate_unit',
        'amount',
        'remark',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}