<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class FwStreetImport implements ToCollection
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
                "id_street" => (int)strtolower($row[0]),
                "v_name" => $row[1],
                "id_parent" => $row[2]
            );
        }
        try
        {
            foreach(array_chunk($arr, 1000) as $t)
            {
                DB::table("fw_street")->insert($t);
            }
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            Log::error($e->getMessage());
            echo "Error occured. See logs.\n";
        }
    }
}
