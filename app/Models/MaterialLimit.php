<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialLimit extends Model
{
    protected $table = 'materials_limit';
    protected $fillable = [
        'material_id',
        'limit_qty'
    ];

    protected $guarded = [
        'id'
    ];

    public function getMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }
}
