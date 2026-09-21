<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmResult extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "film_result";

    /**
     * Primary key (non-default, table uses `film_result_id`)
     */
    protected $primaryKey = "film_result_id";

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
        'film_result_name',
        'film_result_type_fix',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
