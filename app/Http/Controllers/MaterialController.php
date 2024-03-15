<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuzRequest\GetMaterialRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\Material;
use App\Services\KitEquipmentService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class MaterialController extends Controller
{
    use CatalogsTrait;

    private KitEquipmentService $service;

    public function __construct(KitEquipmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * @param GetMaterialRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getInstallerMaterials(GetMaterialRequest $request): JsonResponse
    {
        $json = $this->service->getInstallerMaterials($request);

        if($json['success']) {
            $json['html'] = view('requests.my_materials_modal', $json['compact'])->render();
        }

        return response()->json($json);
    }

    public function index(Request $request)
    {
        $types = DB::table('material_types')->get();
        $type = isset($request->type) && $request->type != 'all' ? $request->type : null;
        $materials = Material::when($type, function ($query, $type) {
            return $query->where('type_id', $type);
        })->paginate(15);
        return view('materials.index', compact(['materials', 'types']));
    }

    public function types()
    {
        $types = DB::table('material_types')->get();
        return view('materials.types', compact(['types']));
    }

    public function storeType(Request $request): RedirectResponse
    {
        if (isset($request->name)) {
            $name = $request->name;
            $row = DB::table('material_types')->where('name', $name)->first();
            if (!$row) {
                DB::table('material_types')->insert([
                    'name' => $name
                ]);
            }
        }
        return redirect()->back();
    }

    public function deleteType(Request $request)
    {
        DB::table('material_types')->delete($request->id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|Application|View
     */
    public function create()
    {
        $types = DB::table('material_types')->get();
        return view('materials.create', compact(['types']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $message = [
            'success' => false,
            'message' => 'Произошла какая-то странная ошибка, попробуйте позднее или сообщите администратору сайта.'
        ];
        if (isset($request->name) && isset($request->type)) {
            $name = $request->name;
            $type_id = $request->type;
            $material = Material::where('name', $name)->where('type_id', $type_id)->first();
            if (!$material) {
                $material = new Material;
                $material->name = $name;
                $material->type_id = $type_id;
                if ($material->save()) {
                    $message = [
                        'success' => true,
                        'message' => 'Расходник успешно добавлен!'
                    ];
                }
            } else {
                $message = [
                    'success' => false,
                    'message' => 'Такой расходник уже есть.'
                ];
            }
        }
        return redirect()->back()->with($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $message = [
            'success' => false,
            'message' => 'Возникла какая-то ошибка при удалении, попробуйте позднее'
        ];
        if (Material::destroy($request->id)) {
            $message = [
                'success' => true,
                'message' => 'Расходник успешно удален'
            ];
        }
        return response()->json($message);
    }

    public function uploadIndex(Request $request)
    {
        $departments = $this->getDepartments();
        $types = DB::table('material_types')->get();
        $type = isset($request->type) ? $request->type : null;
        $materials = null;
        $stocks = Auth::user()->stocks;
        if ($type) {
            $materials = Material::where('type_id', $type)->get();
        }
        return view('materials.upload', compact(['departments', 'types', 'materials', 'stocks']));
    }

    public function upload(Request $request): RedirectResponse
    {
        $material = (int)$request->material;
        $stock = (int)$request->stock;
        $qty = abs((int)$request->qty);
        $values = [
            'material_id' => $material,
            'stock_id' => $stock,
            'qty' => $qty
        ];
        $row = DB::table('stock_material')->where('material_id', $material)->where('stock_id', $stock)->first();
        $success = false;
        if ($row) {
            if (DB::table('stock_material')
                ->where('material_id', $material)
                ->where('stock_id', $stock)
                ->increment('qty', $qty)) {
                $success = true;
                // запишем в историю
                DB::table('materials_story')->insert([
                    'material_id' => $material,
                    'stock_id' => $stock,
                    'qty' => $qty,
                    'author_id' => Auth::user()->id,
                    'incoming' => true
                ]);
            }
        } else {
            if (DB::table('stock_material')->insert($values)) {
                $success = true;
                // запишем в историю
                DB::table('materials_story')->insert([
                    'material_id' => $material,
                    'stock_id' => $stock,
                    'qty' => $qty,
                    'author_id' => Auth::user()->id
                ]);
            }
        }
        if ($success) {
            $message = [
                'success' => true,
                'message' => 'Расходники успешно добавлены'
            ];
        } else {
            $message = [
                'success' => false,
                'message' => 'Произошла ошибка, попробуйте позднее'
            ];
        }
        return redirect()->back()->with($message);
    }

    public function getMaterialsReturnModal(Request $request): JsonResponse
    {
        $user_material = DB::table('user_material')->where('id', $request->id)->first();
        $material = Material::find($user_material->material_id);
        $material->qty = $user_material->qty;
        $json = [
            'success' => true,
            'html' => view('modals.return_material', compact(['material', 'user_material']))->render()
        ];
        return response()->json($json);
    }

    public function editLimit(Request $request): JsonResponse
    {
        $message = [
            'success' => false,
            'message' => 'Возникла какая-то ошибка при обновлении, попробуйте позднее'
        ];
        if (DB::table('materials')
            ->where('id', $request->id)
            ->update(['limit_qty' => $request->limit_qty])) {
            $message = [
                'success' => true,
                'message' => 'Лимит успешно изменен'
            ];
        }
        return response()->json($message);
    }

    public function show(Request $request)
    {
        $material = Material::find($request->id);
        $total_count = 0;
        $users = $material->getUserOwners();
        foreach ($users as $el) {
            $total_count += $el->qty;
        }
        $clients = $material->getClientOwners();
        foreach ($clients as $el) {
            $total_count += $el->qty;
        }
        $stocks = $material->getStockOwners();
        foreach ($stocks as $el) {
            $total_count += $el->qty;
        }
        return view('material', compact(['material', 'users', 'clients', 'stocks', 'total_count']));
    }
}
