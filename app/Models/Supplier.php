<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = "suppliers";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'supplier_name',
        'address',
        'city_id',
        'pincode',
        'phone_no',
        'email_id',
        'web_address',
        'GSTIN',
        'PAN',
        'TAN',
        'msme_reg_no',
        'payment_terms',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}