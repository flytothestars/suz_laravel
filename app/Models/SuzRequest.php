<?php

namespace App\Models;

use App\Http\Traits\CatalogsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SuzRequest extends Model
{
    use CatalogsTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suz_requests';

    protected $fillable = [
        'id_flow',
        'id_ci_flow',
        'dt_flow_dt_event',
        'v_department',
        'v_contract',
        'id_location',
        'id_sector',
        'id_region',
        'id_district',
        'id_town',
        'b_structured_address',
        'id_street',
        'id_house',
        'v_flat',
        'v_unstr_street',
        'v_unstr_house',
        'v_client_title',
        'v_client_cell_phone',
        'v_client_landline_phone',
        'id_kind_works',
        'ltype_works',
        'v_flow_descr',
        'v_flow_time_descr',
        'dt_plan_date',
        'n_plan_time',
        'id_product',
        'id_tplan',
        'v_client_switch_port',
        'v_client_switch_mac',
        'v_iin',
        'id_document_type',
        'v_document_number',
        'dt_document_issue_date',
        'v_document_series',
        'dt_birthday',
        'service_info',
        'status_id',
        'dt_start',
        'dt_stop',
        'routelist_id',
        'comment',
        'comment_author',
        'id_reason',
        'cancel_reason_id',
        'request_id',
        'dispatcher_id',
    ];


    public $timestamps = false;

    /**
     * The users that belong to the request.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'users');
    }

    /**
     * Get the route list that owns the request.
     */
    public function routeList(): BelongsTo
    {
        return $this->belongsTo('App\Models\RouteList', 'routelist_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany('App\Models\Equipment', 'owner_id', 'v_contract');
    }

    public function getEquipments()
    {
        $equipments = collect();
        $service_info = json_decode($this->service_info);
        foreach ($service_info as $si) {
            if (!empty($si->equipment_list)) {
                $equipment_list = $si->equipment_list;
                foreach ($equipment_list as $eq) {
                    $equipments->push($eq);
                }
            }
        }
        return $equipments;
    }

    public function getKits($service_key)
    {
        $kits = collect();
        $kits_serials = array();
        $service_info = json_decode($this->service_info);
        foreach ($service_info as $key => $si) {
            if ($key == $service_key) {
                if (!empty($si->equipment_list)) {
                    $equipment_list = $si->equipment_list;
                    foreach ($equipment_list as $eq) {
                        $v_type = $this->getKitVTypeByModel($eq->id_equipment_model);
                        $kit = collect();
                        $kit->id = '';
                        $kit->v_type = $v_type;
                        $kit->v_serial = $eq->v_serial;
                        $kit->equipment_list = $this->getKitEquipments($equipment_list, $eq->v_serial);
                        if (!in_array($eq->v_serial, $kits_serials)) {
                            $kits_serials[] = $eq->v_serial;
                            $kits->push($kit);
                        }
                    }
                }
            }
        }
        return $kits;
    }

    private function getKitEquipments($equipment_list, $v_serial)
    {
        $equipments = collect();
        foreach ($equipment_list as $eq) {
            if ($eq->v_serial == $v_serial) {
                $eq->model = $this->getEquipmentModel($eq->id_equipment_model);
                $equipments->push($eq);
            }
        }
        return $equipments;
    }

    /**
     * Gets request installers
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection|null
     */
    public function getRequestInstallers()
    {
        $installers_id = DB::table("request_route_list")
            ->join('installer_route_list', 'installer_route_list.routelist_id',
                '=', 'request_route_list.routelist_id')
            ->select("installer_route_list.installer_1", "installer_route_list.installer_2")
            ->where("request_route_list.request_id", $this->id)->get()->toArray();
        $installers = null;
        if ($installers_id) {
            $installers = collect();
            if (isset($installers_id[0]->installer_1) && $installers_id[0]->installer_1) {
                $installers->push(User::find($installers_id[0]->installer_1));
            }
            if (isset($installers_id[0]->installer_2) && $installers_id[0]->installer_2) {
                $installers->push(User::find($installers_id[0]->installer_2));
            }
        }
        return $installers;
    }

    /**
     * Gets date and time of last request story
     * @return mixed|null $date Date and time
     */
    public function getLastDate()
    {
        $row = DB::table("suz_request_story")->select("date")->where("request_id", $this->id)->
        orderBy('id', 'desc')->first();
        return $row->date ?? null;
    }

    /**
     * Gets dt_start of last request story
     * @return mixed|null $dt_start Start Date
     */
    public function getLastDtStart()
    {
        $row = DB::table("suz_request_story")->select("dt_start")->where("request_id", $this->id)->
        orderBy('id', 'desc')->first();
        return $row->dt_start ?? null;
    }

    /**
     * Gets the reason of last request story
     * @return mixed|null $reason Last reason
     */
    public function getLastReason()
    {
        $row = DB::table("suz_request_story")->select("reason")->where("request_id", $this->id)->
        orderBy('id', 'desc')->first();
        return $row->reason ?? null;
    }

    /**
     * Gets dispatcher of request
     * @return User $user|null $dispatcher
     */
    public function getDispatcher()
    {
        $row = DB::table("suz_request_dispatcher")->select("dispatcher_id")->where("request_id",
            $this->id)->first();
        $dispatcher = $row ? User::find($row->dispatcher_id) : null;
        return $dispatcher;
    }

    /**
     * Gets request cancel reason from story table
     * @return SuzRequest|Model|object|null
     */
    public function getRequestCancelReason()
    {
        $row = DB::table("suz_request_story")->select("cancel_reason_id")->where("request_id",
            $this->id)->orderBy("id", "desc")->first();
        $reason = null;
        if ($row) {
            $reason = $this->getReasonById($row->cancel_reason_id);
        }
        return $reason;
    }

    /**
     * Gets request status
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getStatus()
    {
        $row = DB::table("statuses")->where("id", $this->status_id)->first();
        if ($row) {
            $row->class = getStatusClass($row->name);
        }
        return $row;
    }

    public function getCoordinates()
    {
        $row = DB::table("house_coordinates")->select('latitude', 'longitude')
            ->where("id_house", $this->id_house)->first();
        return $row ? [(float)$row->latitude, (float)$row->longitude] : null;
    }

    public function getBasicCoordinates()
    {
        $basic = DB::table('department_coordinates')->where('v_department', $this->v_department)->first();
        return $basic ? [$basic->latitude, $basic->longitude] : ['-18.250935', '-133.732470'];
    }

    public function getCloseMethod()
    {
        $row = DB::table('ci_flow')
            ->join('suz_requests', 'suz_requests.id_ci_flow', '=', 'ci_flow.id_ci_flow')
            ->select('close_method_name')
            ->where('suz_requests.id_ci_flow', $this->id_ci_flow)
            ->first();
        return $row->close_method_name ?? null;
    }

    public function getKitVTypeBySerial($v_serial)
    {
        $v_type = null;
        $service_info = json_decode($this->service_info);
        foreach ($service_info as $si) {
            if (!empty($si->equipment_list)) {
                $equipment_list = $si->equipment_list;
                foreach ($equipment_list as $eq) {
                    if ($eq->v_serial == $v_serial) {
                        $v_type = $this->getKitVTypeByModel($eq->id_equipment_model);
                        break;
                    }
                }
            }
        }
        return $v_type;
    }

    public function getComments()
    {
        $comments = DB::table('suz_request_story')
            ->select('suz_request_story.status_id', 'suz_request_story.dt_start as date', 'suz_request_story.comment as message', 'users.name as author', 'users.id as author_id')
            ->join('users', 'users.id', '=', 'suz_request_story.comment_author')
            ->where('suz_request_story.request_id', $this->id)
            ->whereNotNull('suz_request_story.comment')->get();
        foreach ($comments as &$comment) {
            $comment->date = date('H:i d.m.Y', strtotime($comment->date));
            $comment->status = $this->getStatusById($comment->status_id)->name;
        }
        return $comments;
    }

    public function getIdEquipmentInstByVSerial($v_serial)
    {
        $id_equipment_inst = null;
        $service_info = json_decode($this->service_info);
        foreach ($service_info as $si) {
            if (!empty($si->equipment_list)) {
                $equipment_list = $si->equipment_list;
                foreach ($equipment_list as $eq) {
                    if ($eq->v_serial == $v_serial) {
                        $id_equipment_inst = $eq->id_equipment_inst;
                        break;
                    }
                }
            }
        }
        return $id_equipment_inst;
    }

    public function getEquipmentsByVSerial($kit_serial)
    {
        $service_info = json_decode($this->service_info, true);
        $equipments = array();
        foreach ($service_info as $si) {
            if (count($si['equipment_list']) > 0) {
                foreach ($si['equipment_list'] as $eq) {
                    if ($eq['v_serial'] != $kit_serial) {
                        continue;
                    }
                    $equipments[] = [
                        'id_equipment_model' => $eq['id_equipment_model'],
                        'id_equipment_inst' => $eq['id_equipment_inst'],
                        'v_equipment_number' => $eq['v_equipment_number']
                    ];
                }
            }
        }
        return $equipments;
    }

    public function materialLimitStatistic(): HasMany
    {
        return $this->hasMany(MaterialLimitStatistic::class, 'request_id', 'id');
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class, 'id_town', 'id_town');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class, 'id_house', 'id_house');
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class, 'id_street', 'id_street');
    }

    public function buildAddress(): string
    {
        $address = '';

        // Check if the town relationship is loaded
        if ($this->relationLoaded('town')) {
            $address .= $this->town->v_name . ', ';
        }

        // Check if the street relationship is loaded
        if ($this->relationLoaded('street')) {
            $address .= $this->street->v_name . ', ';
        }

        // Check if the house relationship is loaded
        if ($this->relationLoaded('house')) {
            $address .= 'Дом номер:' . $this->house->house_nm . ', ';
        }

        $address .= 'Квартира номер:' . $this->v_flat;

        return trim($address, ', ');
    }
}
