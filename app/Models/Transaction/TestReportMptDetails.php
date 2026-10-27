<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportMptDetails extends Model
{
    protected $table = "test_report_mpt_details";

    protected $primaryKey = 'test_report_mpt_details_id';

    public $timestamps = false;

    /*
    protected $fillable = [
        'test_report_mpt_id',
        'mpt_test_no',
        'heat_no',
        'quantity',
        'discontinuity_evaluation',
        'result_id'
    ];
    */

    protected $fillable = [
        'test_report_mpt_id',
        'job_description',
        'material_id',
        'thickness',
        'mpt_test_no',
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
