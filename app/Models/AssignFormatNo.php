<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignFormatNo extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "assign_format_no";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $primaryKey = 'assign_id';
    protected $fillable = [
        'assign_id',
        'assign_location_id',
        'assign_format_name',
        'assign_format_no',
        'page_id',
        'rpt_name',
        'assign_effect_date',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}