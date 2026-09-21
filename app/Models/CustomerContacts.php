<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContacts extends Model
{
    use HasFactory;

     /**
     * Table name
     */
    protected $table = "customer_contacts";

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
        'customer_id',
        'contact_person',
        'contact_designation',
        'contact_phone_no',
        'contact_mobile_no',
        'contact_email',
        'send_sms_for',
        'send_email_for'
    ];
}