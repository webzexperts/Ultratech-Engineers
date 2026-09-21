<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProbeUT extends Model
{
    protected $table = "probe_ut";

    protected $primaryKey = 'pu_id';

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
        'pu_probe',
        'pu_serial_number',
        'pu_size_of_probe',
        'pu_ref_angle',
        'pu_frequency',
        'pu_status',
        'own_location_id',
        'current_location_id',
        'table_unique_id',
        'table_pk_id',
        'item_id',
        'name_for_display',
        'status_transaction_id',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];

}

