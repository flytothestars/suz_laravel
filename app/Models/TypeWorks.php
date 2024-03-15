<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeWorks extends Model
{
    protected $table = 'alma_type_works';

    protected $guarded = ['id'];

    protected $fillable = [
        'id_type_work',
        'v_name',
        'id_kind_work_inst'
    ];
}
