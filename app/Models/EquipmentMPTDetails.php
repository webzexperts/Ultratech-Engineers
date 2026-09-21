<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMPTDetails extends Model
{
    protected $table = 'equipment_mpt_details';

    protected $primaryKey = 'emd_id';

    public $timestamps = false; 

    protected $fillable = [
        'emd_em_id',
        'emd_calibration_due_date',
        'status',
    ];
}
