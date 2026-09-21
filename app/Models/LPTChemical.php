<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LPTChemical extends Model
{
    use HasFactory;

    protected $table = "lpt_chemical";

    public $timestamps = false;

    protected $fillable = [
        'lpt_id',
        'lpt_inward_type',
        'lpt_chemical_id',
        'lpt_chemical_name',
        'lpt_designation',
        'lpt_make',
        'lpt_batch_no',
        'lpt_identification_no',
        'lpt_mfg_date',
        'lpt_status',
        'lpt_grnd_id',
        'lpt_document_ref_no',
        'lpt_validity_date',
        'lpt_remark',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}