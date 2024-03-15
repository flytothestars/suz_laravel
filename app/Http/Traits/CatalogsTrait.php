<?php

namespace App\Http\Traits;

use App\Models\AjaxImage;
use App\Models\Material;
use App\Models\SuzRequestStory;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;


trait CatalogsTrait
{
    /**
     * Gets collection of cancel reasons
     * @return \Illuminate\Support\Collection
     */
    public function getReasons()
    {
        return DB::table("alma_reason_undo_flow")->get();
    }

    /**
     * Gets cancel reason by id
     * @param integer $id
     * @return Builder
    */
    public function getReasonById($id)
    {
        return DB::table("alma_reason_undo_flow")->where("id_reason", $id)->first();
    }

    /**
     * Возвращает название типа по его id
     * @param integer - $id_kind_works
     * @return string $v_name
     */
    public function getKindWorksById($id_kind_works)
    {
        $row = DB::table("alma_kind_works")->select("v_name")->where("id_kind_work_inst", "=", (int)$id_kind_works)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает все типы работ
     * @return \Illuminate\Support\Collection
     */
    public function getKindWorks()
    {
        $rows = DB::table("alma_kind_works")->whereNotIn('id_kind_work_inst', [2010, 2006, 2007, 2008, 2009])->distinct('id_kind_work_inst')->orderBy('v_name', 'ASC')->get();
        return $rows;
    }

    /**
     * Возвращает название подтипа по id типа
     * @param array|string - id_type_works
     * @return string
     */
    public function getTypeWorksById($id_type_works)
    {
        if(!is_array($id_type_works))
        {
            $id_type_works = array($id_type_works);
        }
        $rows = DB::table("alma_type_works")->select("v_name")->whereIn("id_type_work", $id_type_works)->get();
        $types = array();
        foreach($rows as $row)
        {
            $types[] = $row->v_name;
        }
        return count($types) > 0 ? implode("/", $types) : '-';
    }

    public function getTypeWorksByKindWorkId($id_kind_work_inst)
    {
        $rows = DB::table('alma_type_works')->where('id_kind_work_inst', $id_kind_work_inst)->get();
        return $rows;
    }

    /**
     * Возвращает название типа заказа по id
     * @param integer id_ci_flow
     * @return string
     */
    public function getCiFlow($id_ci_flow)
    {
        $row = DB::table("ci_flow")->select("v_name")->where("id_ci_flow", (int)$id_ci_flow)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает типы заказов
     * @return array|\Illuminate\Support\Collection
     */
    public function getCiFlows()
    {
        $rows = DB::table("ci_flow")->get();
        return $rows ?? array();
    }

    /**
     * Возвращает название департамента по коду или по id
     * @param string|integer v_ext_ident
     * @return string
     */
    public function getDepartment($v_ext_ident)
    {
        $row = DB::table("fw_departments")->select("v_name as name", "id_department as id", "v_ext_ident");
        if(!is_numeric($v_ext_ident))
        {
            $row = $row->where("v_ext_ident", $v_ext_ident)->first();
        }
        else
        {
            $row = $row->where("id_department", $v_ext_ident)->first();
        }
        return $row ?? null;
    }

    /**
     * Возвращает код департамента по id
     * @param integer id
     * @return string
     */
    public function getDepartmentCodeById($id_department)
    {
        $row = DB::table("fw_departments")->select("v_ext_ident")->
                   where("id_department", $id_department)->first();
        return $row->v_ext_ident ?? null;
    }

    /**
     * Возвращает список департаментов
     * @return \Illuminate\Support\Collection
     */
    public function getDepartments()
    {
        $rows = DB::table("fw_departments")->select("id", "id_department", "v_name", "v_ext_ident")->orderBy('v_name')->get();
        return $rows ?? collect();
    }

    /**
     * Возвращает название участка по id
     * @param integer id_location
     * @return string
     */

    public function getDepartmentName($v_ext_ident)
    {
        $row = DB::table("fw_departments")->select("v_name")->where("v_ext_ident", $v_ext_ident)->first();
        return $row->v_name ?? 'Undefined';
    }

    public function getLocation($id_location)
    {
        $row = DB::table("alma_location")->select("v_name")->where("id_location", (int)$id_location)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает список участков
     * @param integer|boolean
     * @return \Illuminate\Support\Collection|null
     */
    public function getLocations($department_id = false)
    {
        $rows = DB::table("alma_location");
        if($department_id)
        {
            $rows = $rows->where('id_department', $department_id);
        }
        return $rows->get() ?? null;
    }

    /**
     * Возвращает название сектора по id
     * @param integer id_sector
     * @return string
     */
    public function getSector($id_sector)
    {
        $row = DB::table("alma_sector")->select("v_name")->where("id_sector", (int)$id_sector)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает название области по id
     * @param integer id_region
     * @return string
     */
    public function getRegion($id_region)
    {
        $row = DB::table("fw_region")->select("v_name")->where("id_region", (int)$id_region)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает название района по id
     * @param integer id_district
     * @return string
     */
    public function getDistrict($id_district)
    {
        $row = DB::table("fw_district")->select("v_name")->where("id_district", (int)$id_district)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает название района по id
     * @param integer id_town
     * @return string
     */
    public function getTown($id_town)
    {
        $row = DB::table("fw_town")->select("v_name")->where("id_town", (int)$id_town)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает название улицы по id
     * @param integer id_street
     * @return string
     */
    public function getStreet($id_street)
    {
        $row = DB::table("fw_street")->select("v_name")->where("id_street", (int)$id_street)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает номер дома по id
     * @param integer id_house
     * @return string
     */
    public function getHouse($id_house)
    {
        $row = DB::table("fw_address_ligament")->select("house_nm")->where("id_house", (int)$id_house)->first();
        return $row->house_nm ?? 'Undefined';
    }

    /**
     * Возвращает продукт по id
     * @param integer id_product
     * @return string
     */
    public function getProduct($id_product)
    {
        $row = DB::table("fw_product")->select("v_name")->where("id_product", (int)$id_product)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Возвращает продукт по id
     * @param integer id_tariff_plan
     * @return string
     */
    public function getTariff($id_tariff_plan)
    {
        $row = DB::table("fw_tariff_plan")->select("v_name")->where("id_tariff_plan", (int)$id_tariff_plan)->first();
        return $row->v_name ?? 'Undefined';
    }
    /**
     * Возвращает тип документа по id
     * @param integer id_document_type
     * @return string
     */
    public function getDocumentType($id_document_type)
    {
        $row = DB::table("fw_document_types")->select("v_name")->where("id_document_type", (int)$id_document_type)->first();
        return $row->v_name ?? 'Undefined';
    }

    /**
     * Gets status by id
     * @param integer $status_id
     * @return \Illuminate\Database\Query\Builder $row
     */
    public function getStatusById($status_id)
    {
        $row = DB::table("statuses")->where("id", $status_id)->first();
        return $row;
    }

    /**
     * Gets status by name
     * @param string $status_name
     * @return \Illuminate\Database\Query\Builder $row
    */
    public function getStatusByName($status_name)
    {
        $row = DB::table("statuses")->where("name", $status_name)->first();
        return $row;
    }

    /**
     * Gets array of request statuses
     * @return \Illuminate\Support\Collection $rows
     */
    public function getStatuses()
    {
        $rows = DB::table("statuses")->get();
        return $rows;
    }

    /**
     * Gets service status by code
     * @param $status_code
     * @return mixed|string
     */
    public function getServiceStatus($status_code)
    {
        $arr = array(
            'A' => 'Активный',
            'B' => 'Заблокированный',
            'W' => 'Ожидает подключения',
            'D' => 'Будет удалена',
        );
        return $arr[$status_code] ?? 'Undefined';
    }

    /**
     * Returns mnemonic of kit by equipments model
     * @param $id_equipment_model
     * @return string|null $v_type
    */
    public function getKitVTypeByModel($id_equipment_model)
    {
        $row = DB::table('alma_grid_fill_eq_kits_type')->select('id_equip_kits_type')->where('id_equipment_model', $id_equipment_model)->first();
        $v_type = null;
        if($row)
        {
            $id_equip_kits_type = $row->id_equip_kits_type;
            $row = DB::table('alma_equipment_kits_type')->select('v_mnemonic')->where('id_equip_kits_type', $id_equip_kits_type)->first();
            if($row)
            {
                $v_type = $row->v_mnemonic ?? null;
            }
        }
        return $v_type;
    }

    public function getMnemonics()
    {
        $rows = DB::table("alma_equipment_kits_type")->select("v_mnemonic", "v_name")->groupBy('v_mnemonic')->get();
        return $rows;
    }

    public function getEquipmentModel($id_equipment_model)
    {
        $row = DB::table('alma_equipment_model')->where('id_equipment_model', $id_equipment_model)->first();
        return $row ?? null;
    }

    public function getService($id_service)
    {
        $row = DB::table('fw_service')->select('v_name')->where('id_service', $id_service)->first();
        return $row->v_name ?? 'undefined';
    }

    public function getTechnology($id_service_technology)
    {
        $row = DB::table("alma_technology_type")->select("v_name")->where('id_technology',  $id_service_technology)->first();
        return $row->v_name ?? 'Undefined';
    }

    public function getEquipmentTransfer($v_equipment_transfer)
    {
        $arr = array(
            'S' => 'Продажа',
            'R' => 'Аренда',
            'RS' => 'Ответственное хранение'
        );
        return $arr[$v_equipment_transfer] ?? 'Undefined';
    }

    public function getMaterialType($id)
    {
        $row = DB::table('material_types')->where('id', $id)->first();
        return $row->name ?? '';
    }

    public function getClientMaterials($contract)
    {
        $rows = DB::table('client_material')->where([
            ['contract', '=', $contract],
            ['qty', '<>', 0],
        ])->get();
        foreach($rows as &$r)
        {
            $material = Material::find($r->material_id);
            $r->name = $material->name;
        }
        return $rows;
    }

    public function getLastComment($request_id)
    {
        $comment = SuzRequestStory::where('request_id', $request_id)->orderBy('id', 'desc')->first()->comment;
        return $comment ?? '-';
    }

    public function getImages($request_id)
    {
        $rows = AjaxImage::where('request_id', $request_id)->get();
        $name_array = collect();
        foreach ($rows as $row) {
            $image = $row->image;
            $name_array->push($image);
        }
        return $name_array;
    }

    public function getRepairName($id_type)
    {
        $row = DB::table("repair_type")->select('v_name')->where('id_type', $id_type)->first();
        return $row;
    }

    public function getInstallers($request_id)
    {
        $installers_id = DB::table("request_route_list")
                ->join('installer_route_list', 'installer_route_list.routelist_id',
                    '=', 'request_route_list.routelist_id')
                ->select("installer_route_list.installer_1", "installer_route_list.installer_2")
                ->where("request_route_list.request_id", $request_id)->get()->toArray();
        if($installers_id)
        {
            $installers = null;
            $installers = collect();
            if(isset($installers_id[0]->installer_1) && $installers_id[0]->installer_1)
            {
                $installers->push(User::find($installers_id[0]->installer_1)->name);
            }
            if(isset($installers_id[0]->installer_2) && $installers_id[0]->installer_2)
            {
                $installers->push(User::find($installers_id[0]->installer_2)->name);
            }
        }
        else
        {
            $installers = null;
            $installers = collect();
            $installers->push('-');
        }
        return $installers;
    }

    public function getCompletingRequest($request_id)
    {
        $row = SuzRequestStory::where('request_id', $request_id)->orderBy('id', 'desc')->first()->dispatcher_id;
        $dispatcher = $row ? User::find($row) : null;
        return $dispatcher;
    }

    public function getRepairTypes($request_id)
    {
        $row = DB::table("request_repair_type")->select("id_type")->where('request_id', $request_id)->orderBy('id', 'desc')->get();
        $types = [];
        foreach ($row as $r)
        {
            $name = DB::table("repair_type")->select("v_name")->where('id_type', $r->id_type)->first();
            $types[] = $name->v_name;
        }
        return $types;
    }
}
