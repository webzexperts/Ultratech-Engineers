<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TechniqueSheetRtDetails extends Model
{
    public $timestamps = false;

    public $table = 'technique_sheet_rt_details';

    protected $primaryKey = 'technique_sheet_rt_details_id';

    protected $fillable = [
        'technique_sheet_rt_details_id',
        'technique_sheet_rt_id',
        'sr_no',
        'identification',
        'location',
        'source_id_fix',
        'film_brand_id',
        'film_type_id',
        'thickness',
        'sfd',
        'iqi_designation',
        'iqi_sensitivity',
        'iqi_designation_id',
        'iqi_sensitivity_id',
        'film_id',
        'no_of_film_fix',
        'film_qty',
        'sq_in',
        'sq_cm',
        'total_sq_in',
        'total_sq_cm',
        'test_technique',
        'film_position',
    ];

    public function techniqueSheet()
    {
        return $this->belongsTo(TechniqueSheetRt::class, 'technique_sheet_rt_id', 'technique_sheet_rt_id');
    }
}
