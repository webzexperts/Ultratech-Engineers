<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChallanCustomer extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public $table = 'delivery_challan_customer';

    protected $primaryKey = 'dc_id';

    protected $fillable = [

        'dc_sequence',
        'dc_number',
        'dc_date',
        'customer_id',
        'current_location_id',
        'mode_of_transport',
        'transporter',
        'vehicle_no',
        'special_note',
        'prepared_by_user_id',
        'assign_format_no',
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