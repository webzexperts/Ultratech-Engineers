<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class CustomerDCNonReturnableDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "customer_dc_non_returnable_details";

    public $timestamps = false;

    protected $primaryKey = 'customer_dc_non_returnable_details_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_dc_non_returnable_id',
        'material_inward_details_id',
        'dc_qty',
        'remark',
    ];
}
