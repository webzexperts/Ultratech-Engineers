<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturnCustomer extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public $table = 'item_return_customer';

    protected $primaryKey = 'return_id';

    protected $fillable = [
        
        'return_sequence',
        'return_number',
        'return_date',
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