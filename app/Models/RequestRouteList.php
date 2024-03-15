<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestRouteList extends Model
{
    protected $table = 'request_route_list';
    protected $guarded = ['id'];
    protected $fillable = [
        'request_id',
        'routelist_id',
        'time'
    ];
}
