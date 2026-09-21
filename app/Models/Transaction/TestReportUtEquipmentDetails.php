<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportUtEquipmentDetails extends Model
{
    protected $table = "test_report_ut_equipment_details";

    protected $primaryKey = 'test_report_ut_equipment_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_ut_id',
        'eu_id',
        'make',
        'display',
        'eu_sr_no',
        'cal_due_date',
    ];
}
