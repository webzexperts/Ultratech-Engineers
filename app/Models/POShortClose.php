<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class POShortClose extends Model
{
    protected $table = "po_short_close";

    public $timestamps = false;

    protected $primaryKey = 'po_sc_id';

    protected $fillable = [
        
        'po_sc_pod_id',
        'po_sc_date',
        'po_sc_qty',
        'po_sc_regret_reason_id',
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
