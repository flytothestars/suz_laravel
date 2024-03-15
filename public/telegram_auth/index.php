<?php
  if (isset($_GET['hash'])) {
    echo "Logged in as " . $_GET['first_name'] . " " .
      $_GET['last_name'] . " (" . $_GET['id'] .
      (isset($_GET['username']) ? ",@" . $_GET['username'] : "") . ")";
  }

  function checkTelegramAuthorization($auth_data) {
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
      $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    $secret_key = hash('sha256', env('TELEGRAM_BOT_TOKEN'), true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);
    if (strcmp($hash, $check_hash) !== 0) {
      throw new Exception('Data is NOT from Telegram');
    }
    if ((time() - $auth_data['auth_date']) > 86400) {
      throw new Exception('Data is outdated');
    }
    return $auth_data;
  }

  if (isset($_GET['hash'])) {
      try {
          $auth_data = checkTelegramAuthorization($_GET);
          echo "Hello, " . $auth_data['first_name'];
      } catch (Exception $e) {
          die ($e->getMessage());
      }
  }
?>
