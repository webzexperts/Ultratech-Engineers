<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class FilmDc extends Model
{
    /**
     * Table name
     */
    protected $table = "film_dc";

    /**
     * Primary key
     */
    protected $primaryKey = 'film_dc_id';

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
        'current_location_id',
        'film_dc_sequence',
        'film_dc_no',
        'film_dc_date',
        'from_type_id',
        'job_type_fix',
        'film_size_unit_fix',
        'customer_id',
        'customer_client',
        'total_qty',
        'total_square_inch',
        'film_size_for_print',
        'sp_note',
        'assign_format_no',
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
