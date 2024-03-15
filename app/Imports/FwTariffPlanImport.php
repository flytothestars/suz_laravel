<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class FwTariffPlanImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $arr = array();
        foreach($collection as $key => $row)
        {
            if($key == 0) continue;
            $arr[] = array(
                "id_tariff_plan" => (int)strtolower($row[0]),
                "v_name" => $row[1],
                "id_product" => $row[2] ?? 0
            );
        }
        try
        {
            DB::table("fw_tariff_plan")->insert($arr);
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            echo $e->getMessage();
        }
    }
}
