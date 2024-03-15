<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private CaptchaService $captchaService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CaptchaService $service)
    {
        $this->middleware('guest')->except('logout');
        $this->captchaService = $service;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }

    public function validateCaptcha(Request $request): JsonResponse
    {
        $captchaCode = Session::get('captcha_code');
        $userInputCaptcha = $request->input('captchaInput');

        $isValid = ($userInputCaptcha === $captchaCode);

        return response()->json(['valid' => $isValid]);
    }

    public function showLoginForm()
    {
        $data = [];
        if(env('APP_ENV') != 'dev' && env('APP_ENV') != 'stage'){
            $data = $this->captchaService->generateCaptcha();
        }
        return view('auth.login', $data);
    }
}
