<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class NonRetMatChallanDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "non_ret_mat_challan_details";


    protected $primaryKey = 'nrmcd_id';

    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        
        'nrmcd_nrmc_id',
        'nrmcd_mid_id',
        'nrmdc_mins_id',
        'nrmcd_qty',
        'nrmdc_remark',
        'nrmcd_type',
        'nrmdc_status',
    ];

}
