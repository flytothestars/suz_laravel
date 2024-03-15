<?php

namespace App\Http\Controllers;

use App\Services\AjaxImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AjaxImageUploadController extends Controller
{
    protected AjaxImageUploadService $ajaxImageUploadService;

    public function __construct(AjaxImageUploadService $ajaxImageUploadService)
    {
        $this->ajaxImageUploadService = $ajaxImageUploadService;
    }

    public function ajaxImageUploadPost(Request $request): JsonResponse
    {
        $requestId = $request->request_id;
        $files = $request->file('file');

        $result = $this->ajaxImageUploadService->uploadImages($requestId, $files, $request->all());

        return response()->json($result);
    }
}
