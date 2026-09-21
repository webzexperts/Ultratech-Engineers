<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNSupplier extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $table = 'grn_supplier';

    protected $primaryKey = 'grn_id';

    protected $fillable = [
        'grn_id',
        'grn_sequence',
        'current_location_id',
        'grn_number',
        'grn_date',
        'grn_type_id',
        'grn_challan_number',
        'grn_challan_date',
        'grn_supplier_id',
        'mode_of_transport',
        'grn_transporter',
        'grn_vehicle_number',
        'grn_total_amount',
        'amount_in_word',
        'prepared_by_user_id',
        'grn_special_note',
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