<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialLimitStatistic extends Model
{

    protected $table = 'materials_limit_statistic';
    protected $fillable = [
        'material_id',
        'qty',
        'request_id',
        'installer_id'
    ];

    protected $guarded = ['id'];

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function suzRequest(): BelongsTo
    {
        return $this->belongsTo(SuzRequest::class);
    }
}
