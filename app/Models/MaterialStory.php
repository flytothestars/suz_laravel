<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialStory extends Model
{
    protected $table = 'materials_story';
    protected $guarded = ['id'];
    protected $fillable = [
        'material_id',
        'owner_id',
        'author_id',
        'stock_id',
        'qty',
        'request_id',
        'id_flow',
        'returned',
        'incoming',
        'from',
        'from_stock'
    ];

}
