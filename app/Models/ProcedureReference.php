<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureReference extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = "procedure_reference";

    /**
     * Primary key (non-default, table uses `procedure_reference_id`)
     */
    protected $primaryKey = "procedure_reference_id";

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
        'procedure_reference',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];
}
