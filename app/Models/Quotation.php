<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotation';

    protected $primaryKey = 'quot_id';

    public $timestamps = false; // because you are using created_on / last_on

    protected $hidden = ['quot_image'];


    protected $fillable = [
        'quot_sequence',
        'quot_number',
        'quot_date',
        'quot_customer_id',
        'quot_kind_attn_id',
        'quot_sac_id',
        'quot_ref_no_date',
        'quot_offer_validity',
        'quot_prepared_by_id',
        'quot_authorised_by_id',
        'quot_special_note',
        'quot_copy_from_id',
        'quot_terms_and_conditions',
        'quot_revision_doc',
        'quot_image',
        'assign_format_no',
        'year_id',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];

}