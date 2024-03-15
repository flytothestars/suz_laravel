<?php

namespace App\Services\Mobile;

use App\Http\Traits\CatalogsTrait;
use Illuminate\Support\Facades\Auth;

class RouteListService
{
    use CatalogsTrait;

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
        // dd($requests);
        if ($request->status || $request->ci_flow) {
            $requests = collect($requests);
            if ($request->status) {
                $requests = $requests->filter(function ($item) use ($request) {
                    return $item->status_id == $request->status;
                })->values();
            }
            
            if ($request->ci_flow) {
                
                $requests = $requests->whereIn('id_ci_flow', $request->ci_flow)->values();
                
                // $idTypeWorks = $request->work_type;
                // $requests = $requests->filter(function ($item) use ($idTypeWorks) {
                //     $item->ltype_works_decoded = json_decode($item->ltype_works, true);
                //     foreach ($idTypeWorks as $key) {
                //         return isset($item->ltype_works_decoded['id_type_works']) && $item->ltype_works_decoded['id_type_works'] == $key;
                //     }
                // })->values();
            }
        }
        return compact(['routeList', 'requests']);
    }
}