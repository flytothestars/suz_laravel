<?php

namespace App\Http\Controllers;

use App\Http\Traits\CatalogsTrait;
use App\Models\Location;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockController extends Controller
{
    use CatalogsTrait;

    public function index()
    {
        $stocks = Stock::paginate(10);
        return view('stocks.index', compact(['stocks']));
    }

    public function create()
    {
        $departments = $this->getDepartments();
        return view('stocks.create', compact(['departments']));
    }

    /**
     * @throws Throwable
     */
    public function getStockholdersByLocation(Request $request): JsonResponse
    {
        $location_id = $request->location_id;
        $location = Location::find($location_id);
        $users = $location->users;
        $json = [
            'success' => true,
            'html' => view('options.stockholders', compact('users'))->render()
        ];
        return response()->json($json);
    }

    public function store(Request $request)
    {
        $stock = new Stock;
        $stock->name = $request->name;
        $stock->department_id = (int)$request->department_id;
        $stock->location_id = (int)$request->location_id;
        $stock->return = $request->return == 'on'; //TODO Хардкод
        $users = array_map('intval', $request->users);
        $stock->users()->attach($users);
        $stock->parent_id = isset($request->parent_id) && $request->parent_id != '' ? $request->parent_id : null;
        if (!$stock->save()) {
            $message = [
                'success' => false,
                'message' => 'Возникла какая-то ошибка, повторите позднее.',
            ];
        } else {
            $message = [
                'success' => true,
                'message' => 'Склад создан успешно.'
            ];
        }
        return redirect('/stocks')->with($message);
    }

    public function edit(Request $request)
    {
        $stock = Stock::find($request->id);
        $departments = $this->getDepartments();
        $locations = $this->getLocations($stock->department_id);
        $parent_stocks = DB::table('stocks')->where([
            ['id', '<>', $stock->id],
            ['department_id', $stock->department_id]
        ])->whereNull('parent_id')->get(['id', 'name']);

        $location = Location::find($stock->location_id);
        $users = $location->users;
        return view('stocks.edit', compact(['stock', 'departments', 'locations', 'users', 'parent_stocks']));
    }

    public function update(Request $request)
    {
        $stock = Stock::find($request->id);
        $stock->name = $request->name;
        $stock->department_id = $request->department_id;
        $stock->location_id = $request->location_id;
        $stock->return = $request->return == 'on'; //TODO Хардкод
        $stock->users()->sync($request->users);
        if (!$stock->save()) {
            $message = [
                'success' => false,
                'message' => 'Возникла какая-то ошибка, повторите позднее.',
            ];
        } else {
            $message = [
                'success' => true,
                'message' => 'Изменения сохранены успешно.'
            ];
        }
        return redirect('/stocks')->with($message);
    }

    public function delete(Request $request): JsonResponse
    {
        $stock = Stock::find($request->id);
        if ($stock->users->count() == 0) {
            $stock->users()->detach();
            if ($stock->hasEquipments()) {
                $json = [
                    'success' => false,
                    'message' => 'К этому складу привязано оборудование. Вы не можете его удалить.'
                ];
            } else {
                $stock->delete();
                $json = [
                    'success' => true,
                    'message' => 'Склад успешно удален.'
                ];
            }
        } else {
            $attached_word = $stock->users->count() == 1 ? 'привязан' : 'привязано';
            $json = [
                'success' => false,
                'message' => 'К этому складу ' . $attached_word . ' ' . $stock->users->count() . ' человек. Вы не можете его удалить.'
            ];
        }
        return response()->json($json);
    }
}
