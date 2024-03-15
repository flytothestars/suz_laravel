<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Kit;
use App\Models\SuzRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\KitEquipmentService;
use Throwable;

class EquipmentController extends Controller
{

    private KitEquipmentService $service;

    public function __construct(KitEquipmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Returns rendered equipment list for modal
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getEquipmentList(Request $request): JsonResponse
    {
        $kit = Kit::find($request->kit_id);
        $equipments = $kit->equipments;
        $json = [
            'success' => true,
            'html' => view('inventory.inventory_equipment', compact('equipments'))->render()
        ];
        return response()->json($json);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getKit(Request $request): JsonResponse
    {
        $equipmentId = $request->equipment_id;
        $equipment = Equipment::find($equipmentId);
        $kit = $equipment->kit;
        $json = [
            'success' => true,
            'html' => view('inventory.kit_modal', compact('kit'))->render()
        ];
        return response()->json($json);
    }

    public function getSimilarEquipments(Request $request): JsonResponse
    {
        $equipment = Equipment::find($request->equipment_id);
        $similarEquipments = $equipment->installerHave(Auth::user()->id);
        $json = [
            'success' => true,
            'html' => view('inventory.similar_equipments', compact('similarEquipments'))->render()
        ];
        return response()->json($json);
    }

    public function getEquipmentsReturnModal(Request $request): JsonResponse
    {
        $equipment = Equipment::find($request->equipment_id);
        $similarEquipments = $equipment->installerHave(Auth::user()->id);
        $json = [
            'success' => true,
            'html' => view('modals.return_equipment', compact('equipment', 'similarEquipments'))->render()
        ];
        return response()->json($json);
    }

    public function getInstallerEquipment(Request $request): JsonResponse
    {
        $suzRequest = SuzRequest::find($request->request_id);
        $installer_id = $request->installer_id ?? Auth::user()->id;
        $direction = $request->direction ?? 'give';
        if ($direction == 'give') //TODO Хардкод
        {
            $me = User::find($installer_id);
            $equipments = $me->equipments->groupBy('id_equipment_model');
        } else {
            $equipments = $this->getClientEquipments($suzRequest->v_contract)->groupBy('id_equipment_model');
        }
        $selectedInstaller = $installer_id;
        $installers = $suzRequest->getRequestInstallers();
        $json = [
            'success' => true,
            'html' => view('requests.use_equipment_modal', compact(['equipments', 'installers', 'selectedInstaller', 'direction']))->render()
        ];
        return response()->json($json);
    }

    public function getClientEquipments($contract)
    {
        return Equipment::where('owner_id', $contract)->get();
    }

    public function getEquipmentInstances(Request $request): JsonResponse
    {
        $equipment = Equipment::find($request->equipment_id);
        $instances = $equipment->installerHave($equipment->owner_id);
        $json = [
            'success' => true,
            'html' => view('options.equipment_instances', compact('instances'))->render()
        ];
        return response()->json($json);
    }

    public function show(Request $request)
    {
        $equipment = Equipment::find($request->id);
        $compact = $this->service->show($equipment, 'equipment');
        return view('equipment', $compact);
    }
}
