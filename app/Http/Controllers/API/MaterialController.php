<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuzRequest\GetMaterialRequest;
use App\Services\KitEquipmentService;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    private KitEquipmentService $service;

    public function __construct(KitEquipmentService $service)
    {
        $this->service = $service;
    }

    public function getInstallerMaterials(GetMaterialRequest $request): JsonResponse
    {
        $json = $this->service->getInstallerMaterials($request);
        return response()->json($json);
    }
}
