<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ObservationSheetDetailsPrint extends Model
{
    protected $table = "observation_sheet_details_details_print";

    protected $primaryKey = 'observation_sheet_details_details_print_id';

    public $timestamps = false;

    protected $fillable = [
        'observation_sheet_details_details_print_id',
        'observation_sheet_details_id',
        'sr_no',
        'identification',
        'location',
        'source_id_fix',
        'film_brand_id',
        'film_type_id',
        'thickness',
        'sfd',
        'iqi_designation_id',
        'iqi_sensitivity_id',
        'iqi_designation',
        'iqi_sensitivity',
        'film_id',
        'no_of_film_fix',
        'film_qty',
    ];
}