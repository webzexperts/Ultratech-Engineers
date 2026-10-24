<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class OfferDetails extends Model
{
    protected $table = "offer_details";

    protected $primaryKey = 'offer_details_id';

    public $timestamps = false;

    protected $fillable = [
        
        'offer_id',
        'process_type_fix',
        'type_of_testing_id_fix',
        'sr_no',
        'lab_id',
        'customer_id_no',
        'job_desc',
        'die_no',
        'heat_no',
        'drg_no',
        'material_id',
        'thickness',
        'quantity',
        'remark',
    ];
}
