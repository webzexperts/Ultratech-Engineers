<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "item";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'item_name',
        'item_group_id',
        'unit_id',
        'conv_factor',
        'inter_location_transfer',
        'min_stock_level',
        'document_ref_no',
        'validity_date',
        'item_type',
        'identification_req',
        'status',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}