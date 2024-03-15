<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMaterial extends Model
{
    protected $table = 'user_material';
    protected $with = ['material'];

    protected $fillable = [
        'user_id',
        'material_id',
        'qty'
    ];
    protected $guarded = ['id'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
