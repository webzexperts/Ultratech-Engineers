<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportRtDetails extends Model
{
    protected $table = "test_report_rt_details";

    protected $primaryKey = 'test_report_rt_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_rt_id',
        'sr_no',
        'identification',
        'location',
        'source_id_fix',
        'film_brand_id',
        'film_type_id',
        'thickness',
        'sfd',
        'iqi_designation',
        'iqi_sensitivity',
        'iqi_designation_id',
        'iqi_sensitivity_id',
        'optical_density',
        'film_id',
        'no_of_film_fix',
        'exposure_time',
        'finding',
        'finding_id',
        'finding_level_id',
        'result_id',
        'film_result_type_fix',
        'ug',
        'film_qty',
        'sq_in',
        'sq_cm',
        'total_sq_in',
        'total_sq_cm',
        'include_in_measurement_sheet',
        'record_type_id',
        'main_rt_detail_id'
    ];
}
