<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enclosure extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "enclosure";

    /**
     * Primary key (non-default, table uses `enclosure_id`)
     */
    protected $primaryKey = "enclosure_id";

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
        'enclosure_name',
        'current_location_id',
        'enclosure_type_value_fix',
        'enclosure_no',
        'enclosure_layout',
        'enclosure_layout_blob',
        'enclosure_permission_to_use',
        'enclosure_permission_to_use_blob',
        'enclosure_validity',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
