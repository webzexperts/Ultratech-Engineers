<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FindingLevel extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "finding_level";

    /**
     * Primary key (non-default, table uses `finding_level_id`)
     */
    protected $primaryKey = "finding_level_id";

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
        'finding_level_name',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
