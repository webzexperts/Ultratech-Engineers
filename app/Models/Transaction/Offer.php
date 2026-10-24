<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = "offer";

    protected $primaryKey = 'offer_id';

    public $timestamps = false;

    protected $fillable = [

        'current_location_id',
        'offer_sequence',
        'offer_no',
        'offer_date',
        'nabl_type_fix',
        'test_at_fix',
        'job_type_fix',
        'customer_id',
        'challan_no',
        'challan_date',
        'material_type_id',
        'total_qty',
        'test_method_id',
        'procedure_reference_id',
        'evaluation_as_per_id',
        'acceptance_standard_id',
        'radiation_energy_fix',
        'coverage_area',
        'procedure_submitted_fix',
        'del_date',
        'sp_note',
        'test_witness_tpi_fix',
        'tpi_person',
        'test_items_condition',
        'public_domain_agree_fix',
        'conformity_statement_fix',
        'test_methods_defined_fix',
        'laboratory_capability_fix',
        'appropriate_test_method_fix',
        'pertinent_discussions',
        'deviation_intimate',
        'contract_review_repeated_fix',
        'authorized_id_fix',
        'authorized_created_by',
        'authorized_created_on',
        'authorized_last_by',
        'authorized_last_on',
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
