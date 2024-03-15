<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;

trait AdminTrait
{
    private $soap_settings_table = "soap_settings";

    public function getSoapSettings()
    {
        return DB::table($this->soap_settings_table)->get();
    }

    public function getSoapSettingByCode($code)
    {
        return DB::table($this->soap_settings_table)->where("code", $code)->first();
    }
}