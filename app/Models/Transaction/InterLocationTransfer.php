<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterLocationTransfer extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $table = 'inter_location_transfer';

    protected $primaryKey = 'ilt_id';

    protected $fillable = [
        'ilt_id',
        'dc_sequence',
        'current_location_id',
        'dc_number',
        'dc_date',
        'to_location_id',
        'dc_type_id',
        'mode_of_transport',
        'transporter',
        'vehicle_no',
        'sp_note',
        'prepared_by_id',
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