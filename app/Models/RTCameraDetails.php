<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RTCameraDetails extends Model
{
    use HasFactory;

    protected $table = "rt_camera_details";

    public $timestamps = false;

    protected $hidden = [
        'rtcd_decay_chart_blob',
    ];

    protected $fillable = [
        'rtcd_id',
        'rtcd_rt_camera_id',
        'rtcd_last_of_loading_date',
        'rtcd_initial_activity_ci',
        'rtcd_source_size',
        'rtcd_pencil_no',
        'rtcd_iga_no',
        'rtcd_decay_chart',
        'rtcd_decay_chart_blob',
        'status'
    ];
}