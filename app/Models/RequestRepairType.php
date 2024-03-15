<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestRepairType extends Model
{
    protected $guarded = ['id'];
    protected $table = 'request_repair_type';
    protected $fillable = [
        'id_type',
        'request_id'
    ];
}
