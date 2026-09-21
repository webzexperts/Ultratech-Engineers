<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportUt extends Model
{
    protected $table = "test_report_ut";

    protected $primaryKey = 'test_report_ut_id';

    public $timestamps = false;

    protected $fillable = [
        'current_location_id',
        'test_report_sequence',
        'test_report_no',
        'test_report_date',
        'customer_id',
        'material_inward_details_id',
        'observation_sheet_details_id',
        'nabl_type_fix',
        'job_type_fix',
        'from_type_id_fix',
        'customer_client',
        'type_of_job_id',
        'job_desc_id',
        'part_no',
        'drg_no',
        'material_id',
        'heat_no',
        'product_code',
        'date_of_testing',
        'date_of_testing_value',
        'test_carried_out_at',
        'amendment_no',
        'amendment_date',
        'amendment_reason',
        'stage_of_test',
        'area_of_coverage_id',
        'surface_condition',
        'surface_temp',
        'thickness',
        'couplant',
        'test_technique',
        'ref_block_used',
        'scanning_area',
        'scan_plan_no',
        'procedure_ref_id',
        'acceptance_standard_id',
        'defectogram_image',
        'defectogram_image_blob',
        'ulr_id',
        'ulr_no',
        'ulr_sequence',
        'ulr_year',
        'tested_by_authority_person_id',
        'reviewed_by_authority_person_id',
        'authorized_by_authority_person_id',
        'sp_note',
        'note',
        'assign_format_no',
        'total_qty',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];
}
