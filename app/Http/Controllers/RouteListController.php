<?php

namespace App\Http\Controllers;

use App\Http\Requests\RouteList\StoreRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\Location;
use App\Models\RouteList;
use App\Models\SuzRequest;
use App\Models\User;
use App\Services\RouteListService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RouteListController extends Controller
{
    use CatalogsTrait;

    private RouteListService $service;

    public function __construct(RouteListService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $compact = $this->service->index($request);
        return view('routing.index', $compact);
    }

    /**
     * Display route lists to installer.
     * @param Request $request
     * @return Factory|Application|View
     */
    public function indexInstaller(Request $request)
    {

        $data = $this->service->indexInstaller($request);

        if (auth()->user()->telegram_id) {
            return view('installers.index', $data);
        }

        return view('telegram');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->store($request);
        return redirect()->back()->with('message', 'Маршрут добавлен');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function show(Request $request)
    {
        $routeList = RouteList::find($request->id);
        $routeList->location = $this->getLocation($routeList->location_id);
        return view('routelist.show', compact(['routeList']));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $routeListId = (int)$request->routelist_id;
        $installer_1 = (int)$request->edit_installer_1;
        $installer_2 = (int)$request->edit_installer_2;

        if ($installer_1 == 0 && $installer_2 == 0) {
            throw ValidationException::withMessages([
                'message' => ['Нужен хотя бы один монтажник']
            ]);
        }

        $routeList = RouteList::find($routeListId);
        $requests = $routeList->requests()->get();
        $requestsCopies = array();
        $requestsIds = array();
        $dispatcher_id = Auth::user()->id;

        foreach ($requests as $req) {
            $reqCopy = $req->replicate();
            $reqCopy->request_id = (int)$req->id;
            $reqCopy->dt_start = date("Y-m-d H:i:s");
            $reqCopy->dispatcher_id = $dispatcher_id;
            $reqCopy->installer_1 = $installer_1 == 0 ? null : $installer_1;
            $reqCopy->installer_2 = $installer_2 == 0 ? null : $installer_2;
            $requestsCopies[] = $reqCopy->toArray();
            $requestsIds[] = (int)$req->id;
        }

        // Начинаем транзакцию
        DB::beginTransaction();
        try {
            // Обновляем дату последней записи в истории
            DB::table("suz_request_story")
                ->whereIn("request_id", $requestsIds)
                ->orderBy("id", "desc")
                ->take(1)->update(['dt_stop' => date("Y-m-d H:i:s")]);

            // связываем заявку с диспетчером
            DB::table("suz_request_dispatcher")->whereIn("request_id", $requestsIds)->delete();
            $suzRequestDispatcherArray = array();
            foreach ($requestsIds as $id) {
                $suzRequestDispatcherArray[] = [
                    'request_id' => $id,
                    'dispatcher_id' => $dispatcher_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            DB::table("suz_request_dispatcher")->insert($suzRequestDispatcherArray);

            // записываем в таблицу истории
            DB::table("suz_request_story")->insert($requestsCopies);

            DB::table("installer_route_list")->where("routelist_id", $routeListId)->delete();
            DB::table("installer_route_list")->insert([
                'installer_1' => $installer_1,
                'installer_2' => $installer_2,
                'routelist_id' => $routeListId,
                'created_at' => date("Y-m-d H:i:s")
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw ValidationException::withMessages([
                'rollback' => ["Rolling back: " . $th->getMessage()]
            ]);
        }
        return redirect()->back()->with('message', 'Маршрутный лист обновлен');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        if (isset($request->id)) {
            $id = $request->id;
            $routeList = RouteList::find($id);
            if (!$routeList->requests()->get()) {
                $json = [
                    'success' => false,
                    'message' => 'Маршрут содержит заявки. Вы не можете просто так удалить его.',
                ];
                return response()->json($json, 400);
            }
            RouteList::destroy($id);
            DB::table("installer_route_list")->where("routelist_id", $id)->delete();
            $json = [
                'success' => true,
                'message' => 'Маршрут успешно удален.',
            ];
            return response()->json($json);
        } else {
            $json = [
                'success' => false,
                'message' => 'Маршрут не удален, повторите еще раз.',
            ];
            return response()->json($json, 400);
        }
    }

    public function getRouteCoordinates(Request $req)
    {
        $routeList = RouteList::find($req->id);
        $coordinates = array();
        if ($routeList) {
            $requests = $routeList->requests()->get();
            foreach ($requests as $req) {
                $coordinates[] = $req->getCoordinates();
            }
        }
        echo json_encode($coordinates);
    }

    public function getRouteListInstallers(Request $request): JsonResponse
    {
        $department_id = $request->department_id;
        $routelistId = $request->routelist_id;
        $routeList = RouteList::find($routelistId);
        $installers = User::role('техник')->notBusy($request->date)->where('department_id', $department_id)->get();
        $routeListInstallers = $routeList->installers();
        $options = "<option value='0'>Не выбрано</option>";

        foreach ($installers as $inst) {
            $options .= "<option value='" . $inst->id . "'>" . $inst->name . "</option>";
        }

        $options .= "<option value='" . $routeListInstallers[0]->id . "'>" . $routeListInstallers[0]->name . "</option>";

        if (isset($routeListInstallers[1])) {
            $options .= "<option value='" . $routeListInstallers[1]->id . "'>" . $routeListInstallers[1]->name . "</option>";
        }

        $arr_1 = [
            'options' => $options,
            'val' => $routeListInstallers[0]->id
        ];

        $arr_2 = [
            'options' => $options,
            'val' => $routeListInstallers[1]->id ?? 0
        ];

        $html = array($arr_1, $arr_2);
        $json = json_encode($html);

        return response()->json($json);
    }
}
