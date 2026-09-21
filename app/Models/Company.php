<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "companies";

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
        'company_name',
        'address',
        'country_id',
        'city',
        'state',
        'mobile_no',
        'email',
        'web_address',
        'gstin',
        'pan',
        'cin',
        'bin',
        'exporters_ref',
        'company_logo',
        'stamp',
        'path_company_logo',
        'path_stamp',
        'other_logo_1',
        'other_logo_2',
        'other_logo_3',
        'export_value_declaration',
        'contract_note_terms_and_condition',
        'ilca_logo',
        'created_by',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
        'created_on'
    ];
}