<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = "offer";

    protected $primaryKey = 'offer_id';

    public $timestamps = false;

    protected $fillable = [
        'offer_id',
        'current_location_id',
        'offer_sequence',
        'offer_no',
        'offer_date',
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
        'authorized_id',
        'prepared_by_user_id',
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
