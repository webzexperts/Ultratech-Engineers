<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $table = 'error_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'operation',
        'section',
        'message',
        'code',
        'file',
        'line_number',
        'url',
        'created_at',
    ];
}
