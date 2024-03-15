<?php

namespace App\Http\Controllers;

use App\Http\Traits\CatalogsTrait;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    use CatalogsTrait;

    private SearchService $service;

    public function __construct(SearchService $service)
    {
        $this->service = $service;
    }

    public function search(Request $request)
    {
        $data = $this->service->searchRequest($request);

        if ($data['status'] == 'redirect') {
            return redirect($data['path']);
        }

        return view('search', $data['data']);
    }

    public function searchUser(Request $request): JsonResponse
    {
        $users = DB::table('users')->select('id', 'name')->where('name', 'like', '%' . $request->q . '%')->take(10)->get();
        return response()->json($users);
    }

    public function searchKit(Request $request)
    {
        $me = Auth::user();
        $output = "";
        $query = $request->get('searchQuery');
        $kits = $me->kits()->where('v_serial', 'LIKE', '%' . $query . '%')->orderBy('updated_at', 'desc')->paginate(5);
        if ($kits->count() > 0) {
            foreach ($kits as $kit) {
                $output .= '
				<tr>
	                <td>
	                    <button data-id="' . $kit->id . '" class="btn btn-white show-kit"><i class="fas fa-list"></i></button>
	                    <span class="h5 equipment-model">' . $kit->v_type . '</span>
	                </td>
	                <td>
	                    <span class="h5">' . $kit->v_serial . '</span>
	                </td>
	                <td>' . $kit->equipments->count() . '</td>
	                <td>' . $kit->updated_at . '</td>
	            </tr>
				<div class="my-3">'
                    . $kits->links() .
                    '</div>';
            }
        }
        return json_encode($output);
    }
}
