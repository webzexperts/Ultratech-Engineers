<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RTCamera extends Model
{
    use HasFactory;

    protected $table = "rt_camera";

    public $timestamps = false;
    
    protected $primaryKey = 'rt_camera_id';

    protected $hidden = [
        'movement_approval_blob',
    ];


    protected $fillable = [
       'rt_camera_id',
        'rt_camera_name',
        'rt_serial_no',
        'rt_isotope',
        'rt_x_ray',
        'rt_focal_spot',
        'rt_document_ref_no',
        'rt_validity_date',
        'rt_aerb_no',
        'application_no',
        'movement_approval',
        'movement_approval_blob',
        'validity',
        'rt_status',
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
        'locked_on'
    ];
}