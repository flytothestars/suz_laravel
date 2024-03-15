<?php

namespace App\Models;

use App\Http\Traits\CatalogsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock extends Model
{
    use CatalogsTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stocks';

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function equipments()
    {
        return $this->hasMany('App\Models\Equipment', 'stock_id', 'id');
    }

    public function kits()
    {
        return $this->hasMany('App\Models\Kit', 'stock_id', 'id');
    }

    public function getDepartmentAttribute()
    {
        return $this->getDepartment($this->department_id);
    }

    public function hasEquipments()
    {
        return ($this->equipments->count() > 0 || $this->kits->count() > 0);
    }

    public function getMaterials()
    {
        $materials = DB::table('stock_material')->where('stock_id', $this->id)->where('qty','>', 0)->paginate(20);
        foreach($materials as &$m)
        {
            $material = Material::find($m->material_id);
            $m->name = $material->name;
            $m->type = $material->type;
        }
        return $materials;
    }
    public function getMaterialsRequestFrom()
    {
        $materials_request = DB::table('materials_request')->where('from_stock', $this->id)->paginate(20);
        foreach($materials_request as &$mr)
        {
            $material = Material::find($mr->material_id);
            $mr->name = $material->name;
            $mr->type = $material->type;
        }
        return $materials_request;
    }
    public function getMaterialsRequestTo()
    {
        $materials_request = DB::table('materials_request')->where('stock_id', $this->id)->paginate(20);
        foreach($materials_request as &$mr)
        {
            $material = Material::find($mr->material_id);
            $mr->name = $material->name;
            $mr->type = $material->type;
        }
        return $materials_request;
    }
    public function getkitRequestFrom()
    {
        $kit_request = DB::table('kit_request')->where('from_stock', $this->id)->paginate(20);
        foreach($kit_request as &$kr)
        {
            $kit = Kit::find($kr->kit_id);
            if(isset($kit))
            {
                $kr->name = $kit->name;
            }
        }
        return $kit_request;
    }
    public function getkitRequestTo()
    {
        $kit_request = DB::table('kit_request')->where('stock_id', $this->id)->paginate(20);
        foreach($kit_request as &$kr)
        {
            $kit = Kit::find($kr->kit_id);
            if(isset($kit))
            {
                $kr->name = $kit->name;
            }
        }
        return $kit_request;
    }
    public function getEquipmentStory()
    {
        $dateFrom = date("Y-m-d", strtotime("-1 month")) . " 00:00";
        $dateTo   = date("Y-m-d", strtotime("now")) . " 23:59";
        $equipment_story = DB::table('equipment_story')->where(function($q){
            $q->where('stock_id', $this->id)->orWhere('from_stock', $this->id);
        });
        $equipment_story = $equipment_story->whereBetween("created_at", [$dateFrom, $dateTo])->orderBy('id', 'DESC')->paginate(30);
        foreach($equipment_story as &$eq)
        {
            $stocks = Stock::find($eq->stock_id);
            if(isset($stocks))
            {
                $eq->stockname = $stocks->name;
            }
            $from_stocks = Stock::find($eq->from_stock);
            if(isset($from_stocks))
            {
                $eq->from_stockname = $from_stocks->name;
            }
            $users = User::find($eq->author_id);
            $eq->username = $users->name;
            $owners = User::find($eq->owner_id);
            if(isset($owners))
            {
                $eq->ownername = $owners->name;
            }
            $froms = User::find($eq->from);
            if(isset($froms))
            {
                $eq->fromname = $froms->name;
            }
            $kit = Kit::find($eq->equipment_id);
            if(isset($kit))
            {
                $mnemonic = DB::table('alma_equipment_kits_type')->where('v_mnemonic', $kit->v_type)->first();
                $mnemonic = $mnemonic->v_name;
                $eq->kitname = $mnemonic;
            }
        }
        return $equipment_story;
    }
    public function getMaterialsStory()
    {
        $dateFrom = date("Y-m-d", strtotime("-1 month")) . " 00:00";
        $dateTo   = date("Y-m-d", strtotime("now")) . " 23:59";
        $materials_story = DB::table('materials_story')->where('stock_id', $this->id)->orWhere('from_stock', $this->id)->orderBy('id', 'DESC')->whereBetween("created_at", [$dateFrom, $dateTo])->paginate(30);
        foreach($materials_story as &$ms)
        {
            $stocks = Stock::find($ms->stock_id);
            $ms->stockname = $stocks->name;
            $from_stocks = Stock::find($ms->from_stock);
            if(isset($from_stocks))
            {
                $ms->from_stockname = $from_stocks->name;
            }
            $users = User::find($ms->author_id);
            if(isset($users))
            {
                $ms->username = $users->name;
            }
            $owners = User::find($ms->owner_id);
            if(isset($owners))
            {
                $ms->ownername = $owners->name;
            }
            $froms = User::find($ms->from);
            if(isset($froms))
            {
                $ms->fromname = $froms->name;
            }
            $material = Material::find($ms->material_id);
            if(isset($material))
            {
                $mnemonic = DB::table('materials')->where('id', $ms->material_id)->first();
                $mnemonic = $mnemonic->name;
                $ms->materialname = $mnemonic;
            }
        }
        return $materials_story;
    }
}
