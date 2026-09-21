<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class PlanningManagementDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "planning_management_details";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;

    protected $primaryKey = 'pmd_id';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'pmd_id',
        'pmd_pm_id',
        'pmd_oad_id',
        'pmd_plan_qty',
        'pmd_status',
    ];

}
