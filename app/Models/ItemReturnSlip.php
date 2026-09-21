<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturnSlip extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "item_return_slip";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;

    protected $primaryKey = 'irs_id';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'irs_id',
        'irs_sequence',
        'irs_number',
        'irs_date',
        'irs_type_id',
        'irs_employee_id',
        'irs_special_note',
        'company_id',
        'year_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}