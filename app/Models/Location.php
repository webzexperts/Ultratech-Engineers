<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = "location";

    public $timestamps = false;

    protected $primaryKey = 'location_id';

    protected $fillable = [
        'location_id',
        'location_name',
        'location_type',
        'location_code',
        'gst_bill_location',
        'location_company_name',
        'location_address',
        'location_country_id',
        'location_state_id',
        'location_city_id',
        'location_pin_code',
        'location_phone_no',
        'location_email_id',
        'location_gstin',
        'location_pan',
        'location_nabl_applicable',
        'nabl_id',
        'location_nabl_symbol',
        'location_nabl_symbol_blob',
        'location_nabl_test',
        'location_ilac_applicable',
        'location_ilac_symbol',
        'location_ilac_symbol_blob',
        'location_header_address',
        'location_status',
        'show_all_location_camera',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}