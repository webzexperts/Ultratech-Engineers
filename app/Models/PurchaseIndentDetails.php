<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseIndentDetails extends Model
{
    protected $table = 'purchase_indent_details';
    protected $primaryKey = 'pid_id';
    public $timestamps = false; 

    protected $fillable = [

        'pid_pi_id',
        'item_id',
        'indent_qty',
        'remark',
    ];
}
