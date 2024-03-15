<?php

namespace App\Models;

use App\Http\Traits\CatalogsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasRoles;
    use Notifiable;
    use CatalogsTrait;

    protected $with = ['stocks'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'password', 'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    /**
     * Returns kits of user
     * @return HasMany
     */
    public function kits(): HasMany
    {
        return $this->hasMany('App\Models\Kit', 'owner_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany('App\Models\Equipment', 'owner_id');
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Stock');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Location');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Department');
    }

    public function getInstallerStocks()
    {
        $locations = Auth::user()->locations()->get(['id'])->pluck('id')->toArray();
        return Stock::whereIn('location_id', $locations)->get();
    }

    /**
     * Returns department of user
     * @return string|null
     */
    public function getDepartmentAttribute()
    {
        return $this->department_id != 0 ? $this->getDepartment($this->department_id) : null;
    }

    /**
     * Returns location of user
     * @return Collection|\Tightenco\Collect\Support\Collection|null
     */
    public function getLocationAttribute()
    {
        if ($this->location_id != 0) {
            $id = $this->location_id;
            $name = $this->getLocation($this->location_id);
            $collection = collect();
            $collection->id = $id;
            $collection->name = $name;
        }

        return $collection ?? null;
    }

    /**
     * Returns routelist of user
     * @return null
     */
    public function routeList($date = false)
    {
        $row = DB::table('installer_route_list')
            ->join('route_lists', 'route_lists.id', '=', 'installer_route_list.routelist_id')
            ->where('route_lists.date', $date)
            ->where(function ($q) {
                $q->where('installer_1', $this->id)->orWhere('installer_2', $this->id);
            })
            ->first(['routelist_id']);

        return $row ? RouteList::where('id', $row->routelist_id)->first() : null;
    }

    public function getRouteLists()
    {
        $rows = DB::table('installer_route_list')
            ->join('route_lists', 'route_lists.id', '=', 'installer_route_list.routelist_id')
            ->where(function ($q) {
                $q->where('installer_1', $this->id)
                    ->orWhere('installer_2', $this->id);
            })->pluck('routelist_id')->toArray();
        $routeLists = $rows ? RouteList::whereIn('id', $rows) : null;
        if ($routeLists) {
            $routeLists = $routeLists->orderBy('id', 'desc')->get();
        }
        return $routeLists;
    }

    /**
     * Returns all requests of user
     * @return Collection|\Tightenco\Collect\Support\Collection
     */
    public function requests()
    {
        $rows = DB::table('installer_route_list')
            ->select('routelist_id')
            ->where('installer_1', $this->id)
            ->orWhere('installer_2', $this->id)->get();
        $arr = array();
        foreach ($rows as $r) {
            $arr[] = $r->routelist_id;
        }
        $requests = collect();
        $routeLists = RouteList::whereIn('id', $arr)->get();
        foreach ($routeLists as $routeList) {
            $requests = $requests->merge($routeList->requests()->get());
        }
        return $requests;
    }

    /**
     * Not busy scope - returns users that are free to work
     * @param $query
     * @param bool $date
     * @return mixed
     */
    public function scopeNotBusy($query, $date = '')
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        /*
            select installer_1, installer_2 from installer_route_list irl inner join route_lists rl on rl.id = irl.routelist_id where rl.date = $date
        */
        $busy = DB::table("installer_route_list")
            ->join('route_lists', 'route_lists.id', '=', 'installer_route_list.routelist_id')
            ->where('route_lists.date', '=', $date)
            ->pluck('installer_1')
            ->merge(DB::table("installer_route_list")
                ->join('route_lists', 'route_lists.id', '=', 'installer_route_list.routelist_id')
                ->where('route_lists.date', '=', $date)
                ->pluck('installer_2'))
            ->filter()
            ->all();

        return $query->whereNotIn("id", $busy);
    }

    public function scopeCurrentLocation($query, $location_id)
    {
        $location = Location::find($location_id);
        $location_users = $location->users()->pluck('user_id')->toArray();
        return $query->whereIn('id', $location_users);
    }

    /**
     * Returns color depending on role of user
     * @return string
     */
    public function getColor(): string
    {
        $color = "primary";
        if ($this->hasRole('администратор')) {
            $color = "success";
        } elseif ($this->hasRole('диспетчер')) {
            $color = "light";
        } elseif ($this->hasRole('техник')) {
            $color = "danger";
        } elseif ($this->hasRole('кладовщик')) {
            $color = "info";
        } elseif ($this->hasRole('супервизор')) {
            $color = "warning";
        } elseif ($this->hasRole('инспектор')) {
            $color = "dark";
        }
        return $color;
    }

    public function isBusy(): bool
    {
        $rows = DB::table("installer_route_list")
            ->join('route_lists', 'route_lists.id', '=', 'installer_route_list.routelist_id')
            ->where('route_lists.date', '=', date('Y-m-d'))
            ->get(["installer_1", "installer_2"]);
        $busy = array();
        foreach ($rows as $row) {
            if ($row->installer_1) {
                $busy[] = $row->installer_1;
            }
            if ($row->installer_2) {
                $busy[] = $row->installer_2;
            }
        }
        return in_array($this->id, $busy);
    }

    public function getMaterials()
    {
        $materials = UserMaterial::where([
            ['user_id', '=', $this->id],
            ['qty', '<>', 0],
        ])->get();
        //Рефакторить пагинация не работает для мобильки
        return $materials->map(function ($userMaterial) {
            $material = $userMaterial->material;
            return (object)[
                'id' => $userMaterial->id,
                'user_id' => $userMaterial->user_id,
                'material_id' => $material->id,
                'qty' => $userMaterial->qty,
                'name' => $material->name,
                'type' => $material->type,
                'limit_qty' => optional($material->getMaterialLimit)->limit_qty,
            ];
        });
    }

    public function getMaterialsApi()
    {
        return UserMaterial::where([
            ['user_id', '=', $this->id],
            ['qty', '<>', 0],
        ])->paginate(20);
    }

    public function materialLimitStatistic(): HasMany
    {
        return $this->hasMany(MaterialLimitStatistic::class, 'installer_id', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
