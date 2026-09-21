<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    /**
     * Table name
     */
    protected $table = "part";

    protected $primaryKey = 'part_id';

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
        'part_id',
        'job_desc_id',
        'part_no',
        'drg_no',
        'company_id',
        'created_by',
        'last_by',
        'locked_by',
        'created_on',
        'last_on',
        'locked_on'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['part'];

    /**
     * Get the part formatted name.
     *
     * @return string
     */
    public function getPartAttribute()
    {
        return (!empty($this->drg_no) && trim($this->drg_no) !== '') 
            ? $this->part_no . ' - ' . $this->drg_no 
            : $this->part_no;
    }
}
