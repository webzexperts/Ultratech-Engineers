<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmBrand extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "film_brand";

    /**
     * Primary key (non-default, table uses `film_brand_id`)
     */
    protected $primaryKey = "film_brand_id";

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
        'film_brand',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
