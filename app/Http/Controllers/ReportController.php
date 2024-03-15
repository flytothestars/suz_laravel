<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Exports\RequestsExport;
use App\Http\Traits\CatalogsTrait;
use App\Models\Material;
use App\Models\Stock;
use App\Services\GetLocationUser;
use App\Transformers\ReportCardUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    use CatalogsTrait;

    public function index(Request $request)
    {
        $rows = null;
        $stocks = [];

        if (in_array(auth()->user()->id, [60, 153]) || Auth::user()->hasRole('администратор')) {
            $departments = $this->getDepartments()->sortBy('v_name');
            if (isset($request->department) && $request->department != 'ALL') {
                $department_id = DB::table('fw_departments')->where('v_ext_ident', $request->department)->first();
                $stocks = Stock::where('department_id', $department_id->id_department)->get();
                $stocks = $stocks->map->only('id', 'name', 'department_id');
            }
        } else {
            $departments = DB::table('fw_departments')->where('id_department', Auth::user()->department_id)->first();
            $stocks = Stock::where('department_id', $departments->id_department)->get();
            $stocks = $stocks->map->only('id', 'name', 'department_id');
        }
        return view('report.index', compact(['rows', 'departments', 'stocks']));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $stock = null;
        $department = null;
        if (isset($request->stock)) {
            $stock = $request->stock;
        }
        if (isset($request->department)) {
            $department = $request->department;
        }
        if (isset($request->department) && $request->department == 'ALL') //TODO Хардкод
        {
            $department = null;
        }
        if ($request->type == 1 || $request->type == 2) {
            $rows = $this->getRows($request->type, $request->date_from, $request->date_to, $department);
        } elseif ($request->type == 3) {
            $rows = $this->getConsolidated1($request->date_from, $request->date_to, $stock);
        } elseif ($request->type == 4) {
            $rows = $this->getConsolidated2($request->date_from, $request->date_to, $stock);
        } elseif ($request->type == 5) {
            $rows = $this->getBalance1($stock);
        } elseif ($request->type == 6) {
            $rows = $this->getBalance2($stock);
        }
        $export = new ReportExport($rows, $request->type);
        return Excel::download($export, 'report.xlsx');
    }

    public function exportRequests(Request $request): BinaryFileResponse
    {
        $department = null;
        $type = null;
        if (isset($request->department)) {
            $department = $request->department;
        }
        if (isset($request->type)) {
            $type = $request->type;
        }
        $requests = $this->getRequests($request->date_from, $request->date_to, $department, $type);
        $export = new RequestsExport($requests);
        return Excel::download($export, 'report.xlsx');
    }

    public function getRequests($date_from, $date_to, $department, $type): array
    {
        $requests = DB::table('suz_requests')
            ->join('ci_flow', 'ci_flow.id_ci_flow', '=', 'suz_requests.id_ci_flow')
            ->join('fw_departments', 'fw_departments.v_ext_ident', '=', 'suz_requests.v_department')
            ->join('alma_location', 'alma_location.id_location', '=', 'suz_requests.id_location')
            ->join('fw_town', 'fw_town.id_town', '=', 'suz_requests.id_town')
            ->join('statuses', 'statuses.id', '=', 'suz_requests.status_id')
            ->leftJoin('alma_sector', 'alma_sector.id_sector', '=', 'suz_requests.id_sector')
            ->join('fw_product', 'fw_product.id_product', '=', 'suz_requests.id_product')
            ->join('fw_street', 'fw_street.id_street', '=', 'suz_requests.id_street')
            ->leftJoin('fw_address_ligament', 'fw_address_ligament.id_house', '=', 'suz_requests.id_house')
            ->leftJoin('materials_story', 'materials_story.id_flow', '=', 'suz_requests.id_flow')
            ->leftJoin('materials', 'materials.id', '=', 'materials_story.material_id')
            ->leftJoin('users', 'users.id', '=', 'materials_story.author_id')
            ->leftJoin('alma_kind_works', 'alma_kind_works.id_kind_work_inst', '=', 'suz_requests.id_kind_works')
            ->leftJoin('request_route_list', 'request_route_list.request_id', '=', 'suz_requests.id')
            ->leftJoin('installer_route_list', 'installer_route_list.routelist_id', '=', 'request_route_list.routelist_id')
            ->leftJoin('users as installer1', 'installer1.id', '=', 'installer_route_list.installer_1')
            ->leftJoin('users as installer2', 'installer2.id', '=', 'installer_route_list.installer_2')
            ->leftJoin('suz_request_story', function ($join) {
                $join->on('suz_request_story.request_id', '=', 'suz_requests.id')
                    ->whereNotNull('suz_request_story.comment')
                    ->orderByDesc('suz_request_story.id')
                    ->limit(1);
            })
            ->leftJoin('users as dispatcher', 'dispatcher.id', '=', 'suz_request_story.dispatcher_id')
            ->leftJoin('request_repair_type', 'request_repair_type.request_id', '=', 'suz_requests.id')
            ->leftJoin('repair_type', 'repair_type.id_type', '=', 'request_repair_type.id_type')
            ->leftJoin('alma_type_works', function ($join) {
                $join->on('alma_type_works.id_type_work', '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(suz_requests.ltype_works, '$.id_type_works'))"))
                    ->whereNotNull('suz_requests.ltype_works');
            })
            ->select(
                'suz_requests.id as id',
                'suz_requests.id_flow as id_flow',
                'ci_flow.v_name as ci_flow',
                'suz_requests.dt_start as dt_start',
                'suz_requests.dt_stop as dt_stop',
                'suz_requests.v_contract as contract',
                'fw_departments.v_name as department',
                'alma_location.v_name as location',
                'suz_requests.ltype_works as work_type',
                'suz_requests.v_flow_descr as description',
                DB::raw("CASE
            WHEN CHAR_LENGTH(fw_address_ligament.house_nm) THEN CONCAT(fw_town.v_name, ', ', fw_street.v_name, ' ', fw_address_ligament.house_nm, ', ', suz_requests.v_flat)
            WHEN fw_address_ligament.house_nm IS NULL THEN CONCAT(fw_town.v_name, ', ', fw_street.v_name)
        END as address"),
                'statuses.name as status',
                DB::raw("CASE
            WHEN CHAR_LENGTH(alma_sector.v_name) > 0 THEN alma_sector.v_name
            WHEN alma_sector.v_name IS NULL THEN '-'
        END as sector"),
                'fw_product.v_name as product',
                'suz_requests.id_kind_works as id_kind_works',
                'suz_requests.dt_plan_date as dt_plan_date',
                'materials.name as material_name',
                'materials_story.qty as qty',
                'installer1.name as installer1',
                'installer2.name as installer2',
                'alma_kind_works.v_name as kind_works',
                'suz_request_story.comment as comment',
                'dispatcher.name as completing',
                DB::raw("GROUP_CONCAT(DISTINCT repair_type.v_name SEPARATOR '; ') as repair_types"),
                DB::raw("GROUP_CONCAT(DISTINCT alma_type_works.v_name SEPARATOR '; ') as ltypework"),
                DB::raw("(SELECT GROUP_CONCAT(DISTINCT fw_service.v_name SEPARATOR '; ') FROM fw_service WHERE JSON_SEARCH(suz_requests.service_info, 'one', CAST(fw_service.id_service AS CHAR)) IS NOT NULL) as service_name"),
                DB::raw("(SELECT GROUP_CONCAT(DISTINCT alma_technology_type.v_name SEPARATOR '; ') FROM alma_technology_type WHERE JSON_SEARCH(suz_requests.service_info, 'one', CAST(alma_technology_type.id_technology AS CHAR)) IS NOT NULL) as technology_name")
            )
            ->where('suz_requests.id_ci_flow', $type)
            ->whereBetween('suz_requests.dt_start', [$date_from, $date_to])
            ->where('suz_requests.v_department', $department)
            ->whereNotNull('suz_requests.routelist_id')
            ->groupBy(['suz_requests.id'])
            ->get();

        return $requests->toArray();

    }

    public function arrayPaginator($array, $request): LengthAwarePaginator
    {
        $page = Input::get('page', 1);
        $perPage = 5;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }

    public function getRows($type, $date_from, $date_to, $department): array
    {
        $date_from .= " 00:00:00";
        $date_to .= " 23:59:59";
        if ($type == 1) {
            $sql = "SELECT
					fw_departments.v_name as department,
					fw_town.v_name as town,
					CONCAT(fw_street.v_name, ', ', fw_address_ligament.house_nm) as address,
					-- stocks.name as stock,
					al.v_name as location,
					fw_product.v_name as work_type,
					CASE
					    WHEN CHAR_LENGTH(es.owner_id) < 5 THEN es.from
					    WHEN CHAR_LENGTH(es.owner_id) = 9 THEN es.owner_id
					END AS contract,
					sr.id_flow,
					es.created_at ,
					u.name as installer,
					CASE
					    WHEN CHAR_LENGTH(es.from) < 5 THEN 'Монтаж'
					    WHEN CHAR_LENGTH(es.from) = 9 THEN 'Демонтаж'
					    ELSE 'Неизвестно'
					END AS type_flow,
					cf.v_name as type,
					aem.v_name as equipment_name,
					e.v_equipment_number
					FROM equipment_story es
							INNER JOIN suz_requests sr on
							(CASE
								WHEN CHAR_LENGTH(es.owner_id) = 9 THEN es.owner_id
								WHEN CHAR_LENGTH(es.owner_id) < 5 AND CHAR_LENGTH(es.from) = 9 THEN es.from
							END) = sr.v_contract
							INNER JOIN equipment e on e.kit_id = es.equipment_id
							INNER JOIN kit k on k.id = es.equipment_id
							INNER JOIN ci_flow cf on cf.id_ci_flow = sr.id_ci_flow
							INNER JOIN alma_location al on al.id_location = sr.id_location
							INNER JOIN fw_departments on fw_departments.v_ext_ident = sr.v_department
							INNER JOIN fw_town on fw_town.id_town = sr.id_town
							INNER JOIN users u on u.id = es.author_id
							inner join fw_street on fw_street.id_street = sr.id_street
							inner join fw_address_ligament on fw_address_ligament.id_house = sr.id_house
							inner join fw_product on fw_product.id_product = sr.id_product
							inner join alma_equipment_model aem on aem.id_equipment_model = e.id_equipment_model
							WHERE sr.status_id = 5
							and es.created_at BETWEEN '$date_from' AND '$date_to'
							and sr.dt_stop = es.created_at
							and es.is_kit = 1";
        } elseif ($type == 2) {
            $sql = "SELECT DISTINCT
					fw_departments.v_name as department,
					fw_town.v_name as town,
					CASE
						WHEN CHAR_LENGTH(fw_address_ligament.id_house > 0) THEN CONCAT(fw_street.v_name, ', ', fw_address_ligament.house_nm)
						WHEN fw_address_ligament.id_house IS NULL THEN fw_street.v_name
					END as address,
					al.v_name as location,
					fw_product.v_name as work_type,
					CASE
					    WHEN CHAR_LENGTH(ms.owner_id) < 5 THEN ms.from
					    WHEN CHAR_LENGTH(ms.owner_id) = 9 THEN ms.owner_id
					END AS contract,
					sr.id_flow,
					ms.created_at,
					u.name as installer,
					CASE
					    WHEN CHAR_LENGTH(ms.from) < 5 THEN 'Монтаж'
					    WHEN CHAR_LENGTH(ms.from) = 9 THEN 'Демонтаж'
					    ELSE 'Неизвестно'
					END AS type_flow,
					cf.v_name as type,
					m.name as material_name,
					ms.qty
					FROM materials_story ms
							INNER JOIN suz_requests sr on
							(CASE
								WHEN CHAR_LENGTH(ms.owner_id) = 9 THEN ms.owner_id
								WHEN CHAR_LENGTH(ms.owner_id) < 5 AND CHAR_LENGTH(ms.from) = 9 THEN ms.from
							END) = sr.v_contract
							INNER JOIN materials m on m.id = ms.material_id
							INNER JOIN ci_flow cf on cf.id_ci_flow = sr.id_ci_flow
							INNER JOIN fw_departments on fw_departments.v_ext_ident = sr.v_department
							INNER JOIN fw_town on fw_town.id_town = sr.id_town
							INNER JOIN alma_location al on al.id_location = sr.id_location
							INNER JOIN users u on u.id = ms.author_id
							inner join fw_street on fw_street.id_street = sr.id_street
							left join fw_address_ligament on fw_address_ligament.id_house = sr.id_house
							inner join fw_product on fw_product.id_product = sr.id_product
							WHERE sr.status_id > 1
							and ms.created_at between '$date_from' AND '$date_to'
							AND sr.id_flow = ms.id_flow";
        }
        if ($department) {
            $sql .= " and sr.v_department = '$department'";
        }
        return DB::select($sql);
    }

    public function getConsolidated1($date_from, $date_to, $stock): array
    {
        $date_from .= " 00:00:00";
        $date_to .= " 23:59:59";
        $models_sql = "SELECT DISTINCT eq.id_equipment_model FROM equipment_story es
				   INNER JOIN equipment eq on eq.kit_id = es.equipment_id
				   and es.created_at BETWEEN '$date_from' AND '$date_to'";
        $models = DB::select($models_sql);
        $rows = [];
        foreach ($models as $model) {
            $model = $model->id_equipment_model;
            $name_sql = "SELECT aem.v_name FROM alma_equipment_model aem WHERE id_equipment_model = $model";
            $balance_at_the_start_sql = "SELECT COUNT(eq.id_equipment_model) as balance_at_the_start
										FROM equipment eq
										WHERE eq.owner_id IS NULL
										and eq.id_equipment_model = $model
									   	and eq.created_at = '$date_from'";
            $upcoming_sql = "SELECT COUNT('es.id') as upcoming FROM equipment_story es
										INNER JOIN equipment e on e.kit_id = es.equipment_id
										WHERE es.from = 'move_equipment'
										and e.id_equipment_model = $model
									   	and es.created_at BETWEEN '$date_from' AND '$date_to'";
            if ($stock) {
                $upcoming_sql .= " and e.stock_id = $stock";
            }
            $dismantling_sql = "SELECT COUNT(eq.returned) as dismantling
										FROM equipment eq
										INNER JOIN suz_requests sr on sr.v_contract = eq.owner_id
										WHERE eq.id_equipment_model = $model
										and sr.status_id = 5
									   	and sr.dt_plan_date BETWEEN '$date_from' AND '$date_to'";
            if ($stock) {
                $dismantling_sql .= " and eq.stock_id = $stock";
            }
            $repair_sql = "SELECT COUNT('es.id') as repair FROM equipment_story es
								INNER JOIN equipment e on e.kit_id = es.equipment_id
								INNER JOIN suz_requests sr on sr.v_contract = es.owner_id
								INNER JOIN alma_equipment_model aem on aem.id_equipment_model = e.id_equipment_model
								WHERE sr.dt_stop = es.created_at
								and sr.id_ci_flow = 2404
								and sr.status_id = 5
								and CHAR_LENGTH(es.owner_id) = 9
								and sr.dt_plan_date BETWEEN '$date_from' AND '$date_to'
								and e.id_equipment_model = $model";
            if ($stock) {
                $repair_sql .= " and e.stock_id = $stock";
            }
            $repair_gpon_sql = $repair_sql . " and (sr.service_info LIKE '%id_service_technology\":2007%' OR sr.service_info LIKE '%id_service_technology\":2140%')";
            if ($stock) {
                $repair_gpon_sql .= " and e.stock_id = $stock";
            }
            $repair_pd_sql = $repair_sql . " and (sr.service_info LIKE '%id_service_technology\":2005%' OR sr.service_info LIKE '%id_service_technology\":2006%' OR
												sr.service_info LIKE '%id_service_technology\":2007%' OR sr.service_info LIKE '%id_service_technology\":2260%' OR
											    sr.service_info LIKE '%id_service_technology\":2080%' OR sr.service_info LIKE '%id_service_technology\":2060%' OR
											    sr.service_info LIKE '%id_service_technology\":2163%' OR sr.service_info LIKE '%id_service_technology\":2100%')";
            if ($stock) {
                $repair_pd_sql .= " and e.stock_id = $stock";
            }
            $installs_sql = "SELECT COUNT(es.equipment_id) as installs FROM equipment_story es
								INNER JOIN suz_requests sr on sr.v_contract = es.owner_id
								INNER JOIN equipment e on e.kit_id = es.equipment_id
								WHERE sr.dt_stop = es.created_at
								and sr.status_id = 5
								and CHAR_LENGTH(es.owner_id) = 9
								and sr.dt_plan_date BETWEEN '$date_from' AND '$date_to'
								and e.id_equipment_model = $model";
            if ($stock) {
                $installs_sql .= " and e.stock_id = $stock";
            }
            $rent_sql = "SELECT COUNT(es.equipment_id) as rent FROM equipment_story es
							INNER JOIN equipment e on e.kit_id = es.equipment_id
							WHERE es.v_kits_transfer = 'R'
							AND e.id_equipment_model = $model
							AND es.created_at BETWEEN '$date_from' AND '$date_to'";
            if ($stock) {
                $rent_sql .= " and e.stock_id = $stock";
            }
            $sold_sql = "SELECT COUNT(es.equipment_id) as sold FROM equipment_story es
							INNER JOIN equipment e on e.kit_id = es.equipment_id
							WHERE es.v_kits_transfer = 'S'
							AND e.id_equipment_model = $model
							AND es.created_at BETWEEN '$date_from' AND '$date_to'";
            if ($stock) {
                $sold_sql .= " and e.stock_id = $stock";
            }
            $returns_sql = "SELECT COUNT(id) as returns FROM equipment WHERE returned = 1
							AND stock_id IS NOT NULL
							AND id_equipment_model = $model
							AND updated_at BETWEEN '$date_from' AND '$date_to'";
            if ($stock) {
                $returns_sql .= " and stock_id = $stock";
            }
            $balance_at_the_end_sql = "SELECT COUNT(id_equipment_model) as balance_at_the_end FROM equipment eq
							WHERE owner_id IS NULL
							and stock_id IS NOT NULL
							and id_equipment_model = $model
							and updated_at = '$date_to'";
            if ($stock) {
                $balance_at_the_end_sql .= " and eq.stock_id = $stock";
            }
            $collect = new Collection;
            $collect->name = DB::select($name_sql)[0]->v_name;
            $collect->balance_at_the_start = DB::select($balance_at_the_start_sql)[0]->balance_at_the_start;
            $collect->upcoming = DB::select($upcoming_sql)[0]->upcoming;
            $collect->dismantling = DB::select($dismantling_sql)[0]->dismantling;
            $collect->upcoming_total = $collect->upcoming + $collect->dismantling;
            $collect->repair = DB::select($repair_sql)[0]->repair;
            $collect->repair_gpon = DB::select($repair_gpon_sql)[0]->repair;
            $collect->repair_pd = DB::select($repair_pd_sql)[0]->repair;
            $collect->installs = DB::select($installs_sql)[0]->installs;
            $collect->rent = DB::select($rent_sql)[0]->rent;
            $collect->sold = DB::select($sold_sql)[0]->sold;
            $collect->returns = DB::select($returns_sql)[0]->returns;
            $collect->outgo_total = $collect->repair + $collect->repair_gpon + $collect->repair_pd;
            $collect->balance_at_the_end = DB::select($balance_at_the_end_sql)[0]->balance_at_the_end;

            $rows[] = $collect;
        }
        return $rows;
    }

    public function getConsolidated2($date_from, $date_to, $stock): array
    {
        $rows = [];
        $materials = Material::all();
        foreach ($materials as $material) {
            $balance_at_the_start_sql = "SELECT SUM(qty) as balance_at_the_start FROM stock_material
										WHERE (created_at = '$date_from' OR updated_at = '$date_from')
										AND material_id = " . $material->id;
            $incoming_sql = "SELECT SUM(qty) as incoming FROM materials_story
							WHERE created_at BETWEEN '$date_from' AND '$date_to'
							AND incoming = 1
							AND material_id = " . $material->id;
            $dismantling_sql = "SELECT SUM(qty) as dismantling FROM materials_story
							WHERE created_at BETWEEN '$date_from' AND '$date_to'
							AND returned = 1
							AND material_id = " . $material->id;
            $repair_sql = "SELECT SUM(ms.qty) as repair FROM materials_story ms
							INNER JOIN suz_requests sr ON sr.id = ms.request_id
							WHERE sr.dt_stop BETWEEN '$date_from' AND '$date_to'
							AND sr.id_ci_flow = 2404
							AND sr.status_id = 5
							AND ms.material_id = " . $material->id;
            $repair_gpon_sql = $repair_sql . " and (sr.service_info LIKE '%id_service_technology\":2007%' OR sr.service_info LIKE '%id_service_technology\":2140%')";
            $repair_pd_sql = $repair_sql . " and (sr.service_info LIKE '%id_service_technology\":2005%' OR sr.service_info LIKE '%id_service_technology\":2006%' OR
												sr.service_info LIKE '%id_service_technology\":2007%' OR sr.service_info LIKE '%id_service_technology\":2260%' OR
											    sr.service_info LIKE '%id_service_technology\":2080%' OR sr.service_info LIKE '%id_service_technology\":2060%' OR
											    sr.service_info LIKE '%id_service_technology\":2163%' OR sr.service_info LIKE '%id_service_technology\":2100%')";
            $installs_sql = "SELECT SUM(ms.qty) as installs FROM materials_story ms
							INNER JOIN suz_requests sr ON sr.id = ms.request_id
							WHERE sr.dt_stop BETWEEN '$date_from' AND '$date_to'
							AND (ms.returned = 0 OR ms.returned IS NULL)
							AND (ms.incoming = 0 OR ms.incoming IS NULL)
							AND CHAR_LENGTH(ms.owner_id) = 9
							AND sr.status_id = 5
							AND sr.id_kind_works IN (2160, 2261, 2264, 2100, 2020, 2180, 2024)
							AND ms.material_id = " . $material->id;
            $balance_at_the_end_sql = "SELECT SUM(qty) as balance_at_the_end FROM stock_material
										WHERE created_at = '$date_to'
										AND material_id = " . $material->id;
            $collect = new Collection;
            $collect->name = $material->name;
            $collect->balance_at_the_start = DB::select($balance_at_the_start_sql)[0]->balance_at_the_start;
            $collect->incoming = DB::select($incoming_sql)[0]->incoming;
            $collect->dismantling = DB::select($dismantling_sql)[0]->dismantling;
            $collect->incoming_total = $collect->incoming + $collect->dismantling;
            $collect->repair = DB::select($repair_sql)[0]->repair;
            $collect->repair_gpon = DB::select($repair_gpon_sql)[0]->repair;
            $collect->repair_pd = DB::select($repair_pd_sql)[0]->repair;
            $collect->installs = DB::select($installs_sql)[0]->installs;
            $collect->outgo_total = $collect->repair + $collect->repair_gpon + $collect->repair_pd + $collect->installs;
            $collect->balance_at_the_end = DB::select($balance_at_the_end_sql)[0]->balance_at_the_end;

            $rows[] = $collect;
        }
        return $rows;
    }

    public function getBalance1($stock): array
    {
        $models_sql = "SELECT DISTINCT id_equipment_model FROM equipment";
        $models = DB::select($models_sql);
        $rows = [];
        foreach ($models as $model) {
            $model = $model->id_equipment_model;
            $name_sql = "SELECT aem.v_name FROM alma_equipment_model aem WHERE id_equipment_model = $model";
            $balance_at_the_start_sql = "SELECT COUNT(eq.id_equipment_model) as balance_at_the_start
										FROM equipment eq
										WHERE eq.owner_id IS NULL
										and eq.id_equipment_model = $model";
            if ($stock) {
                $balance_at_the_start_sql .= " and eq.stock_id = $stock";
            }
            $collect = new Collection;
            $collect->name = DB::select($name_sql)[0]->v_name;
            $collect->balance_at_the_start = DB::select($balance_at_the_start_sql)[0]->balance_at_the_start;
            $rows[] = $collect;
        }
        return $rows;
    }

    public function requestsIndex(Request $request)
    {
        $departments = $this->getDepartments();
        $types = $this->getCiFlows();
        return view('report.requests', compact(['departments', 'types']));
    }

    public function reportCard(Request $request, GetLocationUser $service): JsonResponse
    {
        if (!$request->has('location_id')) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Не передан location_id'
            ]);
        }

        $resource = new \League\Fractal\Resource\Collection($service->execute((int)$request->get('location_id')), new ReportCardUser);

        return response()->json([
            'status' => 'ok',
            'data' => $resource,
        ]);
    }
}
