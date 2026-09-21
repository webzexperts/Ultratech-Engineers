<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class MeasurementSheet extends Model
{
    protected $table = "measurement_sheet";

    protected $primaryKey = 'measurement_sheet_id';

    public $timestamps = false;

    protected $fillable = [
        'current_location_id',
        'measurement_sheet_sequence',
        'measurement_sheet_no',
        'measurement_sheet_date',
        'customer_id',
        'total_ir_192_sqin',
        'total_co_60_sqin',
        'total_x_ray_sqin',
        'total_ir_192_repair_sqin',
        'total_co_60_repair_sqin',
        'total_x_ray_repair_sqin',
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
