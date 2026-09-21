<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportMptDetails extends Model
{
    protected $table = "test_report_mpt_details";

    protected $primaryKey = 'test_report_mpt_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_mpt_id',
        'mpt_test_no',
        'heat_no',
        'quantity',
        'discontinuity_evaluation',
        'result_id'
    ];
}
