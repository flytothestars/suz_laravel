<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitRequest extends Model
{
    protected $table = 'kit_request';
    protected $guarded = ['id'];
    protected $fillable = [
        'kit_id',
        'v_serial',
        'author_id',
        'user_id',
        'username',
        'stock_id',
        'stockname',
        'from_stock',
        'from_stockname',
    ];
}
