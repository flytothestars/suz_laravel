<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuzRequest\AssignRequest;
use App\Http\Requests\SuzRequest\CancelRequest;
use App\Http\Requests\SuzRequest\CompleteRequest;
use App\Http\Requests\SuzRequest\PostponeRequest;
use App\Http\Requests\SuzRequest\ReturnRequest;
use App\Models\SuzRequest;
use App\Services\AjaxImageUploadService;
use App\Services\SuzRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuzRequestController extends Controller
{

    private SuzRequestService $service;
    private AjaxImageUploadService $ajaxImageUploadService;

    public function __construct(SuzRequestService $service, AjaxImageUploadService $ajaxImageUploadService)
    {
        $this->service = $service;
        $this->ajaxImageUploadService = $ajaxImageUploadService;
    }

    public function show($id): JsonResponse
    {
        $request = $this->service->show($id);
        return response()->json($request);
    }

    public function complete(CompleteRequest $request): JsonResponse
    {
        $suzRequest = SuzRequest::find($request->request_id);

        if (!$suzRequest) {
            return response()->json(['message' => 'Заявка не существует!']);
        }

        $result = $this->service->complete($request, $suzRequest);
        return response()->json($result);
    }

    /**
     * @throws ValidationException
     */
    public function postpone(PostponeRequest $req): JsonResponse
    {
        $this->service->postpone($req);
        return response()->json(['message' => 'Заявка отложена']);
    }

    /**
     * @throws ValidationException
     */
    public function cancel(CancelRequest $req): JsonResponse
    {
        $result = $this->service->cancel($req);
        return response()->json(['message' => $result['message']], $result['success'] ? 200 : 400);
    }

    public function return(ReturnRequest $request): JsonResponse
    {
        $result = $this->service->return($request);
        return response()->json(['message' => $result['message']], $result['success'] ? 200 : 400);
    }

    public function story(Request $req, int $id): JsonResponse
    {
        $req->id = $id;
        $requests = $this->service->story($req);
        return response()->json($requests);
    }

    public function assign(AssignRequest $req): JsonResponse
    {
        $json = $this->service->assign($req);
        return response()->json($json);
    }

    public function ajaxImageUploadPost(Request $request): JsonResponse
    {
        $requestId = $request->request_id;
        $files = $request->file('file');

        $result = $this->ajaxImageUploadService->uploadImages($requestId, $files, $request->all());

        return response()->json($result);
    }
}
