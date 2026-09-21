<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimationCosting extends Model
{
     protected $table = "estimation_costing";

    protected $primaryKey = 'ec_id';

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
        'ec_id',
        'ec_sequence',
        'ec_number',
        'ec_date',
        'ec_inqd_id',
        'ec_fr_id',
        'ec_upload_file',
        'ec_upload_file_blob_image',
        'ec_estimation',
        'ec_costing',
        'ec_prepared_by_id',
        'ec_reviewed_by_id',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}