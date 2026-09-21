<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IqiSensitivity extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "iqi_sensitivity";

    /**
     * Primary key (non-default, table uses `iqi_sensitivity_id`)
     */
    protected $primaryKey = "iqi_sensitivity_id";

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
        'iqi_sensitivity',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
