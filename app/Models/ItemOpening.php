<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ItemOpening extends Model
{
    
 use HasFactory;

    /**
     * Table name
     */
    protected $table = "item_opening";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

     protected $primaryKey = 'io_id'; // Adjust this if the primary key is something other than `id`


    protected $fillable = [
        'io_item_id',
        'io_opening_qty',
        'io_stock_qty',
        'io_opening_rate_unit',
        'io_opening_amount',
        'io_stock_rate_unit',
        'io_stock_amount',
        'current_location_id',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];

}
