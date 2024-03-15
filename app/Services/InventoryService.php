<?php

namespace App\Services;

use App\Http\Requests\Inventory\Equipment\ReturnRequest as EquipReturnRequest;
use App\Http\Requests\Inventory\Kit\ReturnRequest;
use App\Http\Requests\Inventory\Kit\SearchRequest;
use App\Http\Requests\Inventory\Material\ReturnRequest as MaterialReturnRequest;
use App\Http\Requests\Inventory\Stock\StockAcceptRejectRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\Equipment;
use App\Models\EquipmentStory;
use App\Models\Kit;
use App\Models\KitRequest;
use App\Models\Location;
use App\Models\MaterialRequest;
use App\Models\MaterialStory;
use App\Models\Stock;
use App\Models\StockMaterial;
use App\Models\User;
use App\Models\UserMaterial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class InventoryService
{

    use CatalogsTrait;

    public function myInventory($type = 'all'): array
    {
        $me = Auth::user() ?? Auth::guard('api')->user();

        if ($type === 'all' || $type === 'kits' || $type === 'equipments') {
            $kits = $me->kits()->with('equipments')->orderBy('updated_at', 'desc')->paginate(20);
            if ($type === 'kits') {
                return compact('kits');
            }
        }

        if ($type === 'all' || $type === 'equipments') {
            $equipments = $me->equipments->reject(function ($equipment) use ($kits) {
                return $kits->pluck('id')->contains($equipment->kit_id);
            })->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'id_equipment_model' => $equipment->id_equipment_model,
                    'model' => [
                        'v_name' => $equipment->model->v_name ?? 'Модель неизвестна',
                        'v_vendor' => $equipment->model->v_vendor ?? 'Вендор неизвестен'
                    ],
                    'v_equipment_number' => $equipment->v_equipment_number,
                    'updated_at' => $equipment->updated_at
                ];
            })->groupBy('id_equipment_model');

            if ($type === 'equipments') {
                return compact('equipments');
            }
        }
        
        if ($type === 'all' || $type === 'materials') {
            if($type === 'materials'){
                $materials = $me->getMaterialsApi();
            }else{
                $materials = $me->getMaterials();
            }
            if ($type === 'materials') {
                return compact('materials');
            }
        }


        return compact('kits', 'equipments', 'materials');
    }

    public function index(Request $request): array
    {
        $kits = null;
        $installers = null;
        $equipments = null;
        $mnemonics = array();
        $stocks = Auth::user()->stocks()->orderBy('parent_id', 'asc')->orderBy('id', 'desc')->get();
        $materials = null;
        $materials_request_from = null;
        $materials_request_to = null;
        $kit_request_from = null;
        $kit_request_to = null;
        if (isset($request->stock)) {
            $stock_id = $request->stock;
        }
        if (isset($stock_id)) {
            $kits = Kit::where('stock_id', $stock_id);
            if ($request->mnemonic && $request->mnemonic != 'all') {
                $kits = $kits->where('v_type', $request->mnemonic);
            }
            $mnemonics = $this->getMnemonics();
            $kits = $kits->orderBy('returned', 'DESC')->paginate(20);
            $kits_ids = $kits->pluck('id')->toArray();
            $equipments = Equipment::where('stock_id', $stock_id)->whereNotIn('kit_id', $kits_ids)->orderBy('created_at', 'DESC')->paginate(20);
            $stock = Stock::find($stock_id);
            if (isset($stock->location_id)) {
                $installers = Location::find($stock->location_id)->users()->orderBy('name')->get();
                foreach ($installers as $key => $inst) {
                    if (!$inst->hasRole('техник')) {
                        unset($installers[$key]);
                    }
                }
            }
            $materials = $stock->getMaterials();
            $materials_request_from = $stock->getMaterialsRequestFrom();
            $materials_request_to = $stock->getMaterialsRequestTo();
            $kit_request_from = $stock->getKitRequestFrom();
            $kit_request_to = $stock->getKitRequestTo();
        }
        $showUploadMaterialsBtn = (isset($stock) && $stock->parent_id == null) || Auth::user()->hasRole('администратор');
        if (isset($stock) && $stock->id == 31) {
            $showUploadMaterialsBtn = true;
        }

        return compact(['kits', 'installers', 'materials', 'equipments', 'stocks', 'mnemonics', 'showUploadMaterialsBtn', 'materials_request_from', 'materials_request_to', 'kit_request_from', 'kit_request_to']);
    }


    public function issue(Request $request): array
    {
        $authorId = Auth::user()->id;
        $ownerId = $request->whom;
        $message = [];
        $kitIds = explode(",", $request->kit_id);

        $kitId = $request->kit_id;
        $kit = Kit::find($kitId);
        $from_stock = $kit->stock_id;

        foreach ($kitIds as $kitId) {
            $kit = Kit::find($kitId);
            $kit->stock_id = null;
            $kit->owner_id = $ownerId;

            // writes story about this movement
            $kit->writeStory($authorId, $ownerId, null, $from_stock);

            foreach ($kit->equipments as $eq) {
                $equip = Equipment::find($eq->id);
                $equip->stock_id = null;
                $equip->owner_id = $ownerId;
                $equip->save();
            }
            if ($kit->save()) {
                $message = [
                    'success' => true,
                    'message' => 'Комплекты выданы успешно.'
                ];
            } else {
                $message = [
                    'success' => false,
                    'message' => 'При выдаче комплекта ' . $kit->v_serial . ' произошла ошибка.'
                ];
            }
        }

        return $message;
    }

    public function issueMaterial(Request $request): array
    {
        $values = [];
        if (isset($request->whom)) {
            $whom = $request->whom;
        } else {
            return [
                'success' => false,
                'message' => 'Вы не указали кому надо выдать расходники'
            ];
        }
        $data = $this->moveMaterialsFromStock($request);
        $materials = $data['materials'];
        $qty = $data['qty'];
        $stock_id = $data['stock_id'];

        DB::beginTransaction();
        $success = false;

        foreach ($qty as $key => $qty_el) {
            $m = $materials[$key];
            $current_count = StockMaterial::where('material_id', $m)->where('stock_id', $stock_id)->first();
            if ($current_count->qty > 0) {
                $stockQuery = StockMaterial::where('material_id', $m)
                    ->where('stock_id', $stock_id)
                    ->decrement('qty', $qty_el);
            } else {
                $stockQuery = StockMaterial::where('material_id', $m)
                    ->where('stock_id', $stock_id)
                    ->update(['qty' => 0]);
            }
            if ($stockQuery) {
                $success = true;
                // запишем в историю
                MaterialStory::insert([
                    'material_id' => $m,
                    'owner_id' => $whom,
                    'author_id' => Auth::user()->id,
                    'stock_id' => $stock_id,
                    'qty' => $qty_el,
                    'from_stock' => $stock_id
                ]);
            } else {
                $success = false;
                break;
            }

            $row = UserMaterial::where('material_id', $m)
                ->where('user_id', $whom)
                ->first();

            if ($row) {
                if (!UserMaterial::where('material_id', $m)->where('user_id', $whom)->increment('qty', $qty_el)) {
                    return [
                        'success' => false,
                        'message' => 'При инкременте расходников произошла ошибка.'
                    ];
                }
            } else {
                $values[] = [
                    'material_id' => $m,
                    'user_id' => $whom,
                    'qty' => $qty_el
                ];
            }
        }

        if (count($values) > 0) {
            if (UserMaterial::insert($values)) {
                $success = true;
            } else {
                $success = false;
            }
        }
        if ($success) {
            DB::commit();
            $message = [
                'success' => true,
                'message' => 'Расходники успешно переданы'
            ];
        } else {
            DB::rollBack();
            $message = [
                'success' => false,
                'message' => 'Возникла какая-то ошибка'
            ];
        }

        return $message;
    }

    public function moveMaterials(Request $request): array
    {
        $request_values = [];
        $to_stock = $request->stock;

        $data = $this->moveMaterialsFromStock($request);
        $materials = $data['materials'];
        $qty = $data['qty'];
        $stock_id = $data['stock_id'];

        $stock_id_row = Stock::find($to_stock);
        $stockname = $stock_id_row->name;
        $from_stock_row = Stock::find($stock_id);
        $from_stockname = $from_stock_row->name;
        $author_id_row = User::find(Auth::user()->id);
        $authorname = $author_id_row->name;

        DB::beginTransaction();
        $success = true;
        foreach ($qty as $key => $qty_el) {
            $m = $materials[$key];
            $request_values[] = [
                'material_id' => $m,
                'author_id' => Auth::user()->id,
                'authorname' => $authorname,
                'stock_id' => $to_stock,
                'stockname' => $stockname,
                'qty' => $qty_el,
                'from_stock' => $stock_id,
                'from_stockname' => $from_stockname
            ];
            if (count($request_values) > 0) {
                StockMaterial::where('material_id', $m)
                    ->where('stock_id', $stock_id)
                    ->decrement('qty', $qty_el);
            } else {
                $success = false;
                break;
            }
        }

        return $this->materialInserting($request_values, $success);
    }


    public function returnMaterial(MaterialReturnRequest $request): array
    {
        $data = $request->validated();

        $request_values = [];
        $user_material_id = $data['user_material_id'];
        $user_material = UserMaterial::where('id', $user_material_id)->first();
        $user_id = Auth::user()->id;
        $stock_id = $data['stock'];

        $stock_id_row = Stock::find($stock_id);
        $stockname = $stock_id_row->name;
        $user_id_row = User::find($user_id);
        $username = $user_id_row->name;
        $qty = $data['qty'];

        if ($qty > $user_material->qty) {
            return [
                'success' => false,
                'message' => 'При передаче оборудования произошла ошибка.'
            ];
        }
        # Если он вернул всё, то удалим, иначе просто декремент
        if ($qty == $user_material->qty) {
            # Delete
            UserMaterial::where('id', $user_material_id)->delete();
        } else {
            # Decrement
            UserMaterial::where('id', $user_material_id)->decrement('qty', $qty);
        }
        $request_values[] = [
            'material_id' => $user_material->material_id,
            'user_id' => $user_id,
            'username' => $username,
            'stock_id' => $stock_id,
            'stockname' => $stockname,
            'qty' => $qty
        ];

        return $this->materialInserting($request_values);
    }


    public function moveKit(Request $request): array
    {
        $request_values = [];
        $success = false;
        $authorId = Auth::user()->id;
        $stockId = $request->stock;
        $kitIds = explode(",", $request->kit_id);

        $kitId = $request->kit_id;
        $kit = Kit::find($kitId);

        $from_stock = $kit->stock_id;

        $stock_id_row = Stock::find($stockId);
        $stockname = $stock_id_row->name;
        $from_stock_row = Stock::find($from_stock);
        $from_stockname = $from_stock_row->name;

        foreach ($kitIds as $kitId) {
            $kit = Kit::find($kitId);
            $kit->stock_id = null;
            $kit->owner_id = null;
            $v_serial = $kit->v_serial;

            foreach ($kit->equipments as &$eq) {
                $eq->stock_id = null;
                $eq->owner_id = null;
                $eq->save();
            }

            if ($kit->save()) {
                $request_values[] = [
                    'kit_id' => $kitId,
                    'v_serial' => $v_serial,
                    'author_id' => $authorId,
                    'stock_id' => $stockId,
                    'stockname' => $stockname,
                    'from_stock' => $from_stock,
                    'from_stockname' => $from_stockname
                ];
                $success = true;
            } else {
                $success = false;
            }
        }

        return $this->kitMoving($request_values, $success, $kit);
    }

    public function returnKit(ReturnRequest $request): array
    {
        $data = $request->validated();
        $stock = $data['stock'];
        $kit_id = $data['kit_id'];

        $request_values = [];
        $author_id = Auth::user()->id;
        $kit = Kit::find($kit_id);
        $v_serial = $kit->v_serial;
        $stock_id = (int)$stock;

        $stock_id_row = Stock::find($stock_id);
        $stockname = $stock_id_row->name;
        $user_id_row = User::find($author_id);
        $username = $user_id_row->name;

        $kit->stock_id = null;
        $kit->owner_id = null;
        $kit->returned = true;

        foreach ($kit->equipments as &$eq) {
            $eq->stock_id = null;
            $eq->owner_id = null;
            $eq->returned = true;
            $eq->save();
        }

        if ($kit->save()) {
            $request_values[] = [
                'kit_id' => $kit_id,
                'v_serial' => $v_serial,
                'user_id' => $author_id,
                'username' => $username,
                'stock_id' => $stock_id,
                'stockname' => $stockname
            ];
            $success = true;
        } else {
            $success = false;
        }

        return $this->kitMoving($request_values, $success, $kit);
    }

    public function searchKit(SearchRequest $request)
    {
        $validated = $request->validated();

        $kits = Kit::with(['owner', 'stock'])->where('v_serial', $validated['v_serial'])->get();

        $kits->transform(function ($kit) {
            $ownerName = $kit->owner ? $kit->owner->name : ($kit->stock ? $kit->stock->name : '?');
            $kit->owner = $ownerName;
            $kit->returned = $kit->returned ? 'Да' : 'Нет';

            return $kit->getAttributes();
        });


        return $kits;
    }


    public function moveEquipment(Request $request): array
    {
        $authorId = Auth::user()->id;
        $stockId = $request->stock;
        $equipmentId = $request->equipment_id;

        $equipment = Equipment::find($equipmentId);

        $from = $equipment->owner_id;
        $equipment->stock_id = $stockId;
        $equipment->owner_id = null;

        $equipment->writeStory($authorId, null, $from, $stockId); // $from третьим параметром

        if ($equipment->save()) {
            $message = [
                'success' => true,
                'message' => 'Оборудование перемещено успешно.'
            ];
        } else {
            $message = [
                'success' => false,
                'message' => 'При передаче оборудования произошла ошибка.'
            ];
        }

        return $message;
    }

    public function kitRequestGet(StockAcceptRejectRequest $request): array
    {
        $data = $request->validated();
        $kitIds = $data['id'];
        $json = [
            'success' => false,
            'message' => 'Что-то пошло не так.'
        ];

        DB::beginTransaction();

        try {
            foreach ($kitIds as $kitId) {
                $kit_request = KitRequest::findOrFail($kitId);

                $kit_id = $kit_request->kit_id;
                $stock_id = $kit_request->stock_id;
                $from_stock = $kit_request->from_stock;
                $user_id = $kit_request->user_id;
                $author_id = $kit_request->author_id;

                $kit = Kit::findOrFail($kit_id);
                $kit->stock_id = $stock_id;
                $kit->owner_id = null;
                $kit->writeStory($user_id ?? $author_id, null, null, $from_stock, $stock_id);

                $equipmentIds = $kit->equipments->pluck('id')->toArray();

                Equipment::whereIn('id', $equipmentIds)
                    ->update([
                        'stock_id' => $stock_id,
                        'owner_id' => null,
                    ]);

                $kit_request->delete();
                $kit->save();
            }

            DB::commit();

            $json['success'] = true;
            $json['message'] = 'Комплект(ы) принят(ы) на склад успешно.';
        } catch (\Exception $e) {
            DB::rollback();
        }

        return $json;
    }

    public function kitRequestDelete(StockAcceptRejectRequest $request): array
    {
        $data = $request->validated();

        $kitIds = $data['id'];
        $json = [
            'success' => false,
            'message' => 'Что-то пошло не так.'
        ];

        try {
            DB::beginTransaction();

            foreach ($kitIds as $kitId) {
                $kit_request = KitRequest::findOrFail($kitId);
                $from_stock = $kit_request->from_stock;
                $user_id = $kit_request->user_id;

                $kit = Kit::findOrFail($kit_request->kit_id);
                $kit->stock_id = $user_id ? null : $from_stock;
                $kit->owner_id = $user_id ?: $kit->owner_id;
                $kit->returned = $user_id ? false : $kit->returned;

                foreach ($kit->equipments as &$eq) {
                    $eq->stock_id = $user_id ? null : $from_stock;
                    $eq->owner_id = $user_id ?: $eq->owner_id;
                    $eq->returned = $user_id ? false : $eq->returned;
                    $eq->save();
                }

                if (!$kit->save()) {
                    throw new Exception('Failed to save kit.');
                }

                $kit_request->delete();
            }

            DB::commit();

            $json = [
                'success' => true,
                'message' => 'Комплект(ы) отклонен(ы) и возвращен(ы) успешно.'
            ];
        } catch (Exception $e) {
            DB::rollBack();
        }

        return $json;
    }

    public function returnEquipment(EquipReturnRequest $request): array
    {
        $data = $request->validated();

        $author_id = Auth::user()->id;
        $equipments = $data['return_equipments'];
        $stockId = $data['stock'];
        $success = true;
        $v_equipment_number_error = '';

        foreach ($equipments as $eq) {
            $equipment = Equipment::find($eq);
            $equipment->stock_id = $stockId;
            $equipment->owner_id = null;
            $equipment->returned = true;
            $equipment->writeStory($author_id, null, $stockId);

            if (!$equipment->save()) {
                $success = false;
                $v_equipment_number_error = $equipment->v_equipment_number;
                break;
            }
        }
        if ($success) {
            $message = [
                'success' => true,
                'message' => 'Оборудование возвращено успешно.'
            ];
        } else {
            $message = [
                'success' => false,
                'message' => 'При передаче оборудования ' . $v_equipment_number_error . ' что-то пошло не так.'
            ];
        }
        return $message;
    }

    public function materialsRequestGet(StockAcceptRejectRequest $request): array
    {
        $data = $request->validated();
        $success = false;
        $json = [
            'success' => false,
            'message' => 'Что-то пошло не так.'
        ];
        $materialsIds = $data['id'];

        foreach ($materialsIds as $materialsId) {
            $values = [];
            $materials_request = MaterialRequest::findOrFail($materialsId);
            $from_stock_check = $materials_request->from_stock;
            $user_id_check = $materials_request->user_id;
            if (is_null($from_stock_check) && $user_id_check != '') {
                $success = false;
                $user_id = $materials_request->user_id;
                $stock_id = $materials_request->stock_id;
                $material_id = $materials_request->material_id;

                $stockRow = StockMaterial::where('material_id', $material_id)->where('stock_id', $stock_id)->first();
                if ($stockRow) {
                    # Increment in stock
                    if (StockMaterial::where('material_id', $material_id)
                        ->where('stock_id', $stock_id)
                        ->increment('qty', $materials_request->qty)) {
                        $success = true;
                    }
                } else {
                    # Insert into stock
                    if (StockMaterial::insert([
                        'material_id' => $material_id,
                        'stock_id' => $stock_id,
                        'qty' => $materials_request->qty
                    ])) {
                        $success = true;
                    }
                }
                if ($success) {
                    $json = [
                        'success' => true,
                        'message' => 'Расходники приняты успешно.'
                    ];
                    // удалим из промежуточной таблицы
                    $materials_request->delete();
                    // запишем в историю
                    MaterialStory::insert([
                        'material_id' => $material_id,
                        'author_id' => $user_id,
                        'stock_id' => $stock_id,
                        'qty' => $materials_request->qty,
                        'returned' => true,
                        'from' => $user_id
                    ]);
                } else {
                    $json = [
                        'success' => false,
                        'message' => 'При передаче расходников что-то пошло не так.'
                    ];
                }
            } elseif (is_null($user_id_check) && $from_stock_check != '') {
                $author_id = $materials_request->author_id;
                $stock_id = $materials_request->stock_id;
                $material_id = $materials_request->material_id;
                $from_stock = $materials_request->from_stock;
                $stock_material = StockMaterial::where('stock_id', $stock_id)->where('material_id', $material_id)->first();

                if ($stock_material) {
                    if (StockMaterial::where('material_id', $material_id)
                        ->where('stock_id', $stock_id)
                        ->increment('qty', $materials_request->qty)) {
                        $success = true;
                    } else {
                        $success = false;
                    }
                } else {
                    $values[] = [
                        'material_id' => $material_id,
                        'stock_id' => $stock_id,
                        'qty' => $materials_request->qty
                    ];
                }
                if (count($values) > 0) {
                    if (StockMaterial::insert($values)) {
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
                if ($success) {
                    DB::commit();
                    // удалим из промежуточной таблицы
                    $materials_request->delete();
                    // запишем в историю
                    MaterialStory::insert([
                        'material_id' => $material_id,
                        'from_stock' => $from_stock,
                        'author_id' => $author_id,
                        'stock_id' => $stock_id,
                        'qty' => $materials_request->qty
                    ]);
                    $json = [
                        'success' => true,
                        'message' => 'Расходники успешно перемещены'
                    ];
                } else {
                    DB::rollBack();
                    $json = [
                        'success' => false,
                        'message' => 'Возникла какая-то ошибка'
                    ];
                }
            }
        }

        return $json;
    }

    public function materialsRequestDelete(StockAcceptRejectRequest $request): array
    {
        $data = $request->validated();
        $materialsIds = $data['id'];
        $json = [
            'success' => false,
            'message' => 'Что-то пошло не так.'
        ];

        foreach ($materialsIds as $materialsId) {
            $materials_request = MaterialRequest::findOrFail($materialsId);
            $from_stock_check = $materials_request->from_stock;
            $user_id_check = $materials_request->user_id;
            if (is_null($from_stock_check) && $user_id_check != '') {
                $success = false;
                $user_id = $materials_request->user_id;
                $material_id = $materials_request->material_id;
                $user_material = UserMaterial::where('user_id', $user_id)->where('material_id', $material_id)->first();

                if ($user_material) {
                    # Increment in user inventory
                    if (UserMaterial::where('material_id', $material_id)
                        ->where('user_id', $user_id)
                        ->increment('qty', $materials_request->qty)) {
                        $success = true;
                    }
                } else {
                    # Insert into user inventory
                    if (UserMaterial::insert([
                        'material_id' => $material_id,
                        'user_id' => $user_id,
                        'qty' => $materials_request->qty
                    ])) {
                        $success = true;
                    }
                }
                if ($success) {
                    $json = [
                        'success' => true,
                        'message' => 'Расходники отклонены и возвращены успешно.'
                    ];
                    $materials_request->delete();
                } else {
                    $json = [
                        'success' => false,
                        'message' => 'При передаче расходников что-то пошло не так.'
                    ];
                }
            } elseif (is_null($user_id_check) && $from_stock_check != '') {
                $success = false;
                $material_id = $materials_request->material_id;
                $from_stock = $materials_request->from_stock;
                $stock_material = StockMaterial::where('stock_id', $from_stock)->where('material_id', $material_id)->first();

                if ($stock_material) {
                    # Increment in from stock
                    if (StockMaterial::where('material_id', $material_id)
                        ->where('stock_id', $from_stock)
                        ->increment('qty', $materials_request->qty)) {
                        $success = true;
                    }
                }
                if ($success) {
                    DB::commit();
                    $json = [
                        'success' => true,
                        'message' => 'Расходники отклонены и возвращены успешно.'
                    ];
                    $materials_request->delete();
                } else {
                    DB::rollBack();
                    $json = [
                        'success' => false,
                        'message' => 'При передаче расходников что-то пошло не так.'
                    ];
                }
            }
        }

        return $json;
    }

    /**
     * @throws Throwable
     */
    public function getMassEquipment(Request $request): array
    {
        if ($request->equipment_numbers != '') {
            $serials = explode("\n", $request->equipment_numbers);
            $stock_id = $request->stock_id;
            $kits = [];
            $kit_ids = [];
            $clean = 1;
            foreach ($serials as $serial) {
                $serial = trim($serial);
                $kit = Kit::where('v_serial', $serial)->where('stock_id', $stock_id)->first();
                if ($kit) {
                    $kit->wrong = false;
                    $kits[$serial] = $kit;
                    $kit_ids[] = $kit->id;
                    $kit->owner = isset($kit->owner()->name) ? $kit->owner()->name : $kit->owner_id;
                } else {
                    $kit = Kit::where('v_serial', $serial)->first();
                    if ($kit) {
                        $kit->wrong = true;
                        $clean = 0;
                        $kit->owner = $kit->owner()->name ?? $kit->owner_id;
                        $kits[$serial] = $kit;
                    } else {
                        $collection = new Collection;
                        $collection->wrong = true;
                        $clean = 0;
                        $collection->v_type = '?';
                        $collection->stock = '?';
                        $collection->owner = '?';
                        $kits[$serial] = $collection;
                    }
                }
            }
            $json = [
                'clean' => $clean,
                'html' => view('inventory.mass_result', compact(['clean', 'kits', 'kit_ids']))->render()
            ];
        } else {
            $json = [
                'clean' => 0,
                'html' => ''
            ];
        }
        return $json;
    }

    public function movementStory(Request $request): array
    {
        $stockId = $request->get('stock_id');
        $dateFrom = $request->has('date_from') ? $request->get('date_from') : date("Y-m-d", strtotime("-1 month")) . " 00:00";
        $dateTo = $request->has('date_to') ? $request->get('date_to') : date("Y-m-d", strtotime("now")) . " 23:59";
        if ($stockId && $stockId != '') {
            $stock = Stock::find($stockId);
            $equipment_story = $stock->getEquipmentStory();
            $materials_story = $stock->getMaterialsStory();
            $uniqueEqId = EquipmentStory::whereBetween("created_at", [$dateFrom, $dateTo])
                ->select('equipment_id', DB::raw('MAX(id) as id'))
                ->groupBy('equipment_id')
                ->get();
            $unique = $uniqueEqId->pluck('id')->toArray();
            $showRollbackBtn = Auth::user()->id == 4 || Auth::user()->id == 153; //TODO WTF, узнать что это за функционал и почему тут должны быть эти айдишники. Перенести айдишник в новую таблицу и убрать Хардкод.
            $result = [
                'success' => true,
                'data' => compact(['equipment_story', 'materials_story', 'unique', 'showRollbackBtn'])
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'Пожалуйста, выберите склад.'
            ];
        }

        return $result;
    }

    public function rollbackKit(Request $request): array
    {
        $kit_story = $request->id;
        $kit_story_row = EquipmentStory::where("id", $kit_story)->first();
        $from = $kit_story_row->from;
        $from_stock = $kit_story_row->from_stock;
        $kit_id = $kit_story_row->equipment_id;
        $story = EquipmentStory::findOrFail($request->id);

        $kit = Kit::find($kit_id);

        if (is_null($from) && $from_stock != '') {
            $kit->stock_id = $from_stock;
            $kit->owner_id = null;

            foreach ($kit->equipments as $eq) {
                $equip = Equipment::find($eq->id);
                $equip->stock_id = $from_stock;
                $equip->owner_id = null;
                $equip->save();
            }

        } else {
            $kit->stock_id = null;
            $kit->owner_id = $from;

            foreach ($kit->equipments as $eq) {
                $equip = Equipment::find($eq->id);
                $equip->stock_id = null;
                $equip->owner_id = $from;
                $equip->save();
            }
        }

        if ($kit->save()) {
            $story->delete();
            $json = [
                'success' => true,
                'message' => 'Комплект успешно откатился.'
            ];
        } else {
            $json = [
                'success' => false,
                'message' => 'При откате комплекта ' . $kit->v_serial . ' произошла ошибка.'
            ];
        }

        return $json;
    }

    private function moveMaterialsFromStock($request)
    {
        if (isset($request->materials)) {
            $materials = explode(',', $request->materials);
        } else {
            $message = [
                'success' => false,
                'message' => 'Неверно указаны расходники'
            ];
            return redirect()->back()->with($message);
        }
        if (isset($request->stock_id)) {
            $stock_id = $request->stock_id;
        } else {
            $message = [
                'success' => false,
                'message' => 'Не указано с какого склада вы хотите выдать расходники'
            ];
            return redirect()->back()->with($message);
        }
        if (isset($request->qty) && $request->qty[0] > 0) {
            $qty = $request->qty;
        } else {
            $message = [
                'success' => false,
                'message' => 'Не указано количество выдаваемых расходников'
            ];
            return redirect()->back()->with($message);
        }
        return [
            'qty' => $qty,
            'materials' => $materials,
            'stock_id' => $stock_id
        ];
    }

    /**
     * @param array $request_values
     * @param bool $success
     * @return array
     */
    private function materialInserting(array $request_values, bool $success = false): array
    {
        if (count($request_values) > 0) {
            if (MaterialRequest::insert($request_values)) {
                $success = true;
            } else {
                $success = false;
            }
        }
        if ($success) {
            DB::commit();
            $message = [
                'success' => true,
                'message' => 'Расходники успешно переданы. Ожидайте принятия складом.'
            ];
        } else {
            DB::rollBack();
            $message = [
                'success' => false,
                'message' => 'Возникла какая-то ошибка'
            ];
        }
        return $message;
    }

    /**
     * @param array $request_values
     * @param bool $success
     * @param $kit
     * @return array
     */
    private function kitMoving(array $request_values, bool $success, $kit): array
    {
        if (count($request_values) > 0) {
            if (KitRequest::insert($request_values)) {
                $success = true;
            } else {
                $success = false;
            }
        }
        if ($success) {
            DB::commit();
            $message = [
                'success' => true,
                'message' => 'Комплект(ы) успешно передан(ы). Ожидайте принятия складом.'
            ];
        } else {
            DB::rollBack();
            $message = [
                'success' => false,
                'message' => 'При передаче комплекта ' . $kit->v_serial . ' произошла ошибка.'
            ];
        }
        return $message;
    }
}
