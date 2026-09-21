<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IqiDesignation extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "iqi_designation";

    /**
     * Primary key (non-default, table uses `iqi_designation_id`)
     */
    protected $primaryKey = "iqi_designation_id";

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
        'iqi_designation',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
