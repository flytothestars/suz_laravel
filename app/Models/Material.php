<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

//TODO Рефакторинг, нужно делать по релейшенам через модели, а не через фасад DB
class Material extends Model
{
    protected $table = 'materials';
    protected $with = ['getMaterialLimit'];
    protected $fillable = [
        'name',
        'type_id',
    ];

    protected $guarded = [
        'id'
    ];

    public function getTypeAttribute()
    {
        $row = DB::table('material_types')->where('id', $this->type_id)->first();
        return $row->name ?? '';
    }

    /**
     * @return HasOne
     */
    public function getMaterialLimit(): HasOne
    {
        return $this->hasOne(MaterialLimit::class, 'material_id', 'id');
    }

    public function userMaterials(): HasMany
    {
        return $this->hasMany(UserMaterial::class, 'material_id', 'id');
    }

    public function getUserOwners()
    {
        $user_ids = DB::table('user_material')->where('material_id', $this->id)->orderBy('qty', 'DESC')->pluck('user_id')->toArray();
        $users = User::whereIn('id', $user_ids)->get();
        foreach ($users as $key => $user) {
            $user->qty = DB::table('user_material')->where('user_id', $user->id)->where('material_id', $this->id)->pluck('qty')->first();
        }
        return $users->sortByDesc('qty');
    }

    public function getClientOwners(): Collection
    {
        return DB::table('client_material')->where('material_id', $this->id)->orderBy('qty', 'DESC')->get();
    }

    public function getStockOwners()
    {
        $stock_ids = DB::table('stock_material')->where('material_id', $this->id)->orderBy('qty', 'DESC')->pluck('stock_id')->toArray();
        $stocks = Stock::whereIn('id', $stock_ids)->get();
        foreach ($stocks as $key => $stock) {
            $stock->qty = DB::table('stock_material')->where('stock_id', $stock->id)->where('material_id', $this->id)->pluck('qty')->first();
        }
        return $stocks->sortByDesc('qty');
    }

    public function limitStatistic(): HasMany
    {
        return $this->hasMany(MaterialLimitStatistic::class, 'material_id', 'id');
    }
}
