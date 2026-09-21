<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class OrderAcceptance extends Model
{
    /**
     * Table name
     */
    protected $table = "order_acceptance";

    public $timestamps = false;

    protected $primaryKey = 'oa_id';

    protected $hidden = ['oa_image'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [

        'oa_sequence',
        'oa_number',
        'oa_date',
        'oa_customer_id',
        'oa_type_id',
        'oa_po_number',
        'oa_po_date',
        'oa_kind_attn_id',
        'oa_file_upload',
        'oa_image',
        'oa_special_note',
        'oa_copy_from_id',
        'oa_terms_and_conditions',
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
