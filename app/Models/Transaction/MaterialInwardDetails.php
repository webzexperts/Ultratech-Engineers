<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class MaterialInwardDetails extends Model
{
    protected $table = "material_inward_details";

    protected $primaryKey = 'material_inward_details_id';

    public $timestamps = false;

    protected $fillable = [
        'material_inward_details_id',
        'material_inward_id',
        'type_of_testing_id_fix',
        'type_of_job_id',
        'job_desc_id',
        'part_id',
        'part_no',
        'drg_no',
        'material_id',
        'heat_no',
        'rt_no',
        'product_code',
        'thickness',
        'area_of_coverage_id',
        'procedure_ref_id',
        'evaluation_as_per_id',
        'acceptance_standard_id',
        'quantity',
        'approx_value',
        'approx_weight',
        'remark',
        'process_type',
        'is_observation_sheet',
        'test_report_rt_id'
    ];
}
