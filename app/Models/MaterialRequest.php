<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $table = 'materials_request';
    protected $guarded = ['id'];
    protected $fillable = [
        'material_id',
        'user_id',
        'username',
        'author_id',
        'author_name',
        'stock_id',
        'stockname',
        'qty',
        'from_stock',
        'from_stockname',
    ];
}
