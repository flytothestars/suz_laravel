<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    private AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $token = $this->respondWithToken(auth('api')->attempt($credentials));

        if (!$token->getData()->access_token) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'token' => $token
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function refreshToken(): JsonResponse
    {
        $token = $this->respondWithToken(auth('api')->refresh());
        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->service->reset($request);
        return response()->json($result);
    }
}
