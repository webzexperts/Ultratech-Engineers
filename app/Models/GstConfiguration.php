<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstConfiguration extends Model
{
    use HasFactory;

    protected $table = "gst_configuration";

    protected $primaryKey = 'gc_id';

    public $timestamps = false;

    protected $fillable = [
        'gc_id',
        'gc_sac',
        'gc_description',
        'gc_remarks',
        'pincode',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}