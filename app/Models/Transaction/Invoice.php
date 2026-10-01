<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\State;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;

    protected $fillable = [
        'current_location_id',
        'invoice_sequence',
        'invoice_no',
        'invoice_date',
        'ref_title',
        'ref_no',
        'ref_date',
        'job_type_fix',
        'film_size_unit_fix',
        'source_type',
        'customer_id',
        'customer_client',
        'tax_type',
        'sac_id',
        'pos_customer',
        'pos_address',
        'pos_state_id',
        'pos_gstin_no',
        'pos_pan_no',
        'basic_amount',
        'discount_amount',
        'value_of_goods',
        'sgst_percent',
        'sgst_amount',
        'cgst_percent',
        'cgst_amount',
        'igst_percent',
        'igst_amount',
        'other_charges',
        'sub_total',
        'round_off',
        'net_amount',
        'due_days',
        'due_date',
        'company_id',
        'year_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on',
    ];

    public function details()
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id', 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'pos_state_id', 'id');
    }
}
