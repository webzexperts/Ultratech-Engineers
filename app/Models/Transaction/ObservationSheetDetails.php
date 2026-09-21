<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ObservationSheetDetails extends Model
{
    protected $table = "observation_sheet_details";

    protected $primaryKey = 'observation_sheet_details_id';

    public $timestamps = false;

    protected $fillable = [
        'observation_sheet_details_id',
        'observation_sheet_id',
        'material_inward_details_id',
        'technique_sheet_rt_id',
        'test_report_rt_id',
        'from_type_id_fix',
        'observation_qty',
        'process_type',
        'type_of_testing_id_fix',
        'film_size_unit_fix'
    ];
}
