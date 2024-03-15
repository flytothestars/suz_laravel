<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuzRequest\GetKitRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\Equipment;
use App\Models\Kit;
use App\Models\User;
use App\Services\KitEquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class KitController extends Controller
{
    use CatalogsTrait;


    private KitEquipmentService $service;

    public function __construct(KitEquipmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Returns rendered equipment list for modal
     * @param GetKitRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getInstallerKits(GetKitRequest $request): JsonResponse
    {
        $json = $this->service->getInstallerKits($request);

        if ($json['success']) {
            $json['html'] = view('requests.my_kits_equipment_modal', $json['compact'])->render();
        }

        return response()->json($json);
    }

    public function getKitsEquipment(Request $request): JsonResponse
    {
        $kit = Kit::find($request->kit_id);
        $equipments = $kit->equipments;
        $direction = $request->direction ?? 'Выдать абоненту';
        $installerId = $request->installerId ?? Auth::user()->id;
        $installer_name = User::find($installerId)->name;
        $v_kits_transfer_arr = [
            'S' => 'Продажа',
            'R' => 'Аренда',
            'RS' => 'Ответственное хранение'
        ];
        $v_kits_transfer = collect();
        $v_kits_transfer->name = $request->v_kits_transfer;
        $v_kits_transfer->code = array_search($request->v_kits_transfer, $v_kits_transfer_arr);
        $json = [
            'success' => true,
            'html' => view('requests.kits_equipment', compact(['kit', 'equipments', 'direction', 'installer_name', 'v_kits_transfer']))->render()
        ];
        return response()->json($json);
    }

    // used in my_inventory page
    public function getKitsReturnModal(Request $request): JsonResponse
    {
        $kit = Kit::find($request->kitId);
        $equipments = $kit->equipments;
        $json = [
            'success' => true,
            'html' => view('modals.return_kit', compact(['kit', 'equipments']))->render()
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

    public function getClientEquipments($contract)
    {
        return Equipment::where('owner_id', $contract)->get();
    }

    public function show(Request $request)
    {
        $kit = Kit::find($request->id);
        $compact = $this->service->show($kit, 'kit');
        return view('kit', $compact);
    }
}
