<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportDptDetails extends Model
{
    protected $table = "test_report_dpt_details";

    protected $primaryKey = 'test_report_dpt_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_dpt_id',
        'dpt_test_no',
        'heat_no',
        'quantity',
        'discontinuity_evaluation',
        'result_id',
    ];
}
