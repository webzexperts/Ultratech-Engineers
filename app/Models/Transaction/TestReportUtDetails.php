<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportUtDetails extends Model
{
    protected $table = "test_report_ut_details";

    protected $primaryKey = 'test_report_ut_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_ut_id',
        'job_description',
        'material_id',
        'thickness',
        'ut_test_no',
        'heat_no',
        'quantity',
        'observation',
        'discontinuity_evaluation',
        'result_id',
    ];

    public function setMaterialIdAttribute($value)
    {
        $this->attributes['material_id'] = (!empty($value) && (int)$value > 0) ? (int)$value : null;
    }
}
