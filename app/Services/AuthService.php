<?php

namespace App\Services;

use Adldap\Laravel\Facades\Adldap;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function reset(ResetPasswordRequest $request): array
    {
        $validated = $request->validated();

        $email = $validated['email'];
        $oldPassword = $validated['old_password'];
        $newPassword = $validated['password'];

        $message = [
            'success' => false,
            'message' => 'Пароль не изменился!.'
        ];

        try {
            $user = Adldap::search()->where('mail', '=', $email)->first();

            if (isset($user) && $user) {
                $result = $user->changePassword($oldPassword, $newPassword);
                $message = [
                    'success' => true,
                    'message' => 'Пароль успешно сменен.',
                    'result' => $result,
                ];
                Log::info($message['message'], ['data' => $message]);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), ['data' => $exception->getTrace()]);
            $message['error'] = $exception->getMessage();
        }

        return $message;
    }
}
