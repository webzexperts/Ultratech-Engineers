<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ProductionEntry extends Model
{
    protected $table = "production_entry";

    protected $primaryKey = 'production_entry_id';

    public $timestamps = false;

    protected $fillable = [
        'production_entry_id',
        'current_location_id',
        'production_entry_sequence',
        'production_entry_no',
        'production_entry_date',
        'enclosure_id',
        'total_sq_in',
        'remark',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
    ];
}
