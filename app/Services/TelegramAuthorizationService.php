<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;

class TelegramAuthorizationService
{
    private $bot_token;

    public function __construct()
    {
        $this->bot_token = env('TELEGRAM_BOT_TOKEN');
    }

    /**
     * @throws Exception
     */
    public function authorizeUser($auth_data): ?string
    {
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);

        $data_check_arr = [];
        foreach ($auth_data->query as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }

        sort($data_check_arr);

        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->bot_token, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if ($hash !== $check_hash) {
            throw new Exception('Data is not from Telegram');
        }

        if (time() - $auth_data['auth_date'] > 86400) {
            throw new Exception('Data is outdated');
        }

        $user = Auth::user();

        if (!$user->telegram_id) {
            $user->telegram_id = $auth_data['id'];
            $saved = $user->save();

            if (!$saved) {
                return 'Что-то пошло не так, попробуйте еще раз.';
            } else {
                return 'Регистрация прошла успешно.';
            }
        }

        return null;
    }
}
