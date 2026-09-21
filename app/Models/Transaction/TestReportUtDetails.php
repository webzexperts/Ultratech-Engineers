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
        'ut_test_no',
        'heat_no',
        'quantity',
        'discontinuity_evaluation',
        'result_id',
    ];
}
