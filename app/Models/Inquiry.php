<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $table = "inquiry";

    public $timestamps = false;

    protected $fillable = [
        'inq_id',
        'inq_sequence',
        'current_location_id',
        'inq_number',
        'inq_date',
        'inq_customer_id',
        'inq_kind_attn_id',
        'inq_ref_no_date',
        'inq_sp_note',
        'inq_prepared_by_id',
        'assign_format_no',
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