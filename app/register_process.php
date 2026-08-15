<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
// Endpoint tra ve JSON: khong in loi ra output, neu khong PHP se chen HTML
// vao giua JSON va jQuery se bao "loi ket noi" du dang ky da chay xong.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
require_once __DIR__ . '/../connect.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . webgoc_url('forum'));
    exit();
}
header('Content-Type: application/json');
$host = $ip_sv;
$dbname = $dbname_sv;
$user = $user_sv;
$pass = $pass_sv;
$pdo = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $username = trim($_POST['user'] ?? '');
    $password = $_POST['pass'] ?? '';
    $rePassword = $_POST['repass'] ?? '';
    $server = $_POST['server'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $email = '';
    if (empty($username) || empty($password) || empty($rePassword) || empty($server)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc (Tài Khoản, Mật khẩu, Nhập lại Mật khẩu, và Server).']);
        exit();
    }
    if ($password !== $rePassword) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp.']);
        exit();
    }
    if (strlen($username) < 3 || strlen($username) > 20) {
        echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập phải có từ 3 đến 20 ký tự.']);
        exit();
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.']);
        exit();
    }
    if (strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.']);
            exit();
        }
        $stmt = $pdo->prepare("INSERT INTO account (
            username, password, email, create_time, update_time, ban, is_admin,
            last_time_login, last_time_logout, ip_address, active, thoi_vang,
            server_login, bd_player, is_gift_box, gift_time, reward, vnd,
            tongnap, token, xsrf_token, newpass, luotquay, vang, event_point,
            vip, tichdiem, point_post, last_post, gioithieu, xacnhan_gioitheu,
            baiviet, xacminh
        ) VALUES (
            :username, :password, :email, NOW(), NOW(), 0, 0,
            '2002-07-31 00:00:00', '2002-07-31 00:00:00', :ip_address, 1, 0,
            :server_login, 1, 0, '0', NULL, 0,
            0, '', '', '', 0, 0, 0,
            0, 0, 0, 0, NULL, 0,
            0, 0
        )");

        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':ip_address' => $ip_address,
            ':server_login' => $server
        ]);
        echo json_encode([
                    'status' => 'success',
                    'message' => 'Đăng ký thành công!',
                    'redirect' => webgoc_url('forum')
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
    } catch (PDOException $e) {
        error_log("Lỗi đăng ký: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
    exit();
}
?>
