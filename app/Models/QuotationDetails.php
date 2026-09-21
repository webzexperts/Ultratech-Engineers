<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationDetails extends Model
{
    protected $table = 'quotation_details';

    protected $primaryKey = 'quotd_id';

    public $timestamps = false;

    protected $fillable = [
        'quotd_quot_id',
        'quotd_inqd_id',
        'quotd_ec_id',
        'quotd_test_method_id',
        'quotd_type_of_job_id',
        'quotd_job_desc_id',
        'quotd_part_id',
        'quotd_part_no',
        'quotd_process_at_id',
        'quotd_description',
        'quotd_qty',
        'quotd_unit_id',
        'quotd_rate_unit',
        'quotd_rate_unit_id',
        'quotd_minimum_charge',
        'quotd_minimum_charge_id',
        'quotd_conveyance_charge',
        'quotd_remark',
        'status'
    ];

}
