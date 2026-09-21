<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NABLConfiguration extends Model
{
    use HasFactory;

    protected $table = "nabl_configurations";

    public $timestamps = false;

    protected $primaryKey = 'nabl_id';

    protected $fillable = [
        'nabl_id',
        'nabl_location',
        'tc_no',
        'location_no',
        'nabl_type',
        'status',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}