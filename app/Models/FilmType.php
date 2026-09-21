<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmType extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "film_type";

    /**
     * Primary key (non-default, table uses `film_type_id`)
     */
    protected $primaryKey = "film_type_id";

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
        'film_type',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
