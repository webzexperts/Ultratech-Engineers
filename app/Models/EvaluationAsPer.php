<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAsPer extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "evaluation_as_per";

    /**
     * Primary key (non-default, table uses `evaluation_as_per_id`)
     */
    protected $primaryKey = "evaluation_as_per_id";

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
        'evaluation_as_per',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
