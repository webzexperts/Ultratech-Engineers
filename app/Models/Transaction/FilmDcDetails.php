<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class FilmDcDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "film_dc_details";

    /**
     * Primary key
     */
    protected $primaryKey = 'film_dc_detail_id';

    /**
     * Disable Laravel default timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'film_dc_id',
        'test_report_rt_id',
        'test_report_no',
        'test_report_date',
        'die_no',
        'film_brand_id',
        'film_type_id',
        'film_id',
        'dc_quantity',
        'sq_in',
        'sq_cm',
        'total_sq_in',
        'total_sq_cm',
        'remark'
    ];
}
