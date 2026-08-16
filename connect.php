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

// Load website settings from DB
$web_settings = [];
$settings_result = $conn->query("SELECT * FROM settings LIMIT 1");
if ($settings_result && ($row = $settings_result->fetch_assoc())) {
    $web_settings = $row;
}

if (!empty($web_settings['Zalo'])) {
    $box_zalo_url = $web_settings['Zalo'];
}
if (!empty($web_settings['IPhone'])) {
    $testflight_url = $web_settings['IPhone'];
}

$download_android = !empty(trim((string) ($web_settings['Android'] ?? ''))) ? trim((string) $web_settings['Android']) : webgoc_url('down/NRO.apk');
$download_windows = !empty(trim((string) ($web_settings['Windows'] ?? ''))) ? trim((string) $web_settings['Windows']) : webgoc_url('down/PC.rar');
$download_iphone = !empty(trim((string) ($web_settings['IPhone'] ?? ''))) ? trim((string) $web_settings['IPhone']) : $testflight_url;
$download_java = !empty(trim((string) ($web_settings['Java'] ?? ''))) ? trim((string) $web_settings['Java']) : webgoc_url('down/JAR.jar');

if (!function_exists('is_external_url')) {
    function is_external_url(?string $url): bool {
        if (!$url) {
            return false;
        }
        return preg_match('~^(?:https?:)?//~i', $url) === 1;
    }
}
?>

