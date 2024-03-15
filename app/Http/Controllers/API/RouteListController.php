<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteList\StoreRequest;
use App\Http\Traits\CatalogsTrait;
use App\Services\RouteListService;
use App\Services\Mobile\RouteListService as apiRouteListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RouteListController extends Controller
{
    use CatalogsTrait;

    private RouteListService $service;
    private apiRouteListService $apiService;

    public function __construct(RouteListService $service, apiRouteListService $apiService)
    {
        $this->service = $service;
        $this->apiService = $apiService;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->index($request);
        return response()->json(['data' => $data]);
    }

    public function indexInstaller(Request $request): JsonResponse
    {
        $requests = $this->apiService->indexInstaller($request);
        return response()->json($requests);
    }

    /**
     * @throws ValidationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $this->service->store($request);
        return response()->json(['message' => 'Маршрут добавлен']);
    }
}
