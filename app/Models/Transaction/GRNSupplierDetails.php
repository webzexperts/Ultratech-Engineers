<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNSupplierDetails extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public $table = 'grn_supplier_details';

    protected $primaryKey = 'grnd_id';

    protected $hidden = ['cali_certificate_blob','previous_cali_certificate_blob'];

    protected $fillable = [
        'grnd_id',
        'grnd_grn_id',
        'table_unique_id',
        'table_pk_id',
        'grnd_item_id',
        'sr_table_unique_id',
        'sr_table_pk_id',
        'grnd_qty',
        'grnd_rate_unit',
        'grnd_amount',
        'grnd_remark',
        'last_cali_date',
        'next_cali_due_date',
        'cali_freq',
        'cali_certificate',
        'cali_certificate_blob',
        'previous_last_cali_date',
        'previous_next_cali_due_date',
        'previous_cali_freq',
        'previous_cali_certificate',
        'previous_cali_certificate_blob',
        'for_calibration',
        'current_status_transaction_id',
        'previous_status_transaction_id',
        'status',
    ];
}