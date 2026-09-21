<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportMptMaterialDetails extends Model
{
    protected $table = "test_report_mpt_material_details";

    protected $primaryKey = 'test_report_mpt_material_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_mpt_id',
        'mm_id',
        'batch_no',
        'make',
        'expiry_date'
    ];
}
