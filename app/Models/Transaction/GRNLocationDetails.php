<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNLocationDetails extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public $table = 'grn_location_details';

    protected $primaryKey = 'grn_locd_id';

    protected $fillable = [
        'grn_locd_grn_id',
        'inter_location_transfer_details_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'qty',
        'rate_unit',
        'amount',
        'remark',
        'stock_effect_type',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}