<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "film";

    /**
     * Primary key (non-default, table uses `film_id`)
     */
    protected $primaryKey = "film_id";

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
        'film_size_inch',
        'length_inch',
        'width_inch',
        'sq_in',
        'film_size_cm',
        'length_cm',
        'width_cm',
        'sq_cm',
        'status',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
