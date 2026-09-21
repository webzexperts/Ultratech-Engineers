<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentUT extends Model
{
    protected $table = 'equipment_ut';
    public $timestamps = false;
    protected $primaryKey = 'eu_id';
    protected $hidden = ['eu_cali_certificate_blob'];

    protected $fillable = [
        'eu_id',
        'eu_equipment_name',
        'eu_make',
        'eu_display',
        'eu_serial_no',
        'eu_cali_freq',
        'eu_last_cali_date',
        'eu_next_cali_due_date',
        'eu_doc_ref_no',
        'eu_cali_certificate',
        'eu_cali_certificate_blob',
        'eu_status',
        'status_transaction_id',
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
