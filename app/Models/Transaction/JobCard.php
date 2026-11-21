<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\JobCardDetails;
use App\Models\Shift;
use App\Models\AuthorityPerson;
use App\Models\RTCamera;

class JobCard extends Model
{
    protected $table = "job_card";

    protected $primaryKey = 'job_card_id';

    public $timestamps = false;

    protected $fillable = [
        'job_card_id',
        'current_location_id',
        'job_card_sequence',
        'job_card_no',
        'job_card_date',
        'shift_id',
        'incharge_authority_person_id',
        'radiographer_authority_person_id',
        'rt_camera_id',
        'company_id',
        'assign_format_no',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];

    public function details()
    {
        return $this->hasMany(JobCardDetails::class, 'job_card_id', 'job_card_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'shift_id');
    }

    public function incharge()
    {
        return $this->belongsTo(AuthorityPerson::class, 'incharge_authority_person_id', 'authority_person_id');
    }

    public function radiographer()
    {
        return $this->belongsTo(AuthorityPerson::class, 'radiographer_authority_person_id', 'authority_person_id');
    }

    public function rtCamera()
    {
        return $this->belongsTo(RTCamera::class, 'rt_camera_id', 'rt_camera_id');
    }
}
