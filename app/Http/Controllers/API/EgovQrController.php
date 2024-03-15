<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EgovQr\CheckRequest;
use App\Http\Requests\EgovQr\GenerateRequest;
use App\Http\Requests\EgovQr\GetAddressRequest;
use App\Services\AjaxImageUploadService;
use App\Services\EgovQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EgovQrController extends Controller
{
    private EgovQrService $service;
    private AjaxImageUploadService $ajaxImageUploadService;

    public function __construct(EgovQrService $service, AjaxImageUploadService $ajaxImageUploadService)
    {
        $this->service = $service;
        $this->ajaxImageUploadService = $ajaxImageUploadService;
    }

    public function generate(GenerateRequest $request): JsonResponse
    {
        return response()->json($this->service->generate($request));
    }

    public function check(CheckRequest $request): JsonResponse
    {
        return response()->json($this->service->check($request));
    }

    public function getAddress(GetAddressRequest $request): JsonResponse
    {
        return response()->json($this->service->getAddress($request));
    }

    public function offlineSign(Request $request): JsonResponse
    {
        $requestId = $request->request_id;
        $files = $request->file('file');
        $title = 'Оффлайн подписание договора.';

        $result = $this->ajaxImageUploadService->uploadImages($requestId, $files, $request->all(),$title);

        return response()->json($result);
    }
}
