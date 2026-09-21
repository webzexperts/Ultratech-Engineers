<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class PlanningManagement extends Model
{
    /**
     * Table name
     */
    protected $table = "planning_management";

    public $timestamps = false;

    protected $primaryKey = 'pm_id';

    // protected $hidden = ['oa_image'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'pm_id',
        'pm_sequence',
        'pm_number',
        'pm_date',
        'pm_process_at',
        'pm_completion_avrg_period',
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
