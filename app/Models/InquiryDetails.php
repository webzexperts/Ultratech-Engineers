<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryDetails extends Model
{
    use HasFactory;

    protected $table = "inquiry_details";

    public $timestamps = false;

    protected $fillable = [
        'inqd_id',
        'inq_id',
        'inqd_test_method',
        'inqd_type_of_job_id',
        'inqd_job_description_id',
        'inqd_part_id',
        'inqd_part_no',
        'inqd_process_at',
        'inqd_description',
        'inqd_quantity',
        'inqd_unit_id',
        'inqd_feasibility_required',
        'inqd_estimation_required',
        'inqd_remark',
        'inqd_file_upload',
        'inqd_file_upload_blob_image',
        'status'
    ];
}