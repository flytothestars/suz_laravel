<?php

namespace App\Services;

use App\Http\Requests\SuzRequest\GetKitRequest;
use App\Http\Requests\SuzRequest\GetMaterialRequest;
use App\Models\Material;
use App\Models\Stock;
use App\Models\SuzRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KitEquipmentService
{
    public function show($model, $type): array
    {
        $owner = getModelOwner($model);
        $model->owner = $owner ?? '-';
        $model->stock = Stock::find($model->stock_id);
        $story = $model->getStory();

        if ($type === 'equipment') {
            $equipment = $model;
            return compact(['equipment', 'story']);
        }

        $kit = $model;
        return compact(['kit', 'story']);

    }

    public function getInstallerKits(GetKitRequest $request): array
    {
        $validation = $request->validated();
        
        $suzRequest = SuzRequest::find($validation['request_id']);
        $requestInstallers = $suzRequest->getRequestInstallers();

        $installer_id = $validation['installer_id'] ?? $requestInstallers[0]->id;
        $installer = User::find($installer_id);
        $direction = $validation['direction'] ?? null;
        $service_key = $validation['service_key'] ?? null;

        if ($direction == 'Выдать абоненту' || !$direction) //TODO Узнать что это за херня, я пока включил это в валидацию, но надо разобраться.
        {
            $direction = 'give';
        }

        $kits = null;
        $equipments = null;

        if ($direction == 'give') //TODO Хардкод
        {
            /*
            Нагрузка базу два запрос один в if проверку неправильно 
            */
            // if ($installer->kits) {
                $kits = $installer->kits()->where('v_department', $suzRequest->v_department)->get();
            // }
        } elseif ($direction == 'take' && $suzRequest->getCloseMethod() != 'closeFlowIntake') //TODO Хардкод
        {
            $kits = $suzRequest->getKits($service_key);
        } else {
            $equipments = [];
            $service_info = json_decode($suzRequest->service_info);

            if ($service_info) {
                foreach ($service_info as $si) {
                    if (!empty($si->equipment_list)) {
                        $equipment_list = $si->equipment_list;
                        foreach ($equipment_list as $eq) {
                            $equipment = collect();
                            $model = DB::table('alma_equipment_model')->where('id_equipment_model', $eq->id_equipment_model)->first();
                            $equipment->v_name = $model->v_name;
                            $equipment->v_vendor = $model->v_vendor;
                            $equipment->v_equipment_number = $eq->v_equipment_number;
                            $equipments[] = $equipment;
                        }
                    }
                }
            }
        }

        $show_b_unbind_cntr = false;
        $show_b_unbind_cntr_methods = [
            'CloseFlowBlock',
            'CloseFlowBlockDebt',
            'CloseFlowChangeAddress',
            'CloseFlowChangeProduct',
            'CloseFlowChangeTech',
            'CloseFlowRepair',
            'CloseFlowDissolution',
            'CloseFlowUnblock',
        ];

        if (in_array($suzRequest->getCloseMethod(), $show_b_unbind_cntr_methods)) {
            $show_b_unbind_cntr = true;
        }

        $selectedInstaller = $installer_id;
        $installers = $requestInstallers;
        
        return [
            'success' => true,
            'compact' => compact(['kits', 'equipments', 'installers', 'selectedInstaller', 'direction', 'show_b_unbind_cntr', 'service_key'])
        ];
    }

    public function getInstallerMaterials(GetMaterialRequest $request): array
    {
        $validation = $request->validated();
        try {
            $suzRequest = SuzRequest::find($validation['request_id']);
            $requestInstallers = $suzRequest->getRequestInstallers();
            $installer_id = $validation['installer_id'] ?? $requestInstallers[0]->id;
            $installer = User::find($installer_id);
            $materials = null;
            $direction_material = $validation['direction_material'] ?? null;

            if ($direction_material == 'Выдать абоненту' || !$direction_material) { //TODO тоже что я писал в методе по оборудованиям.
                $direction_material = 'give';
            }

            if ($direction_material == 'give') { //TODO Хардкод
                if ($installer->getMaterials()) {
                    $materials = $installer->getMaterials();
                }
            } 
            else if ($direction_material == 'take')
            {
               $materials = DB::table('suz_requests')
                    ->join('client_material', 'client_material.contract', '=', 'suz_requests.v_contract')
                    ->join('materials', 'materials.id', '=', 'client_material.material_id')
                    ->join('material_types', 'material_types.id', '=', 'materials.type_id')
                    ->select(
                        'materials.id as id',
                        'materials.id as material_id',
                        'client_material.qty as qty',
                        'materials.name as name',
                        'material_types.name as type',    
                    )
                    ->where('suz_requests.id', $validation['request_id'])
                    ->get();
            }
            else {
                $materials = Material::all();
            }

            $selectedInstallerMaterial = $installer_id;
            $installers_material = $suzRequest->getRequestInstallers();
            $json = [
                'success' => true,
                'compact' => compact(['materials', 'installers_material', 'selectedInstallerMaterial', 'direction_material'])
            ];
        } catch (\Exception $e) {
            $json = [
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'data' => $request->all()
            ];
        }

        return $json;
    }
}
