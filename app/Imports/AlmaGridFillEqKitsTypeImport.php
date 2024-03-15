<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class AlmaGridFillEqKitsTypeImport implements ToCollection
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
                "id_grid_fill_eq_kits_type" => strtolower($row[0]),
                "id_equip_kits_type" => $row[1],
                "id_equipment_type" => $row[2],
                "id_equipment_model" => $row[3]
            );
        }
        try
        {
            DB::table("alma_grid_fill_eq_kits_type")->insert($arr);
        }
        catch(\Illuminate\Database\QueryException $e)
        {
            echo $e->getMessage();
        }
    }
}
