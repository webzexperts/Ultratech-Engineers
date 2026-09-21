<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $table = "customers";

    public $timestamps = false;

    protected $fillable = [
        'customer_code',
        'person_type',
        'customer',
        'address',
        'city_id',
        'pin_code',
        'phone_no',
        'email',
        'web_address',
        'gstin',
        'pan',
        'tan',
        'msme_reg_no',
        'credit_days',
        'payment_terms',
        'user_name',
        'password',
        'normal_password',
        'status',
        'allow_for',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];

    protected $hidden = [
        'password',
    ];
}