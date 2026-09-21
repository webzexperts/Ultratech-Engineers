<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ProductionEntryDetails extends Model
{
    protected $table = "production_entry_details";

    protected $primaryKey = 'production_entry_details_id';

    public $timestamps = false;

    protected $fillable = [
        'production_entry_details_id',
        'production_entry_id',
        'shift_id',
        'source_id_fix',
        'item_id',
        'production_sq_in',
        'retake_sq_in',
        'westage_in',
        'total_sq_in'
    ];
}
