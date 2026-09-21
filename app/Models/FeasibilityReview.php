<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeasibilityReview extends Model
{
    use HasFactory;
    
    protected $table = "feasibility_review";

    protected $primaryKey = 'fr_id';

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
        'fr_id',
        'fr_sequence',
        'fr_number',
        'fr_date',
        'fr_inqd_id',
        'fr_result_id',
        'fr_result_id',
        'fr_feasibility_review',
        'fr_suggest_method_id',
        'fr_reason_id',
        'fr_estimation',
        'fr_costing',
        'fr_upload_file',
        'fr_upload_file_blob_image',
        'fr_prepared_by_id',
        'fr_reviewed_by_id',
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
