<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    use HasFactory;
    
    /**
     * Table name
     */
    protected $table = "menus";

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
        'page',
        'display_name',
        'sequence',
        'actions',
        'show_in_menu',
        'show_in_access',
        'parent',
        
    ];
}
