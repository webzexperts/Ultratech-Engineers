<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DPTChemical extends Model
{
    protected $table = 'dpt_chemical';
    public $timestamps = false;
    protected $primaryKey = 'dpt_id';

    protected $fillable = [
        'dpt_chemical',
        'dpt_designation',
        'dpt_make',
        'dpt_batch_no',
        'dpt_identification_no',
        'dpt_expiry_date',
        'dpt_qty',
        'dpt_status',
        'own_location_id',
        'current_location_id',
        'table_unique_id',
        'table_pk_id',
        'item_id',
        'name_for_display',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];
}
