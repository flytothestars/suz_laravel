<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class FwAddressLigamentImport implements ToCollection
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
                "id_house" => (int)strtolower($row[0]),
                "id_region" => $row[1],
                "id_district" => $row[2],
                "id_town" => $row[3],
                "id_street" => $row[4],
                "house_nm" => $row[5]
            );
        }
        try
        {
            foreach(array_chunk($arr, 1000) as $t)
            {
                DB::table("fw_address_ligament")->insert($t);
            }
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            echo $e->getMessage();
        }
    }
}
