<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RouteList extends Model
{
    /*
    условие из routing.index было заменено на getRouteListRequest()
    @if(!$route_list->requests()->get()->toArray())
    */
    public function requests()
    {
        return $this->hasMany('App\Models\SuzRequest', 'routelist_id');
    }

    public function installers()
    {
        $rows = DB::table("installer_route_list")->select("installer_1", "installer_2")
            ->where("routelist_id", $this->id)->get()->toArray();
        $installers = array();
        if($rows)
        {
            $ids = array();
            foreach($rows[0] as $r)
            {
                if($r)
                {
                    $ids[] = $r;
                }
            }
            $installers = User::whereIn("id", $ids)->get();
        }
        return $installers;
    }

    public function getRequestsByDatetime($date_time)
    {
        $rows = DB::table("request_route_list")->where([
            ["routelist_id", $this->id],
            ["time", $date_time]
        ])->get(['request_id'])->pluck('request_id')->toArray();
        $requests = null;
        if($rows)
        {
            $requests = SuzRequest::whereIn('id', $rows)->get(['id', 'status_id', 'id_ci_flow']);
        }
        return $requests;
    }

    public function getSpec()
    {
        $rows = DB::table("spec_routelist")->where("routelist_id", $this->id)->pluck('id_spec')->toArray();
        $specs = array();
        if($rows)
        {
            $specs = DB::table('specialization')->whereIn("id_spec", $rows)->get();
        }
        return $specs;
    }

    public function getSpecCiFlow()
    {
        $rows = DB::table("spec_routelist")->where("routelist_id", $this->id)->pluck('id_spec')->toArray();
        $specs = array();
        if($rows)
        {
            $specs = DB::table("spec_ci_flow")->whereIn("id_spec", $rows)->pluck('id_ci_flow');
            return $specs;
        }
        else
        {
            return false;
        }
    }

    public function getRouteListRequest()
    {
        $row = DB::table("request_route_list")->where("routelist_id", $this->id)->first();
        return $row;
    }
}
