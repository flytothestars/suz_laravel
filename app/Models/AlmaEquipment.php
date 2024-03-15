<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmaEquipment extends Model
{
    protected $table = 'alma_equipment_model';

    public function modelType(): BelongsTo
    {
        return $this->belongsTo(AlmaEquipmentType::class, 'id_equipment_type','id_equipment_type');
    }
}
