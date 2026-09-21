<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstConfigurationDetails extends Model
{
    use HasFactory;

    protected $table = "gst_configuration_details";

    protected $primaryKey = 'gcd_details_id';

    public $timestamps = false;

    protected $fillable = [
        'gcd_details_id',
        'gc_id',
        'tcd_taxtype_fix_id',
        'tcd_taxtype_name',
        'gcd_tax_per',
        'gcd_effective_date',
        'gcd_remarks'
    ];
}