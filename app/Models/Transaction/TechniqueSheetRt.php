<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TechniqueSheetRt extends Model
{
    protected $table = "technique_sheet_rt";

    protected $primaryKey = 'technique_sheet_rt_id';

    protected $hidden = ['shooting_sketch_image_blob'];

    public $timestamps = false;

    protected $fillable = [
        'current_location_id',
        'technique_sheet_rt_id',
        'technique_sheet_rt_sequence',
        'technique_sheet_rt_no',
        'technique_sheet_rt_date',
        'entry_type_fix',
        'test_report_rt_id',
        'customer_id',
        'type_of_job_id',
        'job_desc_id',
        'part_id',
        'part_no',
        'drg_no',
        'area_of_coverage_id',
        'source_used',
        'source_size',
        'xray_focal_size',
        'lead_screen_thick',
        'lead_screen_thick_back',
        'iqi',
        'film_processing',
        'test_technique',
        'test_arrangement',
        'test_class',
        'film_brand',
        'film_type',
        'procedure_ref_id',
        'evaluation_as_per_id',
        'acceptance_standard_id',
        'customer_procedure_ref',
        'shooting_sketch_image',
        'shooting_sketch_image_blob',
        'film_size_fix',
        'sfd_unit_fix',
        'no_of_films',
        'film_size',
        'total_area',
        'prepared_by_user_id',
        'checked_by_authority_person_id',
        'authorized_by_authority_person_id',
        'sp_note',
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
