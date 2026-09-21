<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterLocationTransferDetails extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $table = 'inter_location_transfer_details';

    protected $primaryKey = 'inter_location_transfer_details_id';

    protected $fillable = [
        'inter_location_transfer_details_id',
        'inter_location_transfer_id',
        'item_id',
        'sr_no_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'pid_id',
        'dc_qty',
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
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}