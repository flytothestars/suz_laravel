<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use App\Models\SoapSettings;
use App\Models\Stock;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AdminController extends Controller
{
    private $logPath = __DIR__ . "/../../../storage/logs/";
    private $service;

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|Application|View
     */
    public function index()
    {
        $settings = SoapSettings::all();
        return view('admin.index', compact(['settings']));
    }

    public function equipment()
    {
        $kits = Kit::paginate(15);
        $owners = User::role('техник')->get();
        $stocks = Stock::all();
        return view('admin.equipment', compact(['kits', 'owners', 'stocks']));
    }

    public function searchEquipment(Request $request): JsonResponse
    {
        $json = [
            'success' => false,
            'html' => ''
        ];

        $kits = Kit::where('v_serial', $request->get('query'))->get();

        if ($kits) {
            $returnHTML = view('admin.search_equipment', compact(['kits']))->render();
            $json = [
                'success' => true,
                'html' => $returnHTML
            ];
        }

        return response()->json($json);
    }

    public function deleteEquipment(Request $request): JsonResponse
    {
        $kit = Kit::find($request->get('id'));
        if ($kit->equipments()->delete()) {
            if ($kit->delete()) {
                return response()->json([
                    'success' => true,
                    'html' => 'Комплект удален!'
                ]);
            }
            return response()->json([
                'success' => false,
                'html' => 'Комплект не удален!'
            ]);
        }

        return response()->json([
            'success' => false,
            'html' => 'Комплектующие не удалены!'
        ]);
    }

    public function changeEquipmentOwner(Request $request): JsonResponse
    {
        $kitId = $request->get('id');
        $ownerId = $request->get('owner_id');

        $result = $this->service->changeEquipmentOwner($kitId, $ownerId);

        return response()->json($result);
    }

    public function changeEquipmentStock(Request $request): JsonResponse
    {
        $kitId = $request->get('id');
        $fromUserId = Auth::user()->id;
        $fromStockId = $kit->stock_id ?? null;
        $stockId = $request->get('stock_id');

        $result = $this->service->changeEquipmentStock($kitId, $fromUserId, $fromStockId, $stockId);

        return response()->json($result);
    }


    public function changeSoapSetting(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $enabled = $request->input('enabled');

        if (!isset($id) || !isset($enabled)) {
            return response()->json(['message' => 'Нет параметров'], 400);
        }

        try {
            SoapSettings::where('id', $id)->update(['enabled' => $enabled]);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }

        return response()->json(['message' => 'Успешно обновлено!'], 200);
    }

    //TODO Невнятно написанный метод, пока отрефакторил так, чтобы ошибок не выводил, надо узнать логику..
    public function soapHistory(Request $request)
    {
        $query = $request->q;
        $closeflow = ''; //Скорее всего, здесь нужно взять closeflow xml из логов или что-то в этом роде,
        file_exists($this->logPath);
        return view('admin.soap_history', ['closeflow' => $closeflow]);
    }

    public function api(Request $request): JsonResponse
    {
        $contract = $request->contract;

        if (!isset($contract)) {
            return Response::json(['success' => false, 'message' => 'Нет параметра']);
        } else {
            $response = $this->service->getOrdersByContract($contract);

            if ($response->count() > 0) {
                return Response::json(['success' => true, 'message' => $response]);
            } else {
                return Response::json(['success' => false, 'message' => 'Заказы не найдены']);
            }
        }
    }
}
