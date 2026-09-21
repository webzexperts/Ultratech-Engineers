<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AerbDocument extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "aerb_documents";

    /**
     * Primary key (non-default, table uses `aerb_documents_id`)
     */
    protected $primaryKey = "aerb_documents_id";

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
        'aerb_documents_name',
        'aerb_documents_upload',
        'aerb_documents_upload_blob',
        'remark',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
