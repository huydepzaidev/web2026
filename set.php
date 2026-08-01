<?php
// set.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'connect.php';
include_once 'cauhinh.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
$_is_logged_in = false;
$_user_id_session = isset($_SESSION['id']) ? $_SESSION['id'] : null;
$_username_session = isset($_SESSION['account']) ? $_SESSION['account'] : null;
if ($_user_id_session === null || $_username_session === null) {
    $temp_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $temp_username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    if ($temp_user_id !== null && $temp_username !== null) {
        $_SESSION['id'] = $temp_user_id;
        $_SESSION['account'] = $temp_username;
        $_user_id_session = $_SESSION['id'];
        $_username_session = $_SESSION['account'];
    }
}
$_username = "Khách";
$_password = "";
$_gioithieu = "";
$_admin = 0;
$_coin = 0;
$_tcoin = 0;
$_status = 0;
$isPremium_name = '<span style="color:#007BFF;font-weight: bold;"><a href="/active">Kích hoạt ngay</a></span>';
if ($_username_session !== null && $_user_id_session !== null) {
    if (isset($conn) && $conn instanceof mysqli) {
        $stmt = $conn->prepare("SELECT id, username, password, gioithieu, vnd, tongnap, active FROM account WHERE id = ? AND username = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("is", $_user_id_session, $_username_session);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_arr = $result->fetch_assoc();
            $stmt->close();

            if ($user_arr) {
                $_is_logged_in = true;
                $_username = htmlspecialchars($user_arr['username'] ?? '');
                $_password = htmlspecialchars($user_arr['password'] ?? '');
                $_gioithieu = htmlspecialchars($user_arr['gioithieu'] ?? '');
                $_coin = $user_arr['vnd'];
                $_tcoin = htmlspecialchars($user_arr['tongnap'] ?? '');
                $_status = $user_arr['active'];
                switch ($_status) {
                    case '1':
                        $isPremium_name = '<span style="color:green;font-weight: bold;">Đã kích hoạt</span>';
                        break;
                    case '0':
                        $isPremium_name = '<span style="color:#007BFF;font-weight: bold;"><a href="/active">Kích hoạt ngay</a></span>';
                        break;
                    case '-1':
                        $isPremium_name = '<span style="color:red;font-weight: bold;">Đang bị khóa</span>';
                        break;
                }
            } else {
                session_destroy();
                header('Location: ' . webgoc_url('app/logout.php'));
                exit();
            }
        } else {
            error_log("Lỗi prepare statement trong set.php: " . $conn->error);
        }
    } else {
        error_log("Biến \$conn không tồn tại hoặc không phải đối tượng mysqli trong set.php.");
    }
}
if (isset($_GET['out'])) {
    session_destroy();
    header('Location: ' . webgoc_url());
    exit();
}
$_IP = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$_login = $_is_logged_in ? "on" : null;
?>
