<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FilmBrand;
use App\Models\Film;
use App\Models\Unit;

class InvoiceDetails extends Model
{
    use HasFactory;

    protected $table = 'invoice_details';
    protected $primaryKey = 'invoice_detail_id';
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'film_dc_id',
        'film_dc_detail_id',
        'dc_no',
        'dc_date',
        'test_report_no',
        'description',
        'film_brand_id',
        'film_id',
        'film_size',
        'film_qty',
        'qty',
        'unit_id',
        'unit_name',
        'rate',
        'amount',
        'remark',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id', 'film_brand_id');
    }

    public function film()
    {
        return $this->belongsTo(Film::class, 'film_id', 'film_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
