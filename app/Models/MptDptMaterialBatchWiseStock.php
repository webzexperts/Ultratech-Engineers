<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MptDptMaterialBatchWiseStock extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'mpt_dpt_material_batch_wise_stock';

    /**
     * Primary Key
     */
    protected $primaryKey = 'mpt_dpt_material_batch_wise_stock_id';

    /**
     * Disable default Laravel timestamps
     */
    public $timestamps = false;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'sr_table_unique_id',
        'sr_table_pk_id',
        'current_location_id',
        'stock_qty',
    ];
}
