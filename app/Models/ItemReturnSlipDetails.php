<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturnSlipDetails extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "item_return_slip_details";

    protected $primaryKey = 'irsd_id';

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'irsd_id',
        'irsd_irs_id',
        'irsd_iisd_id',
        'irsd_item_id',
        'irsd_sr_no_id',
        'irsd_batch_no_id',
        'irsd_sr_no_or_batch_no',
        'irsd_return_qty',
        'irsd_non_rerurnable_qty',
        'irsd_reason_id',
        'irsd_remark',
        'status',
        'current_status_transaction_id',
        'previous_status_transaction_id'
    ];
}