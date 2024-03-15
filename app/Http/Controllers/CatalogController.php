<?php

namespace App\Http\Controllers;

use App\Http\Traits\CatalogsTrait;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use CatalogsTrait;
    public function getLocationByDepartment(Request $request): JsonResponse
    {
        $department_id = $request->department_id;
        $locations = $this->getLocations($department_id);
        return response()->json($locations);
    }

    public function getLocationsOptionsByDepartment(Request $request): JsonResponse
    {
        // 'Every time I'm leavin' on ya', 15:15 22.03.2019. I love this girl
        $departmentCodeOrId = $request->departmentCode ?? $request->department_id;
        $department = $this->getDepartment($departmentCodeOrId);
        $locations = $this->getLocations($department->id);
        $json = [
            'success' => true,
            'html' => view('options.locations', compact(['locations']))->render()
        ];
        return response()->json($json);
    }

    public function getParentStocksOptionsByDepartment(Request $request): JsonResponse
    {
        $departmentCodeOrId = $request->departmentCode ?? $request->department_id;
        $department = $this->getDepartment($departmentCodeOrId);
        $stocks = Stock::where('department_id', $department->id)->whereNull('parent_id')->get(['id', 'name']);
        $json = [
            'success' => true,
            'html' => view('options.parent_stocks', compact('stocks'))->render()
        ];
        return response()->json($json);
    }
}
