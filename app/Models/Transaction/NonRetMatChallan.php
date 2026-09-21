<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class NonRetMatChallan extends Model
{

     /**
     * Table name
     */
    protected $table = "non_ret_mat_challan";

    public $timestamps = false;

    protected $primaryKey = 'nrmc_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        
        'nrmc_sequence',
        'nrmc_number',
        'nrmc_date',
        'nrmc_customer_id',
        'nrmc_transporter',
        'nrmc_vehicle_number',
        'nrmc_lr_no_date',
        'nrmc_special_note',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];

}
