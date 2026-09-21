<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialInspection extends Model
{
    protected $table = 'material_inspection';

    protected $primaryKey = 'mins_id';

    public $timestamps = false;

    protected $fillable = [
        'mins_sequence',
        'mins_number',
        'mins_date',
        'mins_mid_id',
        'mins_description',
        'mins_insp_qty',
        'mins_result',
        'mins_rej_reason',
        'mins_inspected_by_id',
        'mins_special_note',
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
