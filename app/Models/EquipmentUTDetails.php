<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentUTDetails extends Model
{
    protected $table = 'equipment_ut_details';

    protected $primaryKey = 'eud_id';

    public $timestamps = false; 

    protected $fillable = [
        'eud_eu_id',
        'eud_calibration_due_date',
        'status',
    ];
}
