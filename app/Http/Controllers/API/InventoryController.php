<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\Kit\ReturnRequest;
use App\Http\Requests\Inventory\Kit\SearchRequest;
use App\Http\Requests\Inventory\Material\ReturnRequest as MaterialReturnRequest;
use App\Http\Requests\Inventory\Stock\StockAcceptRejectRequest;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{

    private InventoryService $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    public function myInventory($type = 'all'): JsonResponse
    {
        $data = $this->service->myInventory($type);
        return response()->json($data);
    }

    public function returnMaterial(MaterialReturnRequest $request): JsonResponse
    {
        $data = $this->service->returnMaterial($request);
        return response()->json($data);
    }

    public function returnKit(ReturnRequest $request): JsonResponse
    {
        $data = $this->service->returnKit($request);
        return response()->json($data);
    }

    public function searchKit(SearchRequest $request): JsonResponse
    {
        $data = $this->service->searchKit($request);
        return response()->json(['kits' => $data]);
    }

    public function kitRequestGet(StockAcceptRejectRequest $request): JsonResponse
    {
        $data = $this->service->kitRequestGet($request);
        return response()->json($data);
    }

    public function kitRequestDelete(StockAcceptRejectRequest $request): JsonResponse
    {
        $data = $this->service->kitRequestDelete($request);
        return response()->json($data);
    }

    public function materialRequestGet(StockAcceptRejectRequest $request): JsonResponse
    {
        $data = $this->service->materialsRequestGet($request);
        return response()->json($data);
    }

    public function materialRequestDelete(StockAcceptRejectRequest $request): JsonResponse
    {
        $data = $this->service->materialsRequestDelete($request);
        return response()->json($data);
    }
}
