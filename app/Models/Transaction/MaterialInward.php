<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class MaterialInward extends Model
{
    protected $table = "material_inward";

    protected $primaryKey = 'material_inward_id';

    public $timestamps = false;

    protected $fillable = [
        'material_inward_id',
        'current_location_id',
        'material_inward_sequence',
        'material_inward_no',
        'material_inward_date',
        'nabl_type_fix',
        'test_at_fix',
        'job_type_fix',
        'customer_id',
        'dc_no',
        'dc_date',
        'po_no',
        'po_date',
        'sample_drawn_by',
        'is_any_tpi_witness',
        'tpi_name',
        'condition_of_sample',
        'is_equipment_available',
        'competent_personnel_available',
        'is_test_sub_contracted',
        'test_feasible',
        'all_test_parameters_are_in_accredited_scope',
        'required_statement_of_conformity',
        'additional_requirement_from_customer',
        'special_note',
        'prepared_by_user_id',
        'assign_format_no',
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
