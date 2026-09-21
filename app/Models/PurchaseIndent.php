<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseIndent extends Model
{

    protected $table = 'purchase_indent';
    protected $primaryKey = 'pi_id';
    public $timestamps = false; 

    protected $fillable = [

        'pi_sequence',
        'pi_no',   
        'pi_date',
        'current_location_id',
        'to_location_id',
        'indent_by_user_id',
        'special_note',
        'assign_format_no',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}
