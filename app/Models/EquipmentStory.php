<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentStory extends Model
{
    protected $table = 'equipment_story';
    protected $guarded = ['id'];
    protected $fillable = [
        'equipment_id',
        'owner_id',
        'author_id',
        'stock_id'
    ];
}
