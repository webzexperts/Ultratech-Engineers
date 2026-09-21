<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportDptChemicalDetails extends Model
{
    protected $table = "test_report_dpt_chemical_details";

    protected $primaryKey = 'test_report_dpt_chemical_details_id';

    public $timestamps = false;

    protected $fillable = [
        'test_report_dpt_id',
        'dpt_id',
        'chemical_designation',
        'make',
        'batch_no',
        'expiry_date',
    ];
}
