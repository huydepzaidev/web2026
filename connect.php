<?php
// connect.php
require_once __DIR__ . '/base_path.php';

$local_config_file = __DIR__ . '/config.local.php';
$local_config = is_file($local_config_file) ? require $local_config_file : [];

$get_config = static function (string $key, string $default = '') use ($local_config): string {
    $environment_value = getenv($key);
    if ($environment_value !== false && $environment_value !== '') {
        return $environment_value;
    }

    return isset($local_config[$key]) ? (string) $local_config[$key] : $default;
};

$ip_sv = $get_config('DB_HOST', 'localhost');
$dbname_sv = $get_config('DB_NAME');
$user_sv = $get_config('DB_USER');
$pass_sv = $get_config('DB_PASS');

// Legacy aliases: keep every old file using the same DB config from this file.
$db_host = $ip_sv;
$db_name = $dbname_sv;
$db_user = $user_sv;
$db_pass = $pass_sv;

$testflight_url = 'https://testflight.apple.com/join/FWEJoZEB';
$box_zalo_url = 'https://zalo.me/g/njvxgh490';

$thesieure_url = 'https://thesieure.com/chargingws/v2';
$thesieure_partner_id = $get_config('THESIEURE_PARTNER_ID');
$thesieure_partner_key = $get_config('THESIEURE_PARTNER_KEY');
$sepay_webhook_secret = $get_config('SEPAY_WEBHOOK_SECRET');


$conn = new mysqli($ip_sv, $user_sv, $pass_sv, $dbname_sv);

if ($conn->connect_error) {
    die("Lỗi kết nối database: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
