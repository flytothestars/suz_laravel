<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class AlmaEquipmentTypeImport implements ToCollection
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
                "id_equipment_type" => (int)strtolower($row[0]),
                "v_name" => $row[1],
                "v_mnemonic" => $row[2]
            );
        }
        try
        {
            DB::table("alma_equipment_type")->insert($arr);
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            echo $e->getMessage()."\n";
        }
    }
}
