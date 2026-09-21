<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class CustomerDCNonReturnable extends Model
{
    /**
     * Table name
     */
    protected $table = "customer_dc_non_returnable";

    public $timestamps = false;

    protected $primaryKey = 'customer_dc_non_returnable_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'current_location_id',
        'customer_dc_non_returnable_sequence',
        'customer_dc_non_returnable_no',
        'customer_dc_non_returnable_date',
        'customer_id',
        'total_qty',
        'mode_of_transport',
        'transporter',
        'vehicle_no',
        'sp_note',
        'assign_format_no',
        'prepared_by_user_id',
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
