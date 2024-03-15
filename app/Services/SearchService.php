<?php

namespace App\Services;

use App\Http\Traits\CatalogsTrait;
use App\Models\Kit;
use App\Models\Stock;
use App\Models\SuzRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchService
{
    use CatalogsTrait;
    public function searchRequest(Request $request): array
    {
        $user = Auth::user();
        $query = $request->q;
        $suzRequests = null;

        if ($user->hasAnyRole(['администратор', 'диспетчер', 'кладовщик', 'инспектор', 'техник'])) {
            $suzRequests = SuzRequest::where('id_flow', $query)->orWhere('v_contract', $query);
            if (is_numeric($query)) {
                $suzRequests = $suzRequests->orWhere('id', $query);
            }

            $suzRequests = $suzRequests->get();

            if ($suzRequests->count() == 1) {
                return ['status' => 'redirect', 'path' => '/requests/' . $suzRequests[0]->id];
            } elseif ($suzRequests->count() > 1) {
                foreach ($suzRequests as &$request) {
                    $request->kind_works = $this->getKindWorksById($request->id_kind_works);
                    $request->status = $this->getStatusById($request->status_id)->name;
                }
            }
        }

        $kits = Kit::where('v_serial', $query)->get();

        if ($kits) {
            foreach ($kits as &$kit) {
                if ($kit->owner_id) {
                    $user = User::find($kit->owner_id);
                    if ($user) {
                        $kit->owner = $user->name;
                    } else {
                        $kit->owner = $kit->owner_id;
                    }
                } else {
                    $stock = Stock::find($kit->stock_id);
                    if ($stock) {
                        $kit->owner = $stock->name;
                    } else {
                        $kit->owner = '?';
                    }
                }
                $kit->returned = $kit->returned ? 'Да' : 'Нет';
            }
        }

        return ['status' => 'success' ,'data' => compact(['suzRequests', 'kits'])];
    }
}
