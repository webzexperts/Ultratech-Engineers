<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorityPerson extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "authority_person";

    /**
     * Primary key (non-default, table uses `authority_person_id`)
     */
    protected $primaryKey = "authority_person_id";

    /**
     * disable laravel default timestamps field
     */
    public $timestamps = false;

    protected $hidden = [
        'signature_blob',
        'certificate_blob'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'current_location_id',
        'authority_person_type_value_fix',
        'validity',
        'pms_no',
        'certificate',
        'certificate_blob',
        'operator',
        'designation',
        'signature',
        'signature_blob',
        'status',
        'tested_by',
        'reviewed_by',
        'authorized_by',
        'checked_by',
        'approved_by',
        'operator_type',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
