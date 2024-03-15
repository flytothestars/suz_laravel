<?php

namespace App\Http\Controllers;

use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    private CaptchaService $captchaService;

    public function __construct(CaptchaService $service)
    {
        $this->captchaService = $service;
    }

    public function index()
    {
        if (!Auth::user()) {
            $data = [];
            if(env('APP_ENV') != 'dev' && env('APP_ENV') != 'stage'){
                $data = $this->captchaService->generateCaptcha();
            }
            return view('auth.login', $data);
        }

        $roles = Auth::user()->getRoleNames()->toArray();

        if (!count($roles)) {
            return response()->view('errors.403', [], 403);
        }

        $roleRoutes = [
            'администратор' => 'requests',
            'диспетчер' => 'requests',
            'инспектор' => 'requests',
            'техник' => 'routelist.index',
            'кладовщик' => 'inventory.index',
            'просмотр маршрута' => 'routing',
            'просмотр заявок' => 'storyByGroup',
            'супервизер' => 'users.index',
        ];

        foreach ($roles as $role) {
            if (isset($roleRoutes[$role])) {
                $routeName = $roleRoutes[$role];
                return redirect()->route($routeName);
            }
        }

        // Handle the case when none of the roles match any predefined routes
        return response()->view('errors.403', [], 403);
    }


    public function settings()
    {
        return view('settings');
    }

    public function authById(Request $request)
    {
        Auth::loginUsingId($request->id);
        return redirect('/');
    }
}
