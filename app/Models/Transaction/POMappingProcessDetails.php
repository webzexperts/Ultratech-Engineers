<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class POMappingProcessDetails extends Model
{
    /**
     * Table name
     */
    protected $table = "po_mapping_process_details";


    protected $primaryKey = 'po_mpd_id';

    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'po_mpd_id',
        'po_mpd_po_mp_id',
        'po_mpd_mins_id',
        'po_mpd_pmd_id',
        'po_mpd_inward_mapping_qty',
        'po_mpd_oa_mapping_qty',
        'po_mpd_remark',
    ];

}
