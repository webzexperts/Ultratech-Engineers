<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class MeasurementSheetDetails extends Model
{
    protected $table = "measurement_sheet_details";

    protected $primaryKey = 'measurement_sheet_details_id';

    public $timestamps = false;

    protected $fillable = [
        'measurement_sheet_id',
        'test_report_rt_id',
        'material_inward_details_id',
        'ir_192_sqin',
        'co_60_sqin',
        'x_ray_sqin',
        'ir_192_repair_sqin',
        'co_60_repair_sqin',
        'x_ray_repair_sqin',
        'remark',
    ];
}
