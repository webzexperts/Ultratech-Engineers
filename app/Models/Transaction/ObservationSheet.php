<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ObservationSheet extends Model
{
    protected $table = "observation_sheet";

    protected $primaryKey = 'observation_sheet_id';

    public $timestamps = false;

    protected $fillable = [
        'observation_sheet_id',
        'current_location_id',
        'observation_sheet_sequence',
        'observation_sheet_no',
        'observation_sheet_date',
        'customer_id',
        'prepared_by_user_id',
        'sp_note',
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