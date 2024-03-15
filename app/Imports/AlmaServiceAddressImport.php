<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class AlmaServiceAddressImport implements ToCollection
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
                "id_service_ad" => (int)strtolower($row[0]),
                "id_sector" => (int)$row[1],
                "id_technology" => (int)$row[2],
                "id_house" => (int)$row[3],
                "id_service" => (int)$row[4]
            );
        }
        try
        {
            foreach(array_chunk($arr, 1000) as $t)
            {
                DB::table("alma_service_address")->insert($t);
            }
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            echo "Some error occured. Read more in log files.";
            \Log::error($e->getMessage());
            exit;
        }
    }
}
