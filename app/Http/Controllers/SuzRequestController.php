<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuzRequest\AssignRequest;
use App\Http\Requests\SuzRequest\CancelRequest;
use App\Http\Requests\SuzRequest\CompleteRequest;
use App\Http\Requests\SuzRequest\PostponeRequest;
use App\Http\Requests\SuzRequest\ReturnRequest;
use App\Http\Traits\CatalogsTrait;
use App\Models\SuzRequest;
use App\Models\User;
use App\Services\SuzRequestService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;

class SuzRequestController extends Controller
{
    use CatalogsTrait;

    private SuzRequestService $service;

    public function __construct(SuzRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $compact = $this->service->index($request);
        return view('requests.index', $compact);
    }

    public function show($id)
    {
        $user = auth()->user();
        $request = $this->service->show($id);

        if (!$user->telegram_id && $user->hasRole('техник')) {
            return view('telegram');
        }

        return view('requests.show', compact(['request']));

    }

    /**
     */
    public function downloadWord(Request $req)
    {
        try {
            $result = $this->service->downloadWord($req);
            if (!$result['success']) {
                return redirect($result['route'])->with('message', $result['message']);
            }
        } catch (CopyFileException|CreateTemporaryFileException $e) {
            Log::error($e->getMessage(), ['trace' => $e->getTrace()]);
            return response()->json('Error of downloading file');
        }

        return response()->download(storage_path('app/word/' . $result['filename']))->deleteFileAfterSend();
    }

    /**
     * Показывает страницу назначения заявки бригадиру
     * @param Request $req
     * @return Factory|View
     */
    public function assignIndex(Request $req)
    {
        $request = SuzRequest::find($req->id);
        $request->installers = $request->getRequestInstallers();
        $request->date = $request->getLastDate();
        $request->dt_status_start = $request->getLastDtStart();
        $request->status = $this->getStatusById($request->status_id)->name;
        $installers = User::role('техник')->get();

        return view('requests.assign_index', compact(['request', 'installers']));
    }

    /**
     * Назначение заявки
     * @param AssignRequest $req
     * @return JsonResponse
     */
    public function assign(AssignRequest $req): JsonResponse
    {
        $json = $this->service->assign($req);
        return response()->json($json);
    }

    /**
     * Показывает страницу отложения заявки
     * @param Request $req
     * @return Factory|View
     */
    public function postponeIndex(Request $req)
    {
        $request = SuzRequest::find($req->id);
        $time_intervals = range(strtotime("08:00"), strtotime("22:30"), 30 * 60);
        return view('requests.postpone_index', compact(['request', 'time_intervals']));
    }

    /**
     * Отложение заявки
     * @param PostponeRequest $req
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function postpone(PostponeRequest $req): RedirectResponse
    {
        $this->service->postpone($req);
        return redirect()->route('request', ['id' => $req->request_id])->with('message', 'Заявка отложена');
    }

    /**
     * Показывает страницу отмены заявки
     * @param Request $req
     * @return Factory|View
     */
    public function cancelIndex(Request $req)
    {
        $request = SuzRequest::find($req->id);
        $reasons = $this->getReasons();
        return view('requests.cancel_index', compact(['request', 'reasons']));
    }

    /**
     * Отмена заявки
     * @param CancelRequest $req
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function cancel(CancelRequest $req): RedirectResponse
    {
        $result = $this->service->cancel($req);

        if (!$result['success']) {
            return redirect($result['route'])->with('message', $result['message']);
        }

        return redirect()->route($result['route'], $result['params'])->with('message', $result['message']);
    }

    public function returnIndex(Request $req, $id = null)
    {
        $id = $id ?: $req->id;
        $request = SuzRequest::select('id', 'id_flow', 'v_client_title', 'v_contract', 'v_client_cell_phone')->where('id', $id)->first();

        $installer_take = $req->installer_take ?? null;
        $installer_give = $req->installer_give ?? null;
        $materials_take = $req->materials_take ?? null;
        $materials_qty_take = $req->materials_qty_take ?? null;
        $materials_give = $req->materials_give ?? null;
        $materials_qty_give = $req->materials_qty_give ?? null;
        $status_type = $req->status_type ?? 'complete';
        $limit_input = $req->limit_input ?? null;

        return view('requests.return', compact(
            [
                'limit_input',
                'request',
                'installer_take',
                'installer_give',
                'materials_take',
                'materials_qty_take',
                'materials_give',
                'materials_qty_give',
                'status_type'
            ]));
    }

    public function return(ReturnRequest $request)
    {
        $result = $this->service->return($request);

        if (!isset($result['route'])) {
            return response()->json($result);
        }

        return redirect($result['route'])->with('message', $result['message']);
    }

    public function completeIndex(Request $request, $id)
    {
        $request->request_id = $id;
        $compact = $this->service->completeIndex($request);
        return view('requests.complete', $compact);
    }

    public function complete(CompleteRequest $request, $id)
    {
        $request->request_id = $id;
        $suzRequest = SuzRequest::find($request->request_id);

        if (!$suzRequest) {
            return redirect()->back()->with('message', 'Заявка не существует!');
        }

        $result = $this->service->complete($request, $suzRequest);

        return redirect($result['route'])->with('message', $result['message']);
    }

    public function story(Request $req)
    {
        $requests = $this->service->story($req);
        return view('requests.story', $requests);
    }

    public function writeMaterial(Request $request)
    {
        $place = $request->place;
        $data = json_decode($request->data);
        $status_type = decodeJson($request->status_type);
        $redirectUrl = '/requests/' . $request->id;

        //Насколько я понял, здесь для complete пост запрос на фронте, поэтому здесь его нет.
        if ($status_type == 'undo') {
            $redirectUrl .= '/return/';
        } elseif ($status_type == 'cancel') {
            $redirectUrl .= '/cancel/';
        } elseif ($status_type == 'postpone') {
            $redirectUrl .= '/postpone/';
        }

        if ($place == 'complete') {
            $installers = $data->installers;
            $data->installers = implode(', ', $installers);

            $completeRequest = CompleteRequest::createFrom($request, (array)$data);
            $completeRequest->setContainer(app());

            return $this->complete($completeRequest, $request->id);
        } elseif ($place == 'return') {
            $materials_give = json_decode($request->materials_give);
            $materials_qty_give = json_decode($request->materials_qty_give);

            $materials_origin = array_map(function ($give, $qty) {
                return ['id' => $give, 'qty' => $qty];
            }, $materials_give, $materials_qty_give);

            $materials = [];

            foreach ($materials_origin as $id => $qty) {
                $materials[] = [
                    'id' => $id,
                    'qty' => $qty,
                ];
            }

            $returnRequest = ReturnRequest::createFrom($request, [
                'request_id' => $data->id,
                'comment' => $request->comment,
                'materials' => $materials
            ]);

            $returnRequest->setContainer(app());

            return $this->return($returnRequest);

        }

        $json = $this->service->writeMaterial($request);
        $message = $json['message'];

        return redirect($redirectUrl)->with('message', $message);
    }

    public function fixEquipment(Request $request): JsonResponse
    {
        $result = $this->service->fixEquipment($request);
        return response()->json($result);
    }

    public function send2Telegram($id, $msg, $token = '', $silent = false)
    {
        $this->service->send2Telegram($id, $msg, $token, $silent);
    }

    public function storyByGroup(Request $request)
    {

        $compact = $this->service->storyByGroup($request);
        return view('requests.storybygroup', $compact);
    }
}
