<?php

namespace App\Services;

use App\Http\Requests\RouteList\StoreRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\Location;
use App\Models\RouteList;
use App\Models\SuzRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RouteListService
{
    use CatalogsTrait;

    public function index(Request $request): array
    {
        $time_intervals = range(strtotime("08:00"), strtotime("22:30"), 30 * 60);

        if (Auth::user()->hasRole('супервизер') && !Auth::user()->hasAnyRole(['администратор', 'диспетчер', 'просмотр маршрута'])) {
            $departments = Auth::user()->getDepartmentAttribute();
            $locations = Auth::user()->locations()->first();
            $department_id = $departments->id;
            $location_id = $locations->id_location;
            $date = isset($request->date) ? $request->date : date("Y-m-d");
            $department_code = $this->getDepartmentCodeById($department_id);
            $requests = SuzRequest::whereIn("status_id", [1, 2, 4, 6, 7])->where([
                ["v_department", $department_code],
                ["dt_plan_date", $date]
            ]);
            $compact = compact(['departments', 'locations', 'time_intervals']);
            $compact = $this->getRequestData($compact, $requests, $locations, $location_id, $date);
        } else {
            $departments = $this->getDepartments();
            $date = isset($request->date) ? $request->date : date("Y-m-d");
            $compact = compact(['departments', 'time_intervals']);

            if (isset($request->department_id) && isset($request->location_id)) {
                $department_id = $request->department_id;
                $department_code = $this->getDepartmentCodeById($department_id);
                $requests = SuzRequest::whereIn("status_id", [1, 2, 4, 6, 7])->where([
                    ["v_department", $department_code],
                    ["dt_plan_date", $date]
                ]);
                $location_id = $request->location_id;
                $location = Location::select('id')->where('id_location', $location_id)->first();
                $compact = $this->getRequestData($compact, $requests, $location, $location_id, $date);
            }

            if (isset($request->department_id)) {
                $locations = $this->getLocations($request->department_id);
                $compact = array_merge($compact, compact(['locations']));
            }
        }

        return $compact;
    }

    private function getRequestData($compact, $requests, $location, $location_id, $date): array
    {
        $requests = $requests->where('id_location', $location_id);
        $route_lists = RouteList::where('location_id', $location_id)->where('date', $date)->get();
        $compact = array_merge($compact, compact('route_lists'));
        $requests = $requests->get();

        foreach ($requests as &$req) {
            $req->street = $this->getStreet($req->id_street);
            $req->house = $this->getHouse($req->id_house);
            $req->ci_flow = $this->getCiFlow($req->id_ci_flow);
            $req->status = $this->getStatusById($req->status_id)->name;
            $req->coordinates = $req->getCoordinates();
            $req->basic_coordinates = $req->getBasicCoordinates();
            $req->sector = $this->getSector($req->id_sector);
        }

        $installers = User::role('техник')->notBusy($date)->currentLocation($location->id)->get();
        $specialization = DB::table('specialization')->select('id_spec', 'v_name')->get();

        return array_merge($compact, compact(['requests', 'installers', 'specialization']));
    }

    public function indexInstaller($request): array
    {
        $date = isset($request->date) && $request->date != '' ? $request->date : date('Y-m-d');
        $user = Auth::user() ?? Auth::guard('api')->user();

        $routeList = $user->routeList($date);
        $requests = collect();

        if ($routeList) {
            $requests_ = $routeList->requests()->get(['id', 'status_id', 'id_flow', 'id_ci_flow', 'id_kind_works', 'dt_plan_date', 'n_plan_time', 'ltype_works', 'id_street', 'id_house', 'v_flat', 'service_info', 'dt_start', 'v_contract', 'v_client_title']);
            foreach ($requests_ as $req) {
                if ($req->dt_plan_date != $date) {
                    continue;
                } else {
                    $req->status = $this->getStatusById($req->status_id)->name;
                    $req->ci_flow = $this->getCiFlow($req->id_ci_flow);
                    $req->kind_works = $this->getKindWorksById($req->id_kind_works);
                    $req->type_works = '-';
                    $req->dt_plan_date = setPlanDate($req);

                    if ($req->ltype_works != '{}') {
                        $id_type_works_json = json_decode($req->ltype_works);
                        $req->type_works = $this->getTypeWorksById($id_type_works_json->id_type_works);
                    }

                    $req->address = $this->getStreet($req->id_street) . ", " . $this->getHouse($req->id_house) . ", кв. " . $req->v_flat;
                    $services = [];

                    foreach (json_decode($req->service_info) as $service_info) {
                        $service = $this->getService($service_info->id_service);
                        if (isset($services[$service])) {
                            $services[$service]++;
                        } else {
                            $services[$service] = 1;
                        }
                    }

                    $services_text = "";

                    foreach ($services as $key => $serv) {
                        $services_text .= '<div class="h5 m-0 text-primary">' . $key . ": " . $serv . '</div>';
                    }

                    $req->services = $services_text;
                    $requests->push($req);
                }
                $requests = $requests->sortBy('dt_plan_date');
            }
        }

        $requests = $requests->values()->all();

        if ($request->status || $request->work_type) {
            $requests = collect($requests);
            if ($request->status) {
                $requests = $requests->filter(function ($item) use ($request) {
                    return $item->status_id == $request->status;
                });
            }

            if ($request->work_type) {
                $idTypeWorks = $request->work_type;

                $requests = $requests->filter(function ($item) use ($idTypeWorks) {
                    $item->ltype_works_decoded = json_decode($item->ltype_works, true);
                    return isset($item->ltype_works_decoded['id_type_works']) && $item->ltype_works_decoded['id_type_works'] == $idTypeWorks;
                });
            }
        }

        return compact(['routeList', 'requests']);
    }

    /**
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        if (isset($request->location_id)) {
            $installer_1 = (int)$validated['installer_1'];
            $installer_2 = (int)$validated['installer_2'];

            if ($installer_1 === 0 && $installer_2 === 0) {
                throw ValidationException::withMessages([
                    'message' => ['Нужен хотя бы один монтажник']
                ]);
            }

            $validInstallers = [];

            if ($installer_1 !== 0) {
                $validInstallers[] = $installer_1;
            }

            if ($installer_2 !== 0) {
                $validInstallers[] = $installer_2;
            }

            $existingInstallers = User::whereIn('id', $validInstallers)->pluck('id');

            if ($existingInstallers->count() !== count($validInstallers)) {
                throw ValidationException::withMessages([
                    'message' => ['Выбранный монтажник не найден в базе']
                ]);
            }

            $routeList = new RouteList;
            $routeList->location_id = $validated['location_id'];
            $routeList->created_at = now();
            $routeList->date = $validated['date'];
            $routeList->save();

            if ($validated['specialization'] !== null) {
                $specializations = [];
                foreach ($validated['specialization'] as $spec) {
                    $specializations[] = [
                        'id_spec' => $spec,
                        'routelist_id' => $routeList->id,
                        'created_at' => now()
                    ];
                }
                DB::table("spec_routelist")->insert($specializations);
            } else {
                throw ValidationException::withMessages([
                    'message' => ['Выберите специализацию']
                ]);
            }

            DB::table("installer_route_list")->insert([
                'installer_1' => $installer_1,
                'installer_2' => $installer_2,
                'routelist_id' => $routeList->id,
                'created_at' => now()
            ]);
        }
    }
}
