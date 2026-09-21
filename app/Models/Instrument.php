<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{


    protected $table = "instrument";
    protected $primaryKey = 'ins_id';
    public $timestamps = false;
    protected $hidden = ['ins_cali_certificate_blob'];

    protected $fillable = [
        'ins_instrument_no',
        'ins_instrument_name',
        'ins_make',
        'ins_mfg_sr_no',
        'ins_last_cali_date',
        'ins_next_cali_due_date',
        'ins_doc_ref_no',
        'ins_cali_req',
        'ins_cali_freq',
        'ins_cali_certificate',
        'ins_cali_certificate_blob',
        'own_location_id',
        'current_location_id',
        'ins_status',
        'status_transaction_id',
        'table_unique_id',
        'table_pk_id',
        'item_id',
        'name_for_display',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by'
    ];
}
