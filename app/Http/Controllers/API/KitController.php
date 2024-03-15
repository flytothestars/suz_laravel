<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuzRequest\GetKitRequest;
use App\Services\KitEquipmentService;
use Illuminate\Http\JsonResponse;

class KitController extends Controller
{
    private KitEquipmentService $service;

    public function __construct(KitEquipmentService $service)
    {
        $this->service = $service;
    }

    public function getInstallerKits(GetKitRequest $request): JsonResponse
    {
        $result = $this->service->getInstallerKits($request);
        return response()->json($result['compact']);
    }
}
