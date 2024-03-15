<?php

namespace App\Http\Controllers;

use App\Http\Traits\CatalogsTrait;
use App\Services\TelegramAuthorizationService;
use App\Services\UserService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    use CatalogsTrait;

    private UserService $service;
    private TelegramAuthorizationService $telegramService;

    public function __construct(UserService $service, TelegramAuthorizationService $telegramAuthorizationService)
    {
        $this->service = $service;
        $this->telegramService = $telegramAuthorizationService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Factory|Application|View
     */
    public function index(Request $request)
    {
        $data = $this->service->index($request);

        return view('users.index', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Factory|Application|View
     */
    public function show(int $id)
    {
        $data = $this->service->show($id);
        return view('users.show', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $this->service->update($request);
        return redirect()->back()->with('message', 'Сохранено');
    }

    /**
     * @throws Exception
     */
    public function checkTelegramAuthorization(Request $auth_data)
    {
        $message = $this->telegramService->authorizeUser($auth_data);

        if ($message) {
            return redirect()->back()->with('message', $message);
        } else {
            return redirect()->back();
        }
    }
}
