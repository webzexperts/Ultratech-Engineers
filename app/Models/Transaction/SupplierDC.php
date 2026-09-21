<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class SupplierDC extends Model
{

    protected $table = 'supplier_dc';

    protected $primaryKey = 'sup_dc_id';
     public $timestamps = false;

    protected $fillable = [
        'sup_dc_type_id',
        'sup_dc_sequence',
        'current_location_id',
        'sup_dc_number',
        'sup_dc_date',
        'supplier_id',
        'ref_no_date',
        'mode_of_transport',
        'transporter',
        'vehicle_no',
        'sp_note',
        'prepared_by_user_id',
        'assign_format_no',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];

}
