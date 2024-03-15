<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialLimit\Statistic\CreateRequest;
use App\Models\Material;
use App\Models\MaterialLimit;
use App\Models\MaterialLimitStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MaterialLimitController extends Controller
{
    public static function storeStatistic(CreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insertData = [];

        foreach ($data['materials'] as $material) {
            $id = is_array($material['qty']) ? $material['qty']['id'] : $material['id'];
            $qty = is_array($material['qty']) ? $material['qty']['qty'] : $material['qty'];

            $limit_qty = MaterialLimit::where('material_id', $id)->first()->limit_qty;

            if ($qty > $limit_qty) {
                $insertData[] = [
                    'material_id' => $id,
                    'qty' => $qty,
                    'request_id' => $data['request_id'],
                    'installer_id' => $data['installer_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        try {
            MaterialLimitStatistic::insert($insertData);
        } catch (\Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->json(['message' => 'Bad insert', 'data' => $insertData], 400);
        }

        return response()->json('Success insert into material limit statistics');
    }
}
