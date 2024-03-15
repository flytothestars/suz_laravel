<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fw_departments';

    public function users()
    {
    	return $this->belongsToMany('App\Models\User');
    }
}
