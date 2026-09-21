<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDetails extends Model
{
    use HasFactory;

    protected $table = "supplier_details";

    public $timestamps = false;

    protected $fillable = [
        'supd_details_id',
        'sup_id',
        'contact_person',
        'phone_no',
        'email_id',
    ];
}