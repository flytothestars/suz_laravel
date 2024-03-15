<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Equipment extends Model
{
    protected $with = ['model'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stock_id', 'owner_id', 'from', 'from_stock'
    ];
    /**
     * @return BelongsTo
     */
    public function kit()
    {
        return $this->belongsTo('App\Models\Kit');
    }

    /**
     * Returns equipments that belongs to current department
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $v_department
     * @return mixed
     */
    public function scopeCurrentDepartment($query, $v_department)
    {
        $inventories = Kit::where('v_department', $v_department)->get();
        $equipments = collect();
        $ids = array();
        foreach ($inventories as $inv) {
            foreach ($inv->equipments as $eq) {
                $ids[] = $eq->id;
            }
        }
        return $query->whereIn('id', $ids);
    }

    /**
     * Returns department of this equipment
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getDepartmentAttribute()
    {
        $row = DB::table('fw_departments')->where('v_ext_ident', $this->kit->v_department)->first();
        return $row ?? null;
    }

    /**
     * Returns model of this equipment
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getModelAttribute()
    {
        $row = DB::table('alma_equipment_model')->where('id_equipment_model', $this->id_equipment_model)->first();
        return $row ?? null;
    }

    /**
     * Returns total count of equipments by id_equipment_model
     * @return mixed
     */
    public function getTotalCountAttribute()
    {
        $count = $this->where('id_equipment_model', $this->id_equipment_model)->get()->count();
        return $count;
    }

    /**
     * Returns count of equipments in current department by id_equipment_model
     * @return mixed
     */
    public function getCountAttribute()
    {
        $count = $this->where('id_equipment_model', $this->id_equipment_model)->currentDepartment($this->kit->v_department)->get()->count();
        return $count;
    }

    /**
     * Returns how many items of current equipment model does installer have
     * @param integer $installer_id
     * @return Collection $equipments
    */
    public function installerHave($installer_id)
    {
        $kits = $this->kit->id;
        $equipments = Equipment::where('id_equipment_model', $this->id_equipment_model)
            ->where('owner_id', $installer_id)->get();
        return $equipments;
    }

    /**
     * Returns mnemonic of kit by equipments model
     * @param $id_equipment_model
     * @return string|null $v_type
    */
    public function getKitVTypeByModel($id_equipment_model)
    {
        $row = DB::table('alma_grid_fill_eq_kits_type')->select('id_equip_kits_type')->where('id_equipment_model', $id_equipment_model)->first();
        $v_type = null;
        if($row)
        {
            $id_equip_kits_type = $row->id_equip_kits_type;
            $row = DB::table('alma_equipment_kits_type')->where('id_equip_kits_type', $id_equip_kits_type)->get(['v_mnemonic']);
            $v_type = $row->v_mnemonic ?? null;
        }
        return $v_type;
    }

    /**
     * Writes any equipment movement to story
     * @param $author_id
     * @param $owner_id
     * @param $stock_id
     */
    public function writeStory($author_id, $owner_id, $from, $from_stock, $stock_id=null, $id_flow=null, $v_kits_transfer=null)
    {
        DB::table('equipment_story')->insert([
            'equipment_id' => $this->id,
            'author_id' => $author_id,
            'owner_id' => $owner_id,
            'from' => $from,
            'from_stock' => $from_stock,
            'stock_id' => $stock_id,
            'created_at' => date('Y-m-d H:i:s'),
            'is_kit' => false,
            'id_flow' => $id_flow,
            'serial' => $this->v_serial,
            'v_kits_transfer' => $v_kits_transfer
        ]);
    }

    public function getStory()
    {
        $rows = DB::table('equipment_story')->where('equipment_id', $this->id)->where('is_kit', 0)->get();
        foreach($rows as &$row)
        {
            $owner_id = $row->owner_id;
            $row->owner = '-';
            if($owner_id)
            {
                $user = User::find($owner_id);
                if($user)
                {
                    $row->owner = $user->name;
                }
                else
                {
                    $row->owner = $owner_id;
                }
            }
            $row->author = User::find($row->author_id)->name;
            $row->stock = $row->stock_id ? Stock::find($row->stock_id)->name : '-';
        }
        return $rows;
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AlmaEquipment::class, 'id_equipment_model','id_equipment_model');
    }

    public function type()
    {
        return $this->model()->first()->modelType();
    }
}
