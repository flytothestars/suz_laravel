<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairCount extends Model
{
    protected $table = 'repair_count';
    protected $guarded = ['id'];
    protected $fillable = [
        'id_house',
        'count'
    ];
}
