<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMaterial extends Model
{
    protected $table = 'stock_material';
    protected $guarded = ['id'];
    protected $fillable = [
        'material_id',
        'stock_id',
        'qty'
    ];
}
