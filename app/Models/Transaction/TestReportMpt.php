<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TestReportMpt extends Model
{
    protected $table = "test_report_mpt";

    protected $primaryKey = 'test_report_mpt_id';

    public $timestamps = false;

    protected $fillable = [
        'current_location_id',
        'test_report_sequence',
        'test_report_no',
        'test_report_date',
        'customer_id',
        'material_inward_details_id',
        'observation_sheet_details_id',
        'entry_type_fix',
        'nabl_type_fix',
        'job_type_fix',
        'from_type_id_fix',
        'customer_client',
        'type_of_job_id',
        'job_desc_id',
        'job_desc',
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
        'test_technique',
        'type_of_magnetization',
        'type_of_current',
        'prod_pole_spacing',
        'performance_verification',
        'lighting',
        'light_intensity',
        'background_light',
        'uva_light_intensity',
        'yoke_wt_lift_check',
        'procedure_ref_id',
        'acceptance_standard_id',
        'mpt_test_no_label',
        'heat_no_label',
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
        'total_qty',
        'assign_format_no',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];

    public function setMaterialInwardDetailsIdAttribute($value)
    {
        $this->attributes['material_inward_details_id'] = (!empty($value) && (int)$value > 0) ? (int)$value : null;
    }

    public function setObservationSheetDetailsIdAttribute($value)
    {
        $this->attributes['observation_sheet_details_id'] = (!empty($value) && (int)$value > 0) ? (int)$value : null;
    }

    public function setFromTypeIdFixAttribute($value)
    {
        $this->attributes['from_type_id_fix'] = (!empty($value) && (int)$value > 0) ? (int)$value : null;
    }

    public function equipment_details()
    {
        return $this->hasMany(TestReportMptEquipmentDetails::class, 'test_report_mpt_id', 'test_report_mpt_id');
    }

    public function material_details()
    {
        return $this->hasMany(TestReportMptMaterialDetails::class, 'test_report_mpt_id', 'test_report_mpt_id');
    }

    public function report_details()
    {
        return $this->hasMany(TestReportMptDetails::class, 'test_report_mpt_id', 'test_report_mpt_id');
    }
}

