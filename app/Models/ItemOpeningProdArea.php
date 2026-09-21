<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOpeningProdArea extends Model
{
    use HasFactory;

    protected $table = 'item_opening_prod_area';
    protected $primaryKey = 'item_opening_prod_area_id';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'opening_sq_in',
        'stock_sq_in',
        'current_location_id',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}
