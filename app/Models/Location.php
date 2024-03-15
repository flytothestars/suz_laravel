<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'alma_location';

    public function users()
    {
    	return $this->belongsToMany('App\Models\User');
    }
}
