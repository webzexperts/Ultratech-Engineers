<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ItemIssueDetails extends Model
{
    //
    protected $table = 'item_issue_details';
    protected $primaryKey = 'issue_detail_id';
    public $timestamps = false; 

    protected $fillable = [
        'issue_detail_id',
        'issue_id',
        'item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'issue_qty',
        'conv_factor',
        'rate_unit',
        'amount',
        'issue_type',
        'wastage_reason_id',
        'remark',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}