<?php

namespace App\Services;

use App\Http\Controllers\MaterialLimitController;
use App\Http\Requests\MaterialLimit\Statistic\CreateRequest;
use App\Http\Requests\SuzRequest\AssignRequest;
use App\Http\Requests\SuzRequest\CancelRequest;
use App\Http\Requests\SuzRequest\CompleteRequest;
use App\Http\Requests\SuzRequest\PostponeRequest;
use App\Http\Requests\SuzRequest\ReturnRequest;
use App\Http\Traits\AdminTrait;
use App\Http\Traits\CatalogsTrait;
use App\Http\Traits\SoapTrait;
use App\Models\Equipment;
use App\Models\Kit;
use App\Models\Material;
use App\Models\MaterialLimitStatistic;
use App\Models\RequestRepairType;
use App\Models\RouteList;
use App\Models\SuzRequest;
use App\Models\SuzRequestStory;
use App\Models\User;
use DocxMerge\DocxMerge;
use Exception;
use Geeky\Database\CacheQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;

class SuzRequestService
{
    use SoapTrait, CatalogsTrait, AdminTrait, CacheQueryBuilder;

    public function index(Request $request): array
    {
        try {
            $select = array(
                'id',
                'id_flow',
                'id_kind_works',
                'ltype_works',
                'status_id',
                'dt_start',
                'dt_plan_date'
            );
            $requests = SuzRequest::select($select)->orderBy('dt_start', 'desc');

            if ($request->status && $request->status != 'all') {
                $requests = $requests->where('status_id', (int)$request->status)->orderBy('dt_start', 'desc');
            }

            $date_from = $request->date_from ? $request->date_from : date('Y-m-d', strtotime('now -1 week'));
            $date_to = $request->date_to ? $request->date_to : date('Y-m-d');
            $inspector_deps = [];

            if (Auth::user()->hasRole('инспектор') && !Auth::user()->hasAnyRole('администратор', 'диспетчер')) {
                $get_deps = Auth::user()->departments()->get();
                $inspector_deps = $get_deps->map->only(['id', 'id_department', 'v_name', 'v_ext_ident']);
                $v_ext_ident = $inspector_deps->first();
                $department = $request->department ? $request->department : $v_ext_ident['v_ext_ident'];
            } else {
                $department = $request->department && $request->department != 'all' ? $request->department : false;
            }
            $kind_work_id = $request->work_type && $request->work_type != 'all' ? $request->work_type : false;
            $type_work_id = $request->work_subtype && $request->work_subtype != 'all' ? $request->work_subtype : false;
            $requests = $requests->whereBetween('dt_start', [$date_from . " 00:00:00", $date_to . " 23:59:59"]);

            if ($department) {
                $requests = $requests->where('v_department', $department);
            }
            $type_works = null;

            if ($kind_work_id) {
                $requests = $requests->where('id_kind_works', $kind_work_id);
                $type_works = $this->getTypeWorksByKindWorkId($kind_work_id);
            }

            if ($type_work_id) {
                $requests = $requests->where('ltype_works', 'like', '%' . $type_work_id . '%');
            }

            $requests = $requests->paginate(15);

            foreach ($requests as &$req) {
                $req->kind_works = $this->getKindWorksById($req->id_kind_works);
                $req->type_works = '-';
                if ($req->ltype_works != '{}') {
                    $id_type_works_json = json_decode($req->ltype_works);
                    $req->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
                }
                $req->status = $this->getStatusById($req->status_id)->name;
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
        $statuses = $this->getStatuses();
        $departments = $this->getDepartments();
        $kind_works = $this->getKindWorks();

        return compact(['requests', 'statuses', 'departments', 'kind_works', 'type_works', 'inspector_deps']);
    }

    public function show(int $id)
    {

        $me = Auth::user() ?? Auth::guard('api')->user();
        $request = SuzRequest::find($id);
        $checkData = [];

        if (!$request) {
            abort(404);
        }

        $request->status = $this->getStatusById($request->status_id)->name;
        $request->ci_flow = $this->getCiFlow($request->id_ci_flow);
        $request->department = $this->getDepartment($request->v_department);
        $request->contract = $request->v_contract;
        $request->location = $this->getLocation($request->id_location);
        $request->sector = $this->getSector($request->id_sector);
        $request->region = $this->getRegion($request->id_region);
        $request->district = $this->getDistrict($request->id_district);
        $request->town = $this->getTown($request->id_town);
        $request->street = $this->getStreet($request->id_street);
        $request->house = $this->getHouse($request->id_house);
        $request->flat = $request->v_flat;
        $request->kind_works = $this->getKindWorksById($request->id_kind_works);
        $request->type_works = '-';

        if ($request->ltype_works != '{}') {
            $id_type_works_json = json_decode($request->ltype_works);
            $request->id_type_works = $id_type_works_json->id_type_works;
            $request->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
        }

        $request->description = $request->v_flow_descr;
        $request->description_time = $request->v_flow_time_descr;
        $request->dt_plan_date = setPlanDate($request);
        $request->n_plan_time = setPlanDayTime($request);
        $request_repair_type_row = RequestRepairType::select("id_type")->where("request_id", $request->id)->get();
        $repair_array = collect();

        foreach ($request_repair_type_row as $row) {
            $id_type = $row->id_type;
            $repair_name = $this->getRepairName($id_type);
            $repair_array->push($repair_name);
        }

        $request->repair_name = $repair_array;
        $request->images = $this->getImages($request->id);
        $request->product = $this->getProduct($request->id_product);
        $request->tariff = $this->getTariff($request->id_tplan);
        $request->document_type = $this->getDocumentType($request->id_document_type);

        $service_info_collection = json_decode($request->service_info);
        $request->service_info = str_replace(['[', ']', '\\'], '', $request->service_info);

        foreach ($service_info_collection as &$service_info) {
            $service_info->name = $this->getService($service_info->id_service);
            $service_info->technology = $this->getTechnology($service_info->id_service_technology);
            foreach ($service_info->equipment_list as &$equipment_list) {
                $equipment_list->equipment_model = $this->getEquipmentModel($equipment_list->id_equipment_model);
                $equipment_list->equipment_number = $equipment_list->v_equipment_number;
                $equipment_list->equipment_transfer = $this->getEquipmentTransfer($equipment_list->v_equipment_transfer);
                $equipment_list->equipment_serial = $equipment_list->v_serial;
                $exists = Equipment::where('v_equipment_number', $equipment_list->v_equipment_number)->first();
                $equipment_list->exists = (bool)$exists;
            }
        }

        $request->limitcrossed = MaterialLimitStatistic::select('materials_limit_statistic.*', DB::raw('SUM(qty) as qty_sum'))
            ->where('request_id', $id)
            ->groupBy('material_id')
            ->get();

        $request->service = $service_info_collection;
        $request->coordinates = $request->getCoordinates() ? json_encode($request->getCoordinates()) : null;
        $request->basic_coordinates = json_encode($request->getBasicCoordinates());

        $request->installers = $request->getRequestInstallers();
        $request->date = $request->getLastDate();
        $request->dt_status_start = $request->getLastDtStart();
        $request->reason = $request->getLastReason();
        $request->dispatcher = $request->getDispatcher();
        $request->cancel_reason = $request->getRequestCancelReason();
        $request->materials = Material::all();
        $request->client_materials = $this->getClientMaterials($request->v_contract);

        foreach ($request->client_materials as $client_material) {
            $checkData['clientMaterial'][] = $client_material;
            $checkData['request_id'] = $id;
        }

        $checkBinding = checkBinding($checkData);

        foreach ($checkBinding as $item) {
            if ($item['bindedMaterialAmount']) {
                foreach ($request->client_materials as &$client_material) {
                    if ($client_material->id === $item['id']) {
                        $client_material->bindedAmount = $item['bindedMaterialAmount'];
                    }
                }
            }
        }

        $installers = $request->installers ? $request->installers->pluck('id')->toArray() : [];
        $request->is_my_request = in_array($me->id, $installers) || $me->hasAnyRole('администратор|диспетчер');

        return $request;
    }

    //TODO ФормРеквест себя странно ведет, при вызове с Http/Controllers/SuzRequestController.php:249, если не будет правильно по рулсам, то просто редиректит почему то при валидейтед.
    public function complete(CompleteRequest $request, SuzRequest $suzRequest): array
    {
        $validated = $request->validated();

        $me = Auth::user();
        $request_id = $validated['request_id'];
        $id_type = $validated['id_type'] ?? null;
        $installers = $validated['installers'] ?? null;
        $v_kits_transfer = $validated['v_kits_transfer'] ?? null;
        $v_param_internet = $validated['v_param_internet'] ?? '';
        $materials = [];
        $use_clients_equipment = decodeJsonOrNull($validated['use_clients_equipment']);
        $status_id = $this->getStatusByName('Выполнено')->id;
        $equipments_to_take = decodeJsonOrNull($validated['equipments_to_take']);
        $kits_to_give = decodeJsonOrNull($validated['kits_to_give']);
        $kits_to_take = decodeJsonOrNull($validated['kits_to_take']);
        $v_kits_transfer = decodeJsonOrNull($v_kits_transfer);

        $b_unbind_cntr_arr = decodeJsonOrNull($validated['b_unbind_cntr']);
        $installer_take = decodeJsonOrNull($validated['installer_take']);
        $installer_give = decodeJsonOrNull($validated['installer_give']);
        $materials_take = decodeJsonOrNull($validated['materials_take']);
        $materials_qty_take = decodeJsonOrNull($validated['materials_qty_take']);
        $materials_give = decodeJsonOrNull($validated['materials_give']);
        $materials_qty_give = decodeJsonOrNull($validated['materials_qty_give']);

        $additional_info_list = [];
        $additional_info_list[] = [
            'name' => 'ID_CLASSIFIER',
            'value' => $id_type
        ];

        if (!is_null($installers)) {
            if (strripos($installers, ',') === false) {
                $additional_info_list[] = [
                    'name' => 'V_FLOW_EXECUTOR',
                    'value' => $installers,
                ];
            } else {
                $explodes = explode(', ', $installers);
                foreach ($explodes as $installer) {
                    $additional_info_list[] = [
                        'name' => 'V_FLOW_EXECUTOR',
                        'value' => $installer,
                    ];
                }
            }
        } else {
            $additional_info_list[] = [
                'name' => 'V_FLOW_EXECUTOR',
                'value' => null,
            ];
        }


// Changing original request's data
        $suzRequest->status_id = $status_id;
        $suzRequest->dt_stop = date("Y-m-d H:i:s");
        $suzRequest->fill([
            'dt_birthday' => $validated['dt_birthday'],
            'v_iin' => $validated['v_iin'],
            'v_document_number' => $validated['v_document_number'],
            'dt_document_issue_date' => $validated['dt_document_issue_date'],
            'v_document_series' => $validated['v_document_series'],
        ]);

// Copying request to story
        $suzRequest_copy = $suzRequest->replicate();
        $suzRequest_copy->fill([
            'request_id' => $request_id,
            'status_id' => $status_id,
            'dispatcher_id' => $me->id,
            'dt_start' => date("Y-m-d H:i:s"),
            'dt_stop' => "2555-01-01 00:00:00",
            'comment' => $validated['comment'] ?? '',
            'comment_author' => $me->id,
        ]);

// Updating dt_stop of last story record
        $suzRequestStory = SuzRequestStory::where('request_id', $request_id)->latest('id')->first();

        if ($suzRequestStory) {
            $suzRequestStory->dt_stop = date("Y-m-d H:i:s");
        }

        // Let's start transaction
        DB::beginTransaction();
        try {
            if ($id_type) {
                DB::table('request_repair_type')->insert([
                    'id_type' => $id_type,
                    'request_id' => $request_id
                ]);
            }

            if ($request->filled('coordinates')) {
                list($latitude, $longitude) = explode(",", $request->get('coordinates'));

                DB::table('house_coordinates_complete')->insert([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'id_house' => $suzRequest->id_house
                ]);
            }

            // Materials to give to client
            if (!empty($materials_give)) {
                foreach ($materials_give as $mgkey => $material_id) {
                    $installer = $installer_give[$mgkey];
                    $qty = $materials_qty_give[$mgkey];
                    $materials[] = [
                        'id' => $material_id,
                        'qty' => $qty
                    ];
                    updateMaterials($material_id, $installer, $qty, $suzRequest->v_contract);
                    insertMaterialStory($material_id, $materials_qty_give, $installer_give, $request_id, $suzRequest, $mgkey);
                }
            }

            // Materials to take from client
            if (!empty($materials_take)) {
                foreach ($materials_take as $mtkey => $m) {
                    if ($material = Material::find($m)) {
                        updateMaterials($material->id, $installer_take[$mtkey], $materials_qty_take[$mtkey],  $suzRequest->v_contract);
                        insertMaterialStory($material->id, $materials_qty_take, $installer_take, $request_id, $suzRequest, $mtkey, 2);
                    }
                }
            }

            if (isset($kits_to_give)) {
                $kitIds = [];

                foreach ($kits_to_give as $kit_id_arr) {
                    if ($kit_id_arr) {
                        $kitIds = array_unique(array_merge($kitIds, $kit_id_arr));
                    }
                }

                if (!empty($kitIds)) {
                    $kits = Kit::whereIn('id', $kitIds)->get();
                    foreach ($kits as $kit) {
                        $kit->owner_id = $suzRequest->v_contract;
                        $kit->writeStory($me->id, $suzRequest->v_contract, Auth::user()->id, null, null, $suzRequest->id_flow, $v_kits_transfer[0]);
                    }

                    Kit::whereIn('id', $kitIds)->update(['owner_id' => $suzRequest->v_contract]);
                    Equipment::whereIn('kit_id', $kitIds)->update(['stock_id' => null, 'owner_id' => $suzRequest->v_contract]);
                }
            }

            // Writing story record about this request
            DB::table("suz_request_story")->insert($suzRequest_copy->toArray());


            // Sending SOAP-query to Forward
            $closeMethodName = $suzRequest->getCloseMethod();

            if ($closeMethodName && $closeMethodName != '' && $this->getSoapSettingByCode($closeMethodName)->enabled) {
                $data = compact(
                    'id_type',
                    'b_unbind_cntr_arr',
                    'closeMethodName',
                    'suzRequest',
                    'request_id',
                    'kits_to_give',
                    'v_kits_transfer',
                    'v_param_internet',
                    'suzRequest_copy',
                    'installers',
                    'equipments_to_take',
                    'kits_to_take',
                    'use_clients_equipment',
                    'additional_info_list'
                );

                $closing = $this->closeChooice($data); //TODO вынес эту хуйню отсюда, нужно отрефакторить там каждый кейс, это пиздец какой-то.

                if (!$closing['success']) {
                    return $closing;
                }

                if (isset($closing['params']) && count($closing['params']) > 0) {
                    $soapResponse = $this->$closeMethodName($closing['params']);

                    if ($soapResponse->code == 200) {
                        $suzRequest->save();
                        $suzRequestStory->save();

                        DB::commit();
                    } elseif (in_array($soapResponse->code, [431, 441])) {
                        throw new Exception("Статус СУЗ не соответствует статусу в Форварде.", 1);
                    } else {
                        throw new Exception($soapResponse->code . ' - ' . $soapResponse->message, 1);
                    }
                } else {
                    throw new Exception("Error Processing Request. Undefined \$params." . $request_id, 1);
                }
            }
        } catch (\Throwable $th) {
            DB::rollback();

            Log::error('Error in completing request', ['file' => $th->getFile(), 'line' => $th->getLine()]);

            $json = [
                'success' => false,
                'route' => '/requests/' . $request_id,
                'message' => 'Rolling back: ' . $th->getMessage(),
            ];

            $this->log($th->getTraceAsString(), 'stack_trace.log', $request_id);
            return $json;
        }
        if (isset($soapResponse)) {
            $json = [
                'success' => true,
                'route' => '/requests/' . $request_id,
                'message' => $soapResponse->code == 200 ? 'Заявка завершена успешно.' : $soapResponse->code . " - " . $soapResponse->message
            ];
        } else {
            $json = [
                'success' => false,
                'route' => '/requests/' . $request_id,
                'message' => 'Внимание, не налажена связь с Forward!'
            ];
        }

        if (isset($validated['limit_input']) && $validated['limit_input']) {
            $limit_data = [
                'materials' => $materials,
                'request_id' => $request_id,
                'installer_id' => auth()->user()->id
            ];
            $materialLimitRequest = CreateRequest::createFrom($request, $limit_data);
            $materialLimitRequest->setContainer(app());
            MaterialLimitController::storeStatistic($materialLimitRequest);
        }

        return $json;
    }

    public function writeMaterial(Request $request): array
    {
        $request_id = $request->id;
        $give_story = false;
        $take_story = false;
        $suzRequest = SuzRequest::find($request_id);

        $json = [
            'success' => false,
            'message' => 'Расходники не записаны!'
        ];

        $installer_take = decodeJson($request->installer_take);
        $installer_give = decodeJson($request->installer_give);
        $materials_take = decodeJson($request->materials_take);
        $materials_qty_take = decodeJson($request->materials_qty_take);
        $materials_give = decodeJson($request->materials_give);
        $materials_qty_give = decodeJson($request->materials_qty_give);


        if (isset($materials_give) || isset($materials_take)) {
            if ($materials_give) {
                foreach ($materials_give as $mgkey => $material_id) {
                    $installer = $installer_give[$mgkey];
                    $qty = $materials_qty_give[$mgkey];
                    updateMaterials($material_id, $installer, $qty, $suzRequest->v_contract);

                    // writing this material movement to story
                    $give_story = insertMaterialStory($material_id, $materials_qty_give, $installer_give, $request_id, $suzRequest, $mgkey);
                }
                if ($give_story) {
                    $json = [
                        'success' => true,
                        'message' => 'Расходники успешно записаны в базу.'
                    ];
                }
            }

            if ($materials_take) {
                foreach ($materials_take as $mtkey => $m) {
                    if ($material = Material::find($m)) {
                        updateMaterials($material->id, $materials_qty_take[$mtkey], $installer_take[$mtkey], $suzRequest->v_contract);
                    }

                    // writing this material movement to story
                    $take_story = insertMaterialStory($material->id, $materials_qty_take, $installer_take, $request_id, $suzRequest, $mtkey, 2);
                }
                if ($take_story) {
                    $json = [
                        'success' => true,
                        'message' => 'Расходники успешно записаны в базу.'
                    ];
                } else {
                    $json = [
                        'success' => false,
                        'message' => 'Расходники не записаны!'
                    ];
                }
            }
        } else {
            $json = [
                'message' => 'Расходников нет!'
            ];
        }

        return $json;
    }

    public function assign(AssignRequest $req): array
    {
        $validated = $req->validated();

        if (isset($validated['request_id']) && isset($validated['date_time']) && isset($validated['routelist_id'])) {
            $request_id = (int)$validated['request_id'];
            $date_time = $validated['date_time'];
            $date = date('Y-m-d', strtotime($date_time));
            $time = date('H:i', strtotime($date_time));
            $routelist_id = (int)$validated['routelist_id'];

            // Валидация даты и времени
            if (!validateDate($date_time, 'Y-m-d H:i')) {
                return [
                    'success' => false,
                    'message' => 'Дата и время указаны неверно',
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Заполните все поля',
            ];
        }

        $dispatcher_id = Auth::user()->id;
        $status_id = $this->getStatusByName('Назначено')->id;

        $routelist = RouteList::find($routelist_id);
        $installers = $routelist->installers();

        if (count($installers) > 0) {
            $installer_1 = (int)$installers[0]->id != 0 ? (int)$installers[0]->id : null;
            $installer_2 = null;

            if (isset($installers[1]) && (int)$installers[1]->id != 0) {
                $installer_2 = (int)$installers[1]->id;
            }
        } else {
            return [
                'success' => false,
                'message' => 'В маршрутном листе должен быть хотя бы один монтажник',
            ];
        }

        // Меняем статус заявки на 'Назначено'
        $request = SuzRequest::find($request_id);
        $old_status = $request->status_id;
        $request->status_id = $status_id;
        $request->routelist_id = $routelist_id;
        $request->dt_plan_date = $date;

        // Копируем заявку
        $request_copy = $request->replicate();
        $request_copy->request_id = $request_id;
        $request_copy->status_id = $status_id;
        $request_copy->dt_start = date("Y-m-d H:i:s");
        $request_copy->dt_stop = "2555-01-01 00:00:00";
        $request_copy->dispatcher_id = $dispatcher_id;
        $request_copy->installer_1 = $installer_1;
        $request_copy->installer_2 = $installer_2 ?? null;
        $request_copy->date = $date_time;

        // Начинаем транзакцию
        DB::beginTransaction();
        try {
            // Обновляем дату последней записи в истории
            DB::table("suz_request_story")
                ->where("request_id", $request_id)
                ->orderBy("id", "desc")
                ->take(1)->update(['dt_stop' => date("Y-m-d H:i:s")]);

            // записываем в таблицу истории
            DB::table("suz_request_story")->insert($request_copy->toArray());

            // связываем заявку с диспетчером
            DB::table("suz_request_dispatcher")->where("request_id", $request_id)->delete();
            DB::table("suz_request_dispatcher")->insert([
                'request_id' => $request_id, 'dispatcher_id' => $dispatcher_id
            ]);

            // привязываем заявку к маршрутному листу
            DB::table("request_route_list")->where("request_id", $request_id)->delete();
            DB::table("request_route_list")->insert([
                'request_id' => $request_id, 'routelist_id' => $routelist_id, 'time' => $date_time
            ]);

            $canCommit = false;

            // если с "отложено" на "назначено"
            if ($old_status == 2) { //TODO Хардкод, нужно сделать Enums для всех констант в проекте
                // Отправляем SOAP-запрос об отложении заявки в Форвард
                if ($this->getSoapSettingByCode('MoveFromDelayedFW')->enabled) {
                    $params = [
                        'id_flow' => $request_copy->id_flow,
                        'v_description' => '',
                    ];
                    $soapResponse = $this->MoveFromDelayedFW($params);
                    if ($soapResponse->code == 200) {
                        $canCommit = true;
                    } elseif (in_array($soapResponse->code, [431, 441])) { //TODO Хардкод, нужно сделать Enums для всех констант в проекте
                        throw new Exception("Статус СУЗ не соответствует статусу в Форварде.", 1);
                    } else {
                        throw new Exception($soapResponse->code . ' - ' . $soapResponse->message, 1);
                    }
                }
            } elseif ($old_status == 7) {
                $canCommit = true;
            } else {
                // Отправляем SOAP-запрос об отложении заявки в Форвард
                if ($this->getSoapSettingByCode('SetDate')->enabled) {
                    $inst = $installer_1 ?: $installer_2;
                    $worker_name = User::find($inst)->name;
                    $plan_time = convertToPlanTime($time);
                    $params = [
                        'id_flow' => $request_copy->id_flow,
                        'v_worker' => $worker_name,
                        'v_flow_time_descr' => date("H:i", strtotime($time)),
                        'v_description' => '',
                        'dt_plan_date' => date(DATE_ATOM, strtotime($date_time)),
                        'n_plan_time' => $plan_time
                    ];
                    $soapResponse = $this->SetDate($params);

                    if ($soapResponse->code == 200) {
                        $canCommit = true;
                    } elseif (in_array($soapResponse->code, [431, 441])) {
                        throw new Exception("Статус СУЗ не соответствует статусу в Форварде.", 1);
                    } else {
                        throw new Exception($soapResponse->code . ' - ' . $soapResponse->message, 1);
                    }
                }
            }

            if ($canCommit) {
                DB::commit();
                $request->save();
                $ci_flow = $this->getCiFlow($request->id_ci_flow);
                $kind_works = $this->getKindWorksById($request->id_kind_works);
                $town = $this->getTown($request->id_town);
                $street = $this->getStreet($request->id_street);
                $house = $this->getHouse($request->id_house);
                $flat = $request->v_flat;
                $txt = "Номер заказа: " . $request->id_flow . "\n";
                $txt .= "Тип заказа: " . $ci_flow . "\n";
                $txt .= "Тип работ: " . $kind_works . "\n";

                if (isset($validated['old_time']) && date('Y-m-d', strtotime($validated['old_time'])) != date('Y-m-d', strtotime('1970-01-01'))) {
                    $txt .= "Старая дата работ: " . date('Y-m-d H:i', strtotime($validated['old_time'])) . "\n";
                    $txt .= "Новая дата работ: " . date('Y-m-d H:i', strtotime($date_time)) . "\n";
                } else {
                    $txt .= "Запланированная дата работ: " . date('Y-m-d H:i', strtotime($date_time)) . "\n";
                }
                $txt .= "Адрес: " . $town . ', ул.' . $street . ', ' . $house . ', кв. ' . $flat . "\n";
                $txt .= "Ссылка на заказ:" . env('APP_URL') . "/requests/" . $request_id . "\n";
                $user_1 = User::find($installer_1);
                $telegramToken = env('TELEGRAM_BOT_TOKEN');

                if ($user_1->telegram_id != NULL) {
                    $this->send2Telegram($user_1->telegram_id, $txt, $telegramToken);
                }

                if (isset($installer_2) && $installer_2 != NULL) {
                    $user_2 = User::find($installer_2);
                    if ($user_2->telegram_id != NULL) {
                        $this->send2Telegram($user_2->telegram_id, $txt, $telegramToken);
                    }
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Статус СУЗ не соответствует статусу Форварда'
                ];
            }
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Rolling back: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
        return [
            'success' => true,
            'message' => 'Заявка назначена на ' . $time
        ];
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function downloadWord(Request $req): array
    {
        $files = [];

        if (!isset($req->ids) || !$req->ids) {
            $route = '/routelist/';
            return [
                'success' => false,
                'route' => $route,
                'message' => 'Заявки отсутствуют в маршрутном листе',
            ];
        } else {
            foreach ($req->ids as $id) {
                $request = SuzRequest::find($id);
                if ($request->status_id == 1) {
                    $route = '/requests/';
                    return [
                        'success' => false,
                        'route' => $route,
                        'message' => 'Заявка не назначена на бригаду',
                    ];
                } else {
                    $request->ci_flow = $this->getCiFlow($request->id_ci_flow);
                    $request->contract = $request->v_contract;
                    $request->location = $this->getLocation($request->id_location);
                    $request->sector = $this->getSector($request->id_sector);
                    $request->region = $this->getRegion($request->id_region);
                    $request->district = $this->getDistrict($request->id_district);
                    $request->town = $this->getTown($request->id_town);
                    $request->street = $this->getStreet($request->id_street);
                    $request->house = $this->getHouse($request->id_house);
                    $request->flat = $request->v_flat;
                    $address = $request->town . ', ' . $request->street . ', ' . $request->house . ', кв.' . $request->flat;
                    $request->kind_works = $this->getKindWorksById($request->id_kind_works);
                    $request->type_works = '-';

                    if ($request->ltype_works != '{}') {
                        $id_type_works_json = json_decode($request->ltype_works);
                        $request->id_type_works = $id_type_works_json->id_type_works;
                        $request->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
                    }

                    $request->description = $request->v_flow_descr;
                    $request->description_time = $request->v_flow_time_descr;

                    $request_route_list_row = DB::table("request_route_list")
                        ->select("time")
                        ->where("request_id", $request->id)->first();

                    $time = '';
                    if ($request_route_list_row) {
                        $time = $request_route_list_row->time;
                    }
                    if ($time != '' && $time != '0000-00-00 00:00:00') {
                        $request->dt_plan_date = $time;
                    } else {
                        $n_plan_time_arr = [
                            0 => 'до обеда',
                            1 => 'после обеда',
                            2 => 'в течение дня'
                        ];
                        $request->dt_plan_date .= " " . $n_plan_time_arr[$request->n_plan_time];
                    }
                    $request->tariff = $this->getTariff($request->id_tplan);
                    $service_info_collection = json_decode($request->service_info);
                    $request->service_info = str_replace(['[', ']', '\\'], '', $request->service_info);

                    foreach ($service_info_collection as &$service_info) {
                        $service_info->name = $this->getService($service_info->id_service);
                        $service_info->technology = $this->getTechnology($service_info->id_service_technology);

                        foreach ($service_info->equipment_list as &$equipment_list) {
                            $equipment_list->equipment_model = $this->getEquipmentModel($equipment_list->id_equipment_model);
                            $equipment_list->equipment_number = $equipment_list->v_equipment_number;
                            $equipment_list->equipment_transfer = $this->getEquipmentTransfer($equipment_list->v_equipment_transfer);
                            $equipment_list->equipment_serial = $equipment_list->v_serial;
                            $exists = Equipment::where('v_equipment_number', $equipment_list->v_equipment_number)->first();
                            $equipment_list->exists = (bool)$exists;
                        }
                    }

                    $request->service = $service_info_collection;
                    $internet_arr = null;
                    $ctv_arr = null;
                    $stv_arr = null;
                    $tvbox_arr = null;
                    $atv_arr = null;
                    $internet_arr = collect();
                    $ctv_arr = collect();
                    $stv_arr = collect();
                    $tvbox_arr = collect();
                    $atv_arr = collect();

                    foreach ($request->service as $service) {
                        if ($service->equipment_list && $service->name == 'Интернет') { //TODO Хардкод
                            foreach ($service->equipment_list as $eq) {
                                $internet_model = $eq->equipment_model->v_name;
                                $internet_serial = $eq->equipment_number;
                                $internet_transfer = $eq->equipment_transfer;
                                $internet_push = $internet_model . '_' . $internet_serial . '_' . $internet_transfer;
                                $internet_arr->push($internet_push);
                            }
                        } elseif ($service->equipment_list && $service->name == 'ЦТВ') { //TODO Хардкод
                            foreach ($service->equipment_list as $eq) {
                                $ctv_model = $eq->equipment_model->v_name;
                                $ctv_serial = $eq->equipment_number;
                                $ctv_transfer = $eq->equipment_transfer;
                                $ctv_push = $ctv_model . '_' . $ctv_serial . '_' . $ctv_transfer;
                                $ctv_arr->push($ctv_push);
                            }
                        } elseif ($service->equipment_list && $service->name == 'STV') { //TODO Хардкод
                            foreach ($service->equipment_list as $eq) {
                                $stv_model = $eq->equipment_model->v_name;
                                $stv_serial = $eq->equipment_number;
                                $stv_transfer = $eq->equipment_transfer;
                                $stv_push = $stv_model . '_' . $stv_serial . '_' . $stv_transfer;
                                $stv_arr->push($stv_push);
                            }
                        } elseif ($service->equipment_list && $service->name == 'TV BOX') { //TODO Хардкод
                            foreach ($service->equipment_list as $eq) {
                                $tvbox_model = $eq->equipment_model->v_name;
                                $tvbox_serial = $eq->equipment_number;
                                $tvbox_transfer = $eq->equipment_transfer;
                                $tvbox_push = $tvbox_model . '_' . $tvbox_serial . '_' . $tvbox_transfer;
                                $tvbox_arr->push($tvbox_push);
                            }
                        } elseif ($service->equipment_list && $service->name == 'АТВ') { //TODO Хардкод
                            foreach ($service->equipment_list as $eq) {
                                $atv_model = $eq->equipment_model->v_name;
                                $atv_serial = $eq->equipment_number;
                                $atv_transfer = $eq->equipment_transfer;
                                $atv_push = $atv_model . '_' . $atv_serial . '_' . $atv_transfer;
                                $atv_arr->push($atv_push);
                            }
                        }
                    }

                    $internet = $internet_arr->implode(', ');
                    $ctv = $ctv_arr->implode(', ');
                    $stv = $stv_arr->implode(', ');
                    $tvbox = $tvbox_arr->implode(', ');
                    $atv = $atv_arr->implode(', ');
                    $services_arr = null;
                    $services_arr = collect();

                    foreach ($request->service as $service) {
                        $services_arr->push($service->name);
                    }

                    $services = $services_arr->implode(', ');
                    $request->dispatcher = $request->getDispatcher();
                    $dispatcher = $request->dispatcher->name;
                    $request->materials = Material::all();
                    $request->client_materials = $this->getClientMaterials($request->v_contract);
                    $request->installers = $request->getInstallers($request->id);
                    $json_array = json_decode($request->installers, true);

                    if (isset($json_array[0]) && $json_array[0]) {
                        $installer_1 = $json_array[0];
                    } else {
                        $installer_1 = 'Не назначено';
                    }
                    if (isset($json_array[1]) && $json_array[1]) {
                        $installer_2 = $json_array[1];
                    } else {
                        $installer_2 = '';
                    }
                    if ($request->id_ci_flow == 2402 || $request->id_ci_flow == 2440) {
                        $tempName = "request_types_1_test.docx";
                    } elseif ($request->id_ci_flow == 2403) {
                        $tempName = "request_types_3_test.docx";
                    } else {
                        $tempName = "request_types_2_test.docx";
                    }

                    $templateProcessor = new TemplateProcessor('word-patterns/' . $tempName);

                    $templateProcessor->setValue('id_ci_flow', $request->ci_flow);
                    $templateProcessor->setValue('id_flow', $request->id_flow);
                    $templateProcessor->setValue('id', $request->id);
                    $templateProcessor->setValue('tariff', $request->tariff);
                    $templateProcessor->setValue('contract', $request->contract);
                    $templateProcessor->setValue('installer_1', $installer_1);
                    $templateProcessor->setValue('installer_2', $installer_2);
                    $templateProcessor->setValue('type_works', $request->type_works);
                    $templateProcessor->setValue('description_time', $request->description_time);
                    $templateProcessor->setValue('dispatcher', $dispatcher);
                    $templateProcessor->setValue('client_name', $request->v_client_title);
                    $templateProcessor->setValue('address', $address);
                    $templateProcessor->setValue('dt_plan_date', $request->dt_plan_date);
                    $templateProcessor->setValue('location', $request->location);
                    $templateProcessor->setValue('sector', $request->sector);
                    $templateProcessor->setValue('cell_phone', $request->v_client_cell_phone);
                    $templateProcessor->setValue('landline_phone', $request->v_client_landline_phone);
                    $templateProcessor->setValue('description', $request->description);
                    $templateProcessor->setValue('service', $services);
                    $templateProcessor->setValue('internet', $internet);
                    $templateProcessor->setValue('ctv', $ctv);
                    $templateProcessor->setValue('stv', $stv);
                    $templateProcessor->setValue('tvbox', $tvbox);
                    $templateProcessor->setValue('atv', $atv);

                    $saveName = $id . '.docx';
                    $fileStorage = storage_path('app/word/' . $saveName);
                    $files[] = storage_path('app/word/' . $saveName);
                    $templateProcessor->saveAs($fileStorage);
                }
            }
            $name = 'suz_requests_' . date('d-m-Y_H-i-s') . '_auth_' . Auth::user()->id . '.docx';
            $dm = new DocxMerge();
            $dm->merge($files, storage_path('app/word/' . $name), true);

            if (Storage::exists('/word/' . $name)) {
                File::delete($files);
            } else {
                $route = '/routelist/';
                return [
                    'success' => false,
                    'route' => $route,
                    'message' => 'Возникла ошибка при создании документа',
                ];
            }

            return [
                'success' => true,
                'filename' => $name
            ];
        }
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function postpone(PostponeRequest $req)
    {

        $validated = $req->validated();

        if (!isset($validated['request_id']) || !isset($validated['date']) || !isset($validated['time'])) {
            abort(503);
        }

        $request_id = $validated['request_id'];
        $date = $validated['date'];
        $time = $validated['time'];

        // Валидация даты и времени
        if (!validateDate($date)) {
            throw ValidationException::withMessages([
                'date' => ['Дата указана неверно']
            ]);
        }

        $reason = addslashes($validated['reason']);
        $dispatcher_id = Auth::user()->id;

        $status_id = 2;

        // Меняем статус заявки на 'Отложено'
        $request = SuzRequest::find($request_id);

        if (in_array($request->status_id, [1, 3, 5])) {
            throw new Exception("Вы не можете отложить новую заявку.", 1);
        }

        $request->status_id = $status_id;
        $request->dt_plan_date = $date;
        $request->routelist_id = null;

        // Копируем заявку
        $request_copy = $request->replicate();
        $request_copy->request_id = $request_id;
        $request_copy->status_id = $status_id;
        $request_copy->dt_start = date("Y-m-d H:i:s");
        $request_copy->dt_stop = "2555-01-01 00:00:00";
        $request_copy->dispatcher_id = $dispatcher_id;
        $request_copy->date = $date;
        $installers = $request->getRequestInstallers();
        $installer_1 = null;
        $installer_2 = null;

        if ($installers) {
            $installer_1 = $installers[0]->id;
            if (isset($installers[1])) {
                $installer_2 = $installers[1]->id;
            }
        }

        $request_copy->installer_1 = $installer_1;
        $request_copy->installer_2 = $installer_2;
        $request_copy->reason = $reason;

        // Начинаем транзакцию
        DB::beginTransaction();
        try {
            // Обновляем дату последней записи в истории
            DB::table("suz_request_story")
                ->where("request_id", $request_id)
                ->orderBy("id", "desc")
                ->take(1)->update(['dt_stop' => date("Y-m-d H:i:s")]);

            // связываем заявку с диспетчером
            DB::table("suz_request_dispatcher")->where("request_id", $request_id)->delete();
            DB::table("suz_request_dispatcher")->insert([
                'request_id' => $request_id, 'dispatcher_id' => $dispatcher_id
            ]);

            // удаляем заявку из маршрута
            DB::table("request_route_list")->where("request_id", $request_id)->delete();

            // записываем в таблицу истории
            DB::table("suz_request_story")->insert($request_copy->toArray());

            // Отправляем SOAP-запрос об отмене в Форвард
            if ($this->getSoapSettingByCode('MoveToDelayedFW')->enabled) {
                $params = [
                    'id_flow' => $request_copy->id_flow,
                    'v_description' => $reason,
                    'dt_delayed' => date(DATE_ATOM, strtotime($date . ' ' . $time))
                ];
                $soapResponse = $this->MoveToDelayedFW($params);

                if ($soapResponse->code == 200) {
                    DB::commit();
                    $request->save();
                } elseif (in_array($soapResponse->code, [431, 441])) {
                    throw new Exception("Статус СУЗ не соответствует статусу в Форварде.", 1);
                } else {
                    throw new Exception($soapResponse->code . ' - ' . $soapResponse->message, 1);
                }
            } else {
                $this->log('MoveToDelayedFW не пашет', 'soapclient.log', $request_id);
                throw new Exception("Error Processing Request on MoveToDelayedFW. ID: " . $request_id, 1);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            throw ValidationException::withMessages([
                'rollback' => ["Rolling back: " . $th->getMessage()]
            ]);
        }
    }


    /**
     * @throws ValidationException
     */
    public function cancel(CancelRequest $req): array
    {
        $validated = $req->validated();

        if (isset($validated['request_id'])) {
            $request = SuzRequest::find($validated['request_id']);
            if (!$request) {
                abort(404);
            } else {
                $dispatcher_id = Auth::user()->id;
                // установим статус в 'отменено' и дату завершения - текущий момент
                $status_id = $this->getStatusByName('Отменено')->id;

                if ($request->status_id == 5) {
                    return [
                        'success' => false,
                        'route' => '/requests/' . $request->id,
                        'message' => 'Заявка уже выполнена, вы не можете отменить ее! Обновите страницу.'
                    ];
                }

                $request->status_id = $status_id;
                $request->dt_stop = date("Y-m-d H:i:s");

                // Копируем заявку
                $request_copy = $request->replicate();
                $request_copy->request_id = (int)$validated['request_id'];
                $request_copy->status_id = $status_id;
                $request_copy->dt_start = date("Y-m-d H:i:s");
                $request_copy->dispatcher_id = $dispatcher_id;
                $request_copy->reason = addslashes($validated['reason']);
                $request_copy->comment = addslashes($validated['reason']);
                $request_copy->comment_author = $dispatcher_id;
                $request_copy->cancel_reason_id = $validated['id_reason'];

                // Начинаем транзакцию
                DB::beginTransaction();
                try {
                    // Обновляем дату последней записи в истории
                    DB::table("suz_request_story")
                        ->where("request_id", $validated['request_id'])
                        ->orderBy("id", "desc")
                        ->take(1)->update(['dt_stop' => date("Y-m-d H:i:s")]);

                    // связываем заявку с диспетчером
                    DB::table("suz_request_dispatcher")->where("request_id", (int)$validated['request_id'])->delete();
                    DB::table("suz_request_dispatcher")->insert([
                        'request_id' => $validated['request_id'],
                        'dispatcher_id' => $dispatcher_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // записываем в таблицу истории
                    DB::table("suz_request_story")->insert($request_copy->toArray());

                    // Отправляем SOAP-запрос об отмене в Форвард
                    if ($this->getSoapSettingByCode('CancelFlowOut')->enabled && isset($validated['id_reason'])) {
                        $params = [
                            'id_flow' => $request_copy->id_flow,
                            'id_reason' => $validated['id_reason'],
                            'v_description' => $validated['reason'],
                            'dt_cancel' => date(DATE_ATOM)
                        ];

                        $soapResponse = $this->CancelFlowOut($params);

                        if ($soapResponse->code == 200) {
                            DB::commit();
                            $request->save();
                        } elseif (in_array($soapResponse->code, [431, 441])) {
                            throw new Exception("Статус СУЗ не соответствует статусу в Форварде.", 1);
                        } else {
                            throw new Exception($soapResponse->code . ' - ' . $soapResponse->message, 1);
                        }
                    } else {
                        $this->log('CancelFlowOut не пашет', 'soapclient.log', $validated['request_id']);
                        throw new Exception("Error Processing Request on CancelFlowOut. ID: " . $validated['request_id'], 1);
                    }
                } catch (\Throwable $th) {
                    DB::rollback();
                    throw ValidationException::withMessages([
                        'rollback' => ["Rolling back: " . $th->getMessage()]
                    ]);
                }
            }

            return [
                'success' => true,
                'route' => 'request',
                'params' => [
                    'id' => $request->id
                ],
                'message' => 'Заявка отменена'
            ];
        }

        return [
            'success' => false,
            'route' => '/requests/' . $validated['request_id'],
            'message' => 'Отсутствует параметр заявки!'
        ];
    }

    public function return(ReturnRequest $request): array
    {
        $validated = $request->validated();

        $me = Auth::user();

        $request_id = $validated['request_id'];
        $comment = $validated['comment'];
        $status_id = $this->getStatusByName('Договорено')->id;
        $suzRequest = SuzRequest::find($request_id);
        $materials = $request->materials;

        if (!$suzRequest) {
            return [
                'route' => '/requests/' . $request_id,
                'message' => 'Произошла непредвиденная ошибка!',
                'success' => false
            ];
        }
        if ($suzRequest->status_id == 3 || $suzRequest->status_id == 5) {
            return [
                'route' => '/requests/' . $request_id,
                'message' => 'Заявка завершена, вы не можете повлиять на нее!',
                'success' => false
            ];
        }

        $suzRequest->status_id = $status_id;
        $suzRequest->routelist_id = null;

        $suzRequest_copy = $suzRequest->replicate();
        $suzRequest_copy->request_id = $request_id;
        $suzRequest_copy->status_id = $status_id;
        $suzRequest_copy->dispatcher_id = $suzRequest->getDispatcher()->id ?? null;
        $suzRequest_copy->dt_start = date("Y-m-d H:i:s");
        $suzRequest_copy->dt_stop = "2555-01-01 00:00:00";
        $suzRequest_copy->installer_1 = null;
        $suzRequest_copy->installer_2 = null;
        $suzRequest_copy->comment = $comment ?? null;
        $suzRequest_copy->comment_author = $me->id;

        // Начинаем транзакцию
        DB::beginTransaction();
        try {
            // Обновляем дату последней записи в истории
            DB::table("suz_request_story")
                ->where("request_id", $request_id)
                ->orderBy("id", "desc")
                ->take(1)->update(['dt_stop' => date("Y-m-d H:i:s")]);

            // записываем в таблицу истории
            DB::table("suz_request_story")->insert($suzRequest_copy->toArray());

            // отвязываем заявку из маршрутного листа
            DB::table("request_route_list")->where("request_id", $request_id)->delete();

            DB::commit();
            $suzRequest->save();
        } catch (\Throwable $th) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Rolling back: ' . $th->getMessage(),
            ];
        }

        $json = [
            'message' => 'Заявка отвязана от вас.'
        ];

        if ($me->hasRole('администратор')) {
            $redirectTo = '/requests/' . $request_id;
        } else {
            $redirectTo = '/routelist';
        }

        if (isset($validated['limit_input']) && $validated['limit_input'] && $validated['materials']) {
            $limit_data = [
                'materials' => $materials,
                'request_id' => $request_id,
                'installer_id' => auth()->user()->id
            ];

            $materialLimitRequest = CreateRequest::createFrom($request, $limit_data);
            $materialLimitRequest->setContainer(app());

            MaterialLimitController::storeStatistic($materialLimitRequest);
        }

        return [
            'route' => $redirectTo,
            'success' => true,
            'message' => $json['message']
        ];
    }

    public function completeIndex(Request $request): array
    {
        $limit_input = $request->limit_input ?? null;
        $request_id = $request->request_id;
        $suzRequest = SuzRequest::find($request_id);
        $request_id_flow = $suzRequest->id_flow;
        $client_title = $suzRequest->v_client_title;
        $latitude = null;
        $longitude = null;

        $equipments_to_take = $request->equipments_to_take;
        $kits_to_take = $request->kits_to_take;
        $kits_to_give = $request->kits_to_give;

        $v_kits_transfer = $request->v_kits_transfer ?? null;
        $b_unbind_cntr = $request->b_unbind_cntr ?? null;

        $v_param_internet = $request->v_param_internet ?? null;

        $installer_take = $request->installer_take ?? null;
        $installer_give = $request->installer_give ?? null;
        $materials_take = $request->materials_take ?? null;
        $materials_qty_take = $request->materials_qty_take ?? null;
        $materials_give = $request->materials_give ?? null;
        $materials_qty_give = $request->materials_qty_give ?? null;
        $status_type = $request->status_type ?? 'complete';

        $dt_birthday = $request->dt_birthday;
        $v_iin = strlen($request->v_iin) == 12 ? $request->v_iin : str_pad($request->v_iin, 12, '0', STR_PAD_LEFT);
        $v_document_number = $request->v_document_number;
        $dt_document_issue_date = $request->dt_document_issue_date;
        $v_document_series = $request->v_document_series;
        $installers_arr = $request->installers;
        $installers = implode(", ", $installers_arr);

        $use_clients_equipment = $request->use_clients_equipment;

        $id_house = $suzRequest->id_house;
        $coords = DB::table('house_coordinates_yandex')->select('latitude', 'longitude')->where('id_house', $id_house)->get();

        if ($coords) {
            foreach ($coords as &$crd) {
                $latitude = $crd->latitude;
                $longitude = $crd->longitude;
            }
        }

        $count = DB::table('house_coordinates_complete')->where('id_house', $id_house)->count();

        if ($suzRequest->id_ci_flow == 2404) {
            $repair_type = DB::table('repair_type')->select('id_type', 'v_name')->get();
        } else {
            $repair_type = null;
        }

        $request_data = $request->all();

        return compact([
            'limit_input',
            'request_data',
            'status_type',
            'request_id',
            'equipments_to_take',
            'kits_to_take',
            'kits_to_give',
            'request_id_flow',
            'client_title',
            'v_kits_transfer',
            'b_unbind_cntr',
            'v_param_internet',
            'materials_take',
            'materials_qty_take',
            'materials_give',
            'materials_qty_give',
            'dt_birthday',
            'v_iin',
            'v_document_number',
            'dt_document_issue_date',
            'v_document_series',
            'use_clients_equipment',
            'latitude',
            'longitude',
            'count',
            'repair_type',
            'installer_take',
            'installer_give',
            'installers'
        ]);
    }

    public function story(Request $req): array
    {
        $requests = SuzRequestStory::where('request_id', $req->id)->get();

        foreach ($requests as &$request) {
            $suzRequest = SuzRequest::find($request->request_id);
            $request->status = $this->getStatusById($request->status_id)->name;
            $request->ci_flow = $this->getCiFlow($request->id_ci_flow);
            $request->department = $this->getDepartment($request->v_department);
            $request->contract = $request->v_contract;
            $request->location = $this->getLocation($request->id_location);
            $request->sector = $this->getSector($request->id_sector);
            $request->region = $this->getRegion($request->id_region);
            $request->district = $this->getDistrict($request->id_district);
            $request->town = $this->getTown($request->id_town);
            $request->street = $this->getStreet($request->id_street);
            $request->house = $this->getHouse($request->id_house);
            $request->flat = $request->v_flat;
            $request->kind_works = $this->getKindWorksById($request->id_kind_works);
            $request->type_works = '-';

            if ($request->ltype_works != '{}') {
                $id_type_works_json = json_decode($request->ltype_works);
                $request->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
            }
            $request->description = $request->v_flow_descr;
            $request->description_time = $request->v_flow_time_descr;
            $request_route_list_row = DB::table("request_route_list")
                ->select("time")
                ->where("request_id", $request->id)->first();
            $time = '';
            if ($request_route_list_row) {
                $time = $request_route_list_row->time;
            }
            if ($time != '' && $time != '0000-00-00 00:00:00') {
                $request->dt_plan_date = $time;
            } else {
                $n_plan_time_arr = [
                    0 => 'до обеда',
                    1 => 'после обеда',
                    2 => 'в течение дня'
                ];
                $request->dt_plan_date .= " " . $n_plan_time_arr[$request->n_plan_time];
            }
            $request->product = $this->getProduct($request->id_product);
            $request->tariff = $this->getTariff($request->id_tplan);
            $request->document_type = $this->getDocumentType($request->id_document_type);

            $service_info_collection = json_decode($request->service_info);
            $request->service_info = str_replace(['[', ']', '\\'], '', $request->service_info);

            foreach ($service_info_collection as &$service_info) {
                $service_info->name = $this->getService($service_info->id_service);
                $service_info->technology = $this->getTechnology($service_info->id_service_technology);
                foreach ($service_info->equipment_list as &$equipment_list) {
                    $equipment_list->equipment_model = $this->getEquipmentModel($equipment_list->id_equipment_model);
                    $equipment_list->equipment_number = $equipment_list->v_equipment_number;
                    $equipment_list->equipment_transfer = $this->getEquipmentTransfer($equipment_list->v_equipment_transfer);
                    $equipment_list->equipment_serial = $equipment_list->v_serial;
                }
            }
            $request->service = $service_info_collection;
            $request->coordinates = $suzRequest->getCoordinates() ? json_encode($suzRequest->getCoordinates()) : null;
            $request->basic_coordinates = json_encode($suzRequest->getBasicCoordinates());
            $request->installer_1 = $request->installer_1 ? User::find($request->installer_1)->name : null;
            $request->installer_2 = $request->installer_2 ? User::find($request->installer_2)->name : null;
            $request->date = $suzRequest->getLastDate();
            $request->dt_status_start = $suzRequest->getLastDtStart();
            $request->reason = $suzRequest->getLastReason();
            $request->dispatcher = $request->dispatcher_id ? User::find($request->dispatcher_id)->name : null;
            $request->cancel_reason = $suzRequest->getRequestCancelReason();
            $request->materials = $this->getClientMaterials($request->v_contract);
            $request->comment_author = $request->comment_author ? User::find($request->comment_author)->name : '';
        }

        return compact(['requests']);
    }

    public function fixEquipment(Request $request): array
    {
        $message = [];
        $request_id = $request->request_id;
        $v_equipment_number = $request->v_equipment_number;
        $suzRequest = SuzRequest::find($request_id);
        $service_info = json_decode($suzRequest->service_info);
        $kits = [];

        foreach ($service_info as $si) {
            foreach ($si->equipment_list as $equipment_list) {
                if ($equipment_list->v_equipment_number == $v_equipment_number) {
                    if (!array_key_exists($equipment_list->v_serial, $kits)) {
                        $id_kits = rand(123789, 9712389);
                        $row = DB::table('alma_grid_fill_eq_kits_type')->select('id_equip_kits_type')->where('id_equipment_model', $equipment_list->id_equipment_model)->first();
                        $v_type = null;
                        if ($row) {
                            $id_equip_kits_type = $row->id_equip_kits_type;
                            $row2 = DB::table('alma_equipment_kits_type')->select('v_mnemonic')->where('id_equip_kits_type', $id_equip_kits_type)->first();
                            if ($row2) {
                                $v_type = $row2->v_mnemonic;
                            }
                        }
                        if ($v_type === NULL) {
                            return [
                                'success' => false,
                                'message' => 'Мнемоника комплекта не определилась!'
                            ];
                        } else {
                            $kitExists = DB::table('kit')->where('v_serial', $equipment_list->v_serial)->first();
                            if (!$kitExists) {
                                $insertKits = [
                                    'id_flow' => $suzRequest->id_flow,
                                    'v_type' => $v_type,
                                    'id_kits' => $id_kits,
                                    'v_department' => $suzRequest->v_department,
                                    'v_serial' => $equipment_list->v_serial,
                                    'id_status' => 2000,
                                    'dt_activate' => date('Y-m-d'),
                                    'owner_id' => $suzRequest->v_contract
                                ];
                                $kitId = DB::table('kit')->insertGetId($insertKits);
                            } else {
                                $kitId = $kitExists->id;
                            }
                            $kits[$equipment_list->v_serial] = $kitId;
                        }
                    } else {
                        $kitId = $kits[$equipment_list->v_serial];
                    }
                    $insertEquipments = [
                        'id_equipment_model' => $equipment_list->id_equipment_model,
                        'id_equipment_inst' => $equipment_list->id_equipment_inst,
                        'v_equipment_number' => $equipment_list->v_equipment_number,
                        'kit_id' => $kitId,
                        'owner_id' => $suzRequest->v_contract
                    ];
                    if (DB::table('equipment')->insert($insertEquipments)) {
                        $message = [
                            'success' => true,
                            'message' => 'Оборудование записано в базу!'
                        ];
                    } else {
                        $message = [
                            'success' => false,
                            'message' => 'Оборудование не записано в базу!'
                        ];
                    }
                }
            }
        }

        return $message;
    }

    public function send2Telegram(int $id, string $msg, string $token, bool $silent = false)
    {
        $data = [
            'chat_id' => $id,
            'text' => $msg,
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'disable_notification' => $silent
        ];

        if ($token != '') {
            $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendMessage'); //TODO Хардкод
            curl_setopt_array($ch, [
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $data
            ]);
            curl_exec($ch);
            curl_close($ch);
        }

        Log::info('send2Telegram', ['data' => $data, 'token' => $token, 'url' => 'https://api.telegram.org/bot' . $token . '/sendMessage', 'ch' => $ch]);
    }

    public function storyByGroup(Request $request): array
    {
        try {
            $date = $request->date ? $request->date : date('Y-m-d');
            $department = $request->department && $request->department != 'all' ? $request->department : false;
            $select = "SELECT  a.request_id, a.id_flow, a.status_id, a.id_kind_works, a.ltype_works, a.dt_start, a.dt_plan_date, a.dt_flow_dt_event, a.id_ci_flow
						FROM    suz_request_story a
						        INNER JOIN
						        (
						            SELECT  id_flow, MAX(dt_start) mxdate
						            FROM    suz_request_story
						            GROUP   BY id_flow
						        ) b ON a.id_flow = b.id_flow
						                AND a.dt_start = b.mxdate WHERE v_department = '$department' AND dt_start BETWEEN '$date 00:00:00' AND '$date 23:59:59' ORDER BY dt_start DESC";
            $rows = DB::select($select);
            $requests = collect($rows);

            if ($request->status && $request->status != 'all') {
                $requests = $requests->where('status_id', (int)$request->status);
            }
            if ($request->work_group && $request->work_group == 'new') //TODO Хардкод
            {
                $requests = $requests->whereIn('id_ci_flow', ['2425', '2402']);
            }
            if ($request->work_group && $request->work_group == 'service') //TODO Хардкод
            {
                $requests = $requests->whereNotIn('id_ci_flow', ['2425', '2402']);
            }

            foreach ($requests as &$req) {
                $req->kind_works = $this->getKindWorksById($req->id_kind_works);
                $req->type_works = '-';

                if ($req->ltype_works != '{}') {
                    $id_type_works_json = json_decode($req->ltype_works);
                    $req->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
                }

                $req->status = $this->getStatusById($req->status_id)->name;
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        $statuses = $this->getStatuses();
        $departments = $this->getDepartments();

        return compact(['requests', 'statuses', 'departments']);
    }

    private function log($message, $filename, $request_id = false)
    {
        $message = "[" . date('Y-m-d H:i:s') . "] - " . $message;
        if ($request_id) {
            $message = $message . ' - [' . $request_id . ']' . "\n";
        }
        file_put_contents(base_path('storage/logs/') . $filename, $message, FILE_APPEND);
    }

    //TODO Надо как то оптимизировать эту хуйню.
    private function closeChooice($data): array
    {
        $params = [];
        $id_type = $data['id_type'];
        $b_unbind_cntr_arr = $data['b_unbind_cntr_arr'];
        $closeMethodName = $data['closeMethodName'];
        $suzRequest = $data['suzRequest'];
        $request_id = $data['request_id'];
        $kits_to_give = $data['kits_to_give'];
        $v_kits_transfer = $data['v_kits_transfer'];
        $v_param_internet = $data['v_param_internet'];
        $suzRequest_copy = $data['suzRequest_copy'];
        $installers = $data['installers'];
        $equipments_to_take = $data['equipments_to_take'];
        $kits_to_take = $data['kits_to_take'];
        $use_clients_equipment = $data['use_clients_equipment'];
        $additional = $data['additional_info_list'];

        switch ($closeMethodName) {
            case 'CloseFlowConnect':
            {
                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $key => $si) {
                    if ($si) {
                        $kits_list = [];
                        // If 'Use clients equipment' checkbox is checked
                        if (isset($use_clients_equipment[$key]) && $use_clients_equipment[$key] == 'Y') //TODO Хардкод
                        {
                            // Searching kit by v_serial from service_info
                            foreach ($si->equipment_list as $eq_list) {
                                $clients_kit = Kit::where('v_serial', $eq_list->v_serial)->first();
                                if (!$clients_kit) {
                                    $message = "Система не может найти комплект клиента. Серийный номер: " . $eq_list->v_serial;
                                    $this->log($message, 'stack_trace.log', $request_id);

                                    return [
                                        'success' => false,
                                        'route' => '/requests/' . $request_id,
                                        'message' => $message,
                                    ];
                                }

                                $kits_list[] = [
                                    'v_type' => $clients_kit->v_type,
                                    'v_serial' => $clients_kit->v_serial,
                                    'v_kits_transfer' => 'R' // rent by default
                                ];
                                $contract = (string)$suzRequest->v_contract;

                                // update kit
                                $clients_kit->stock_id = null;
                                $clients_kit->owner_id = $contract;
                                $clients_kit->returned = 0;

                                $clients_kit->save();

                                // update equipment
                                $equipments_to_update = Equipment::where('kit_id', $clients_kit->id)->get();
                                foreach ($equipments_to_update as &$eq) {
                                    $eq->stock_id = null;
                                    $eq->owner_id = $contract;

                                    if (!$eq->save()) {
                                        $message = "Ошибка при обновлении оборудования. ID комплекта:" . "$clients_kit->id";
                                        $this->log($message, 'stack_trace.log', $request_id);
                                        return [
                                            'success' => false,
                                            'route' => '/requests/' . $request_id,
                                            'message' => $message,
                                        ];
                                    }
                                }
                            }
                        } else {
                            if ($kits_to_give && isset($kits_to_give[$key])) {
                                foreach ($kits_to_give[$key] as $kit_id) {
                                    $kit = Kit::find($kit_id);
                                    if ($kit) {
                                        $kits_list[] = [
                                            'v_type' => $kit->v_type,
                                            'v_serial' => $kit->v_serial,
                                            'v_kits_transfer' => $v_kits_transfer[$key]
                                        ];
                                    }
                                }
                            }
                        }
                        foreach ($si->service_info_add as &$sia) {
                            if ((int)$sia->id_service_inst == 0) {
                                $sia->id_service_inst = null;
                            }
                            if ($sia->v_service_status == 'W') //TODO Хардкод
                            {
                                $sia->v_service_status = 'A';
                            }
                        }
                        $service_info_arr_subarray = [
                            'id_rec' => $key,
                            'id_service' => $si->id_service ?? '',
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service_technology' => $si->id_service_technology ?? '',
                            'kits_list' => $kits_list,
                            'service_info_add' => json_decode(json_encode($si->service_info_add), true),
                        ];
                        $service_name = $this->getService($si->id_service);
                        if ($service_name == 'Интернет') //TODO Хардкод
                        {
                            $service_info_arr_subarray['v_param_internet'] = $v_param_internet;
                        }
                        $service_info_arr[] = $service_info_arr_subarray;
                    }
                }

                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'DONE_SUCCES',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'dt_cancel' => null,
                    'id_reason_cancel' => null,
                    'v_description_cancel' => null,
                    'f_confidential' => 10000, // n
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'once_srvs_list' => [],
                    'v_iin' => $suzRequest->v_iin ?? '000000000000',
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers,
                    'additional_info_list' => $additional
                ];

                if ($suzRequest->id_document_type != 0) {
                    $params['id_document_type'] = (string)$suzRequest->id_document_type;
                } else {
                    $params['id_document_type'] = null;
                }

                break;
            }
            case 'CloseFlowRepair':
            {
                if (shouldProcessData($equipments_to_take, $kits_to_take)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте.',
                        ];
                    }
                }
                if ((isset($equipments_to_take) && count($equipments_to_take) > 0) || (isset($kits_to_take) && count($kits_to_take) > 0) || (isset($kits_to_give) && count($kits_to_give) > 0)) {
                    $v_result = 'REPAIR_WITH_EQ_CHANGE';
                } else {
                    $v_result = 'REPAIR_WITHOUT_EQ_CHANGE';
                }

                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $kits_list = [];

                        if (isset($kits_to_give[$si_key]) && $kits_to_give) {
                            foreach ($kits_to_give[$si_key] as $kit_id) {
                                $kit = Kit::find($kit_id);
                                if ($kit) {
                                    $kits_list[] = [
                                        'v_type' => $kit->v_type,
                                        'v_serial' => $kit->v_serial,
                                        'v_kits_transfer' => $v_kits_transfer[$si_key]
                                    ];
                                }
                            }
                        }

                        $return_kits_list = [];
                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;

                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }
                        foreach ($si->service_info_add as &$sia) {
                            if ((int)$sia->id_service_inst == 0) {
                                $sia->id_service_inst = null;
                            }
                            if ($sia->v_service_status == 'W') //TODO Хардкод
                            {
                                $sia->v_service_status = 'A';
                            }
                        }
                        $service_info_arr[] = [
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet,
                            'kits_list' => $kits_list,
                            'return_kits_list' => $return_kits_list,
                            'service_info_add' => json_decode(json_encode($si->service_info_add), true)
                        ];
                    }
                }

                $id_reason_repair = 2081;
                $v_action_repair = $suzRequest_copy->comment ?? null;

                if (!$v_action_repair) {
                    return [
                        'success' => false,
                        'route' => '/requests/' . $request_id,
                        'message' => 'Вы забыли указать предпринятые действия (комментарий).',
                    ];
                }

                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => $v_result,
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'dt_cancel' => null,
                    'id_reason_cancel' => null,
                    'v_description_cancel' => null,
                    'id_reason_repair' => $id_reason_repair, // ??
                    'v_action_repair' => $v_action_repair,
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers . '; ' . $id_type,
                    'additional_info_list' => $additional
                ];

                if ($suzRequest->id_document_type != 0) {
                    $params['id_document_type'] = (string)$suzRequest->id_document_type;
                } else {
                    $params['id_document_type'] = null;
                }
                break;
            }
            case 'CloseFlowBlockDebt':
            {
                if (shouldProcessData($equipments_to_take, $kits_to_take)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте.',
                        ];
                    }
                }

                $service_info_arr = array();
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $return_kits_list = array();
                        if (isset($equipments_to_take) && count($equipments_to_take) > 0) {
                            foreach ($equipments_to_take as $key => $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && count($kits_to_take) > 0) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }

                        $service_info_arr[] = [
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet,
                            'return_kits_list' => $return_kits_list
                        ];
                    }
                }

                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'DONE_SUCCES',
                    'id_pay_debt' => '',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'id_document_type' => $suzRequest->id_document_type != 0 ? (string)$suzRequest->id_document_type : null,
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers,
                    'additional_info_list' => $additional
                ];
                break;
            }
            case 'CloseFlowBlock':
            {
                if (shouldProcessData($equipments_to_take, $kits_to_take)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте.',
                        ];
                    }
                }

                $service_info_arr = array();
                $service_info = json_decode($suzRequest->service_info);
                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $return_kits_list = array();
                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }
                        $service_info_arr[] = [
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet,
                            'return_kits_list' => $return_kits_list
                        ];
                    }
                }
                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'DONE_SUCCES',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'id_document_type' => $suzRequest->id_document_type != 0 ? (string)$suzRequest->id_document_type : null,
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers,
                    'additional_info_list' => $additional
                ];
                break;
            }
            case 'CloseFlowUnblock':
            {
                if ((isset($equipments_to_take) && count($equipments_to_take) > 0) || (isset($kits_to_take) && count($kits_to_take) > 0)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте',
                        ];
                    }
                }

                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $kits_list = [];
                        if (isset($kits_to_give) && $kits_to_give) {
                            foreach ($kits_to_give as $key => $kit_id_arr) {
                                if ($kit_id_arr) {
                                    foreach ($kit_id_arr as $kit_id) {
                                        if ($kit_id && $key == $si_key) {
                                            $kit = Kit::find($kit_id);
                                            $kits_list[] = [
                                                'v_type' => $kit->v_type,
                                                'v_serial' => $kit->v_serial,
                                                'v_kits_transfer' => $v_kits_transfer[$key]
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $return_kits_list = [];

                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $key => $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }
                        $service_info_arr[] = [
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet,
                            'kits_list' => $kits_list,
                            'return_kits_list' => $return_kits_list
                        ];
                    }
                }

                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'DONE_SUCCES',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'once_srvs_list' => [],
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'id_document_type' => $suzRequest->id_document_type != 0 ? (string)$suzRequest->id_document_type : null,
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => '',
                    'additional_info_list' => $additional
                ];
                break;
            }
            case 'CloseFlowChangeProduct':
            {
                if (shouldProcessData($equipments_to_take, $kits_to_take)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте.',
                        ];
                    }
                }
                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $kits_list = [];
                        if (isset($kits_to_give) && count($kits_to_give) > 0) {
                            foreach ($kits_to_give as $key => $kit_id_arr) {
                                if ($kit_id_arr) {
                                    foreach ($kit_id_arr as $kit_id) {
                                        if ($kit_id && $key == $si_key) {
                                            $kit = Kit::find($kit_id);
                                            $kits_list[] = [
                                                'v_type' => $kit->v_type,
                                                'v_serial' => $kit->v_serial,
                                                'v_kits_transfer' => $v_kits_transfer[$key]
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $return_kits_list = [];

                        if (isset($equipments_to_take) && count($equipments_to_take) > 0) {
                            foreach ($equipments_to_take as $key => $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && count($kits_to_take) > 0) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }
                        foreach ($si->service_info_add as &$sia) {
                            if ((int)$sia->id_service_inst == 0) {
                                $sia->id_service_inst = null;
                            }
                            if ($sia->v_service_status == 'W') //TODO Хардкод
                            {
                                $sia->v_service_status = 'A';
                            }
                        }
                        if ((int)$si->id_service_inst == 0) {
                            $si->id_service_inst = null;
                        }
                        $service_info_arr[] = [
                            'id_rec' => $si_key,
                            'id_service_inst' => $si->id_service_inst,
                            'id_service_technology' => $si->id_service_technology ?? '',
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet ?? '',
                            'service_info_add' => json_decode(json_encode($si->service_info_add), true),
                            'kits_list' => $kits_list,
                            'return_kits_list' => $return_kits_list
                        ];
                    }
                }
                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'INSTALLATION_EQ',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),

                    'dt_cancel' => null,
                    'id_reason_cancel' => null,
                    'v_description_cancel' => null,

                    'f_confidential' => 10000,

                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,

                    'once_srvs_list' => [],

                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'id_document_type' => $suzRequest->id_document_type != 0 ? (string)$suzRequest->id_document_type : null,
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers,
                    'additional_info_list' => $additional
                ];
                break;
            }
            case 'CloseFlowChangeTech':
            {
                if ((isset($equipments_to_take) && count($equipments_to_take) > 0) || (isset($kits_to_take) && count($kits_to_take) > 0)) {
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте',
                        ];
                    }
                }
                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);

                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $kits_list = [];
                        if (isset($kits_to_give) && $kits_to_give) {
                            foreach ($kits_to_give as $key => $kit_id_arr) {
                                if ($kit_id_arr) {
                                    foreach ($kit_id_arr as $kit_id) {
                                        if ($kit_id && $key == $si_key) {
                                            $kit = Kit::find($kit_id);
                                            $kits_list[] = [
                                                'v_type' => $kit->v_type,
                                                'v_serial' => $kit->v_serial,
                                                'v_kits_transfer' => $v_kits_transfer[$key]
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $return_kits_list = [];
                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }
                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = [
                                        'v_type' => $v_type,
                                        'v_serial' => $kit_serial,
                                        'b_unbind_cntr' => $b_unbind_cntr_arr[$ktt_key]
                                    ];
                                    if ((int)$b_unbind_cntr_arr[$ktt_key] == 1) {
                                        $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                        $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                        $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();
                                        if (!$kitExists) {
                                            $kit_id = DB::table('kit')->insertGetId([
                                                'id_flow' => $suzRequest->id_flow,
                                                'v_type' => $v_type,
                                                'id_kits' => $last_id_kits,
                                                'v_serial' => $kit_serial,
                                                'v_department' => $suzRequest->v_department,
                                                'id_status' => 2000,
                                                'dt_activate' => date('Y-m-d'),
                                                'owner_id' => Auth::user()->id
                                            ]);
                                            $kit = Kit::find($kit_id);
                                        } else {
                                            $kit = Kit::find($kitExists->id);
                                            $kit->owner_id = Auth::user()->id;
                                            $kit->save();
                                        }
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                            }
                        }
                        $service_info_arr[] = [
                            'id_rec' => $si_key,
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'id_service_technology' => $si->id_service_technology ?? '',
                            'id_service' => $si->id_service ?? '',
                            'v_param_internet' => $v_param_internet,
                            'kits_list' => $kits_list,
                            'return_kits_list' => $return_kits_list,
                            'service_info_add' => json_decode(json_encode($si->service_info_add), true)
                        ];
                    }
                }
                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => 'DONE_SUCCES',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'once_srvs_list' => [],
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'id_document_type' => $suzRequest->id_document_type != 0 ? (string)$suzRequest->id_document_type : null,
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers,
                    'additional_info_list' => $additional
                ];
                break;
            }
            case 'CloseFlowDissolution':
            {
                if ((isset($equipments_to_take) && count($equipments_to_take) > 0) || (isset($kits_to_take) && count($kits_to_take) > 0)) {
                    $v_result = 'DONE_WITH_EQ';
                    if (count($b_unbind_cntr_arr) == 0) {
                        return [
                            'success' => false,
                            'route' => '/requests/' . $request_id,
                            'message' => 'Нет информации о том, оставить ли оборудование на контракте',
                        ];
                    }
                } else {
                    $v_result = 'DONE_WITHOUT_EQ';
                }
                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);
                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $return_kits_list = [];
                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $equipment_id) {
                                $equipment = Equipment::find($equipment_id);
                                if ($b_unbind_cntr_arr[$si_key] == 1) {
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                }
                                $return_kits_list[] = [
                                    'v_type' => $equipment->getKitVTypeByModel($equipment->id_equipment_model),
                                    'v_serial' => $equipment->v_serial,
                                    'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                ];
                            }
                        }

                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $kit = Kit::where('v_serial', $kit_serial)->first();
                                    if ($kit) {
                                        $kit->owner_id = Auth::user()->id;
                                        $kit->save();
                                        // writing story record of this movement
                                        $from = $suzRequest->v_contract;
                                        $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                        $return_kits_list[] = [
                                            'v_type' => $suzRequest->getKitVTypeBySerial($kit_serial),
                                            'v_serial' => $kit_serial,
                                            'b_unbind_cntr' => $b_unbind_cntr_arr[$si_key]
                                        ];
                                    }
                                }
                            }
                        }
                        $service_info_arr[] = [
                            'id_service' => $si->id_service ?? '',
                            'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                            'v_param_internet' => $v_param_internet,
                            'return_kits_list' => $return_kits_list
                        ];
                    }
                }

                $params = [
                    'id_flow' => $suzRequest_copy->id_flow,
                    'v_result' => $v_result,
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr,
                    'once_srvs_list' => [],
                    'v_iin' => strlen($suzRequest->v_iin) == 12 ? $suzRequest->v_iin : '000000000000',
                    'v_document_number' => $suzRequest->v_document_number,
                    'dt_document_issue_date' => date(DATE_ATOM, strtotime($suzRequest->dt_document_issue_date)),
                    'v_document_series' => $suzRequest->v_document_series,
                    'dt_birthday' => date(DATE_ATOM, strtotime($suzRequest->dt_birthday)),
                    'v_other' => $installers
                ];
                if ($suzRequest->id_document_type != 0) {
                    $params['id_document_type'] = (string)$suzRequest->id_document_type;
                } else {
                    $params['id_document_type'] = null;
                }

                break;
            }
            case 'CloseFlowIntake':
            {
                $service_info_arr = [];
                $service_info = json_decode($suzRequest->service_info);
                foreach ($service_info as $si_key => $si) {
                    if ($si) {
                        $return_kits_list = [];
                        if (isset($equipments_to_take) && $equipments_to_take) {
                            foreach ($equipments_to_take as $equipment_id) {
                                if ($equipment_id) {
                                    $equipment = Equipment::find($equipment_id);
                                    if ($equipment) {
                                        DB::table('equipment')->where('id', $equipment_id)->update(['owner_id' => Auth::user()->id]);
                                        // writes story about this movement
                                        $from = $suzRequest->v_contract;
                                        $equipment->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                    }
                                    $return_kits_list[] = $equipment->id_equipment_inst;
                                }
                            }
                        }
                        if (isset($kits_to_take) && $kits_to_take) {
                            foreach ($kits_to_take as $ktt_key => $kit_serial) {
                                if ($ktt_key == $si_key) {
                                    $v_type = $suzRequest->getKitVTypeBySerial($kit_serial);
                                    $return_kits_list[] = $suzRequest->getIdEquipmentInstByVSerial($kit_serial);
                                    $last_id_kits_row = DB::table('kit')->select('id_kits')->latest()->first();
                                    $last_id_kits = (int)$last_id_kits_row->id_kits + 1;
                                    $kitExists = DB::table('kit')->select('id')->where('v_serial', $kit_serial)->first();

                                    if (!$kitExists) {
                                        $kit_id = DB::table('kit')->insertGetId([
                                            'id_flow' => $suzRequest->id_flow,
                                            'v_type' => $v_type,
                                            'id_kits' => $last_id_kits,
                                            'v_serial' => $kit_serial,
                                            'v_department' => $suzRequest->v_department,
                                            'id_status' => 2000,
                                            'dt_activate' => date('Y-m-d'),
                                            'owner_id' => Auth::user()->id
                                        ]);
                                        $kit = Kit::find($kit_id);
                                    } else {
                                        $kit = Kit::find($kitExists->id);
                                        $kit->owner_id = Auth::user()->id;
                                        $kit->save();
                                    }
                                    // writing story record of this movement
                                    $from = $suzRequest->v_contract;
                                    $kit->writeStory(Auth::user()->id, Auth::user()->id, $from, null, null, $suzRequest->id_flow);
                                }
                            }
                        }
                        if (count($return_kits_list) > 0) {
                            $service_info_arr[] = [
                                'id_service_inst' => (int)$si->id_service_inst != 0 ? (int)$si->id_service_inst : null,
                                'return_kits_list' => $return_kits_list
                            ];
                        }
                    }
                }
                $params = [
                    'id_flow' => (int)$suzRequest_copy->id_flow,
                    'v_result' => 'TAKED',
                    'dt_close' => date(DATE_ATOM, strtotime(date('Y-m-d'))),
                    'v_description' => $suzRequest_copy->comment, // n
                    'service_info' => $service_info_arr
                ];
                break;
            }
        }

        return [
            'success' => true,
            'params' => $params
        ];
    }
}
