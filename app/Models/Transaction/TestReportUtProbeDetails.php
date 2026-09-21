<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportUtProbeDetails extends Model
{
    protected $table = "test_report_ut_probe_details";

    protected $primaryKey = 'test_report_ut_probe_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_ut_id',
        'pu_id',
        'pu_sr_no',
        'size_of_probe',
        'ref_angle',
        'frequency',
        'cal_range',
        'ref_gain',
        'scanning_db',
        'transfer_corr_gain',
    ];
}
