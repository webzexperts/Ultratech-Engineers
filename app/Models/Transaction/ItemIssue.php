<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ItemIssue extends Model
{
    //
    protected $table = 'item_issue';
    protected $primaryKey = 'issue_id';
    public $timestamps = false; 

    protected $fillable = [
        'issue_id ',
        'issue_sequence',
        'issue_number',
        'issue_date',
        'current_location_id',
        'issue_to',
        'special_note',
        'prepared_by_user_id',
        'assign_format_no',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',    
    ];
}
