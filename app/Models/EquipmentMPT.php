<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMPT extends Model
{
       protected $table = 'equipment_mpt';
    public $timestamps = false;
    protected $primaryKey = 'em_id';

    protected $fillable = [
        'em_id',
        'em_equipment_name',
        'em_make',
        'em_serial_no',
        'em_cali_freq',
        'em_last_cali_date',
        'em_next_cali_due_date',
        'em_cali_certificate',
        'em_cali_certificate_blob',
        'status_transaction_id',
        'em_status',
        'own_location_id',
        'current_location_id',
        'table_unique_id',
        'table_pk_id',
        'item_id',
        'name_for_display',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];
}