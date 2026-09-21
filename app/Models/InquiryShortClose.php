<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryShortClose extends Model
{
    protected $table = "inquiry_short_close";

    public $timestamps = false;

    protected $primaryKey = 'inq_sc_id';

    protected $fillable = [
        'inq_sc_inqd_id',
        'inq_sc_date',
        'inq_sc_qty',
        'inq_sc_regret_reason_id',
        'inq_sc_special_note',
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
