<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\Equipment\ReturnRequest as EquipReturnRequest;
use App\Http\Requests\Inventory\Kit\ReturnRequest;
use App\Http\Requests\Inventory\Material\ReturnRequest as MaterialReturnRequest;
use App\Http\Requests\Inventory\Stock\StockAcceptRejectRequest;
use App\Http\Traits\CatalogsTrait;
use App\Services\InventoryService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class InventoryController extends Controller
{
    use CatalogsTrait;

    private InventoryService $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Factory|Application|View
     */
    public function index(Request $request)
    {

        $compact = $this->service->index($request);
        return view('inventory.index', $compact);
    }

    /**
     * Gives the equipment to installer
     * @param Request $request
     * @return RedirectResponse
     */
    public function issue(Request $request): RedirectResponse
    {
        $message = $this->service->issue($request);
        return redirect()->back()->with($message);
    }

    public function issueMaterials(Request $request): RedirectResponse
    {

        $message = $this->service->issueMaterial($request);
        return redirect()->back()->with($message);
    }

    public function moveMaterials(Request $request): RedirectResponse
    {
        $message = $this->service->moveMaterials($request);
        return redirect()->back()->with($message);
    }

    public function moveKit(Request $request): RedirectResponse
    {

        $message = $this->service->moveKit($request);
        return redirect()->back()->with($message);
    }

    public function moveEquipment(Request $request): RedirectResponse
    {
        $message = $this->service->moveEquipment($request);
        return redirect()->back()->with($message);
    }

    public function myInventory()
    {
        $data = $this->service->myInventory();
        return view('inventory.my_inventory', $data);
    }

    public function returnKit(ReturnRequest $request): RedirectResponse
    {
        $message = $this->service->returnKit($request);
        return redirect()->back()->with($message);
    }

    public function kitRequestGet(StockAcceptRejectRequest $request): JsonResponse
    {
        $json = $this->service->kitRequestGet($request);
        return response()->json($json);
    }

    public function kitRequestDelete(StockAcceptRejectRequest $request): JsonResponse
    {
        $json = $this->service->kitRequestDelete($request);
        return response()->json($json);
    }

    public function returnEquipment(EquipReturnRequest $request): RedirectResponse
    {
        $message = $this->service->returnEquipment($request);
        return redirect()->back()->with($message);
    }

    public function returnMaterial(MaterialReturnRequest $request): RedirectResponse
    {
        $message = $this->service->returnMaterial($request);
        return redirect()->back()->with($message);
    }

    public function materialsRequestGet(StockAcceptRejectRequest $request): JsonResponse
    {
        $json = $this->service->materialsRequestGet($request);
        return response()->json($json);
    }

    public function materialsRequestDelete(StockAcceptRejectRequest $request): JsonResponse
    {
        $json = $this->service->materialsRequestDelete($request);
        return response()->json($json);
    }

    /**
     * @throws Throwable
     */
    public function getMassEquipment(Request $request): JsonResponse
    {
        $json = $this->service->getMassEquipment($request);
        return response()->json($json);
    }

    /**
     * Индексная страница истории перемещений инвентаря по филиалу
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function movementStory(Request $request)
    {
        $result = $this->service->movementStory($request);

        if ($result['success']) {
            return view('inventory.movement_story', $result['data']);
        }
        return redirect()->back()->with($result['message']);
    }

    public function rollbackKit(Request $request): JsonResponse
    {
        $json = $this->service->rollbackKit($request);
        return response()->json($json);
    }
}
