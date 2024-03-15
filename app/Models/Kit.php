<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Kit extends Model
{
    protected $table = 'kit';

    public function equipments(): HasMany
    {
        return $this->hasMany('App\Models\Equipment');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function getNameAttribute()
    {
        $row = DB::table('alma_equipment_kits_type')->where('v_mnemonic', $this->v_type)->first();
        return $row ? $row->v_name : '';
    }

    public function getCountAttribute()
    {
        $inventoriesCount = $this->where('v_type', $this->v_type)->get()->count();
        return $inventoriesCount;
    }

    /**
     * Writes any kit movement to story
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
            'is_kit' => true,
            'id_flow' => $id_flow,
            'serial' => $this->v_serial,
            'v_kits_transfer' => $v_kits_transfer
        ]);
    }

    public function getStory(): Collection
    {
        $rows = DB::table('equipment_story')->where('equipment_id', $this->id)->where('is_kit', 1)->get();
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

    public function getPrevious()
    {
        $row = DB::table('equipment_story')->where('equipment_id', $this->id)->where('is_kit', 1)->orderBy('id', 'desc')->skip(1)->take(1)->first();
        return $row;
    }

    public function getStockAttribute()
    {
        $stock = null;
        if($this->stock_id)
        {
            $stock = Stock::find($this->stock_id);
        }
        return $stock;
    }

}
