<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoapSettings extends Model
{
    protected $table = 'soap_settings';
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'code',
        'enabled'
    ];

    public $timestamps = [
        'created_at',
        'updated_at'
    ];
}
