<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseIndentShortClose extends Model
{
    
    protected $table = "purchase_indent_short_close";
    protected $primaryKey = 'pisc_id';
    public $timestamps = false;

    protected $fillable = [
        'pisc_date',
        'pisc_pid_id',
        'pisc_sc_qty',
        'pisc_sc_reason_id',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by'
    ];
}
