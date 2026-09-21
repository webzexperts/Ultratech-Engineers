<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportMptEquipmentDetails extends Model
{
    protected $table = "test_report_mpt_equipment_details";

    protected $primaryKey = 'test_report_mpt_equipment_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_mpt_id',
        'em_id',
        'em_sr_no',
        'make',
        'cal_due_date'
    ];
}
