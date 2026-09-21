<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNLocation extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $table = 'grn_location';

    protected $primaryKey = 'grn_loc_id';

    protected $fillable = [
        'grn_loc_id',
        'grn_loc_sequence',
        'grn_loc_number',
        'grn_loc_date',
        'current_location_id',
        'mode_of_transport',
        'transporter',
        'vehicle_no',
        'prepared_by_user_id',
        'sp_note',
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