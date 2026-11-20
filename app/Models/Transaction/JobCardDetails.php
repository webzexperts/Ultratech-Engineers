<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\JobCard;
use App\Models\Transaction\ObservationSheetDetails;

class JobCardDetails extends Model
{
    protected $table = "job_card_details";

    protected $primaryKey = 'job_card_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'job_card_detail_id',
        'job_card_id',
        'observation_sheet_details_id',
        'priority_id_fix',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id', 'job_card_id');
    }

    public function worksheetDetail()
    {
        return $this->belongsTo(ObservationSheetDetails::class, 'observation_sheet_details_id', 'observation_sheet_details_id');
    }
}
