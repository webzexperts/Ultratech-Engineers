<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMTPConfiguration extends Model
{
    use HasFactory;

    protected $table = "smtp_configurations";

    protected $primaryKey = 'sc_id';

    public $timestamps = false;

    protected $fillable = [
        'sc_id',
        'email',
        'cc_email',
        'password',
        'mail_host',
        'out_port_no',
        'enable_ssl',
        'reply_email',
        'purchase',
        'company_id',
        'created_by',
        'created_on',
        'last_by',
        'last_on',
        'locked_by',
        'locked_on'
    ];
}
