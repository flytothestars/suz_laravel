<?php

namespace App\Services;

use App\Http\Traits\AdminTrait;
use App\Http\Traits\CatalogsTrait;
use App\Http\Traits\SoapTrait;
use App\Models\Equipment;
use App\Models\Kit;
use App\Models\Material;
use App\Models\MaterialLimitStatistic;
use App\Models\RequestRepairType;
use App\Models\SuzRequest;
use Geeky\Database\CacheQueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminService
{
    use CatalogsTrait;
    public function getOrdersByContract($contract)
    {
        $date_to = date('Y-m-d H:i:s');
        $date_from = date('Y-m-d H:i:s', strtotime('-15 days'));

        $rows = SuzRequest::where('v_contract', $contract)
            ->whereIn('status_id', ['2', '4', '5'])
            ->whereBetween('dt_start', [$date_from, $date_to])
            ->get();

        $response = collect();

        foreach ($rows as $row) {
            $type = $this->getKindWorksById($row->id_kind_works);
            $status = $this->getStatusById($row->status_id)->name;
            $installers = $this->getInstallers($row->id);
            $array = [
                "order_number" => $row->id_flow,
                "order_technics" => $installers,
                "order_status" => $status,
                "order_type" => $type,
                "plan_date" => $row->dt_plan_date
            ];
            $response->push($array);
        }

        return $response;
    }

    public function changeEquipmentStock($kitId, $fromUserId, $fromStockId, $stockId): array
    {
        $kit = Kit::find($kitId);
        if (!$kit) {
            return [
                'success' => false,
                'html' => 'Комплект не найден!'
            ];
        }

        try {
            DB::beginTransaction();

            $kit->owner_id = null;
            $kit->stock_id = $stockId;
            $kit->save();
            $kit->writeStory($fromUserId, null, $fromUserId, $fromStockId, $stockId);

            foreach ($kit->equipments as $eq) {
                $eq->owner_id = null;
                $eq->stock_id = $stockId;
                $eq->save();
            }

            DB::commit();

            return [
                'success' => true,
                'html' => 'Перемещено на склад!'
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'success' => false,
                'html' => 'Ошибка запроса, посмотрите логи!',
                'error_message' => $exception->getMessage()
            ];
        }
    }

    public function changeEquipmentOwner($kitId, $ownerId): array
    {
        $kit = Kit::find($kitId);

        if (!$kit) {
            return [
                'success' => false,
                'html' => 'Комплект не найден!'
            ];
        }

        try {
            DB::beginTransaction();

            $kit->owner_id = $ownerId;
            $kit->stock_id = null;
            $kit->save();

            foreach ($kit->equipments as $eq) {
                $eq->owner_id = $ownerId;
                $eq->stock_id = null;
                $eq->save();
            }

            DB::commit();

            return [
                'success' => true,
                'html' => 'Владелец успешно сменен!'
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'success' => false,
                'html' => 'Ошибка запроса, посмотрите логи!',
                'error_message' => $exception->getMessage()
            ];
        }
    }
}
