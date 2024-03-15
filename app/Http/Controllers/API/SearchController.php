<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\CatalogsTrait;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use CatalogsTrait;

    private SearchService $service;

    public function __construct(SearchService $service)
    {
        $this->service = $service;
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->service->searchRequest($request);
        return response()->json(['data' => $data]);
    }
}
