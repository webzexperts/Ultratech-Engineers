<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaOfCoverage extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "area_of_coverage";

    /**
     * Primary key (non-default, table uses `area_of_coverage_id`)
     */
    protected $primaryKey = "area_of_coverage_id";

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
        'area_of_coverage',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
