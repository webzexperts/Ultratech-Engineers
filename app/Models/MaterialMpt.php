<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialMpt extends Model
{
     protected $table = "material_mpt";

    protected $primaryKey = 'mm_id';

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'mm_material',
        'mm_material_make',
        'mm_batch_no',
        'mm_identification_no',
        'mm_expiry_date',
        'mm_status',
        'mm_qty',
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