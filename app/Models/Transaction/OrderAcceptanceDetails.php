<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class OrderAcceptanceDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "order_acceptance_details";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;

    protected $primaryKey = 'oad_id';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        
        'oad_oa_id',
        'oad_quotd_id',
        'oad_test_method_id',
        'oad_type_of_job_id',
        'oad_job_desc_id',
        'oad_part_id',
        'oad_description',
        'oad_process_at_id',
        'oad_qty',
        'oad_unit_id',
        'oad_rate_unit',
        'oad_rate_unit_id',
        'oad_minimum_charge',
        'oad_minimum_charge_unit_id',
        'oad_conveyance_charge',
        'oad_remark',
        'oad_status',
    ];

}
