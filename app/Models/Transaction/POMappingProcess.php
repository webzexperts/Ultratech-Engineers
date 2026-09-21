<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class POMappingProcess extends Model
{
    /**
     * Table name
     */
    protected $table = "po_mapping_process";


    protected $primaryKey = 'po_mp_id';

    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'po_mp_id',
        'po_mp_sequence',
        'po_mp_number',
        'po_mp_date',
        'po_mp_description',
        'po_mp_mapped_by_id',
        'po_mp_total_mapping_no',
        'po_mp_special_note',
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
