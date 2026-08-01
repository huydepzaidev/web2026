<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once __DIR__ . '/../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
}
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.']);
    } else {
        echo "<!DOCTYPE html><html><head><title>Lỗi</title></head><body><h1>Lỗi kết nối cơ sở dữ liệu.</h1><p>Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p></body></html>";
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['user_id'])) {
    $session_stmt = $pdo->prepare("SELECT is_admin FROM account WHERE id = :id AND ban = 0 LIMIT 1");
    $session_stmt->execute([':id' => (int) $_SESSION['user_id']]);
    $session_user = $session_stmt->fetch();
    header('Location: ' . ($session_user && (int) $session_user['is_admin'] === 1
        ? webgoc_url('admin/')
        : webgoc_url('forum')));
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = trim($_POST['user'] ?? '');
        $password = $_POST['pass'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập và mật khẩu không được để trống.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT id, username, password, is_admin, ban FROM account WHERE username = :username AND password = :password");
            $stmt->execute([
                ':username' => $username,
                ':password' => $password
            ]);
            $user = $stmt->fetch();

            if ($user && (int) $user['ban'] === 0) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (int) $user['is_admin'];
                $update_stmt = $pdo->prepare("UPDATE account SET last_time_login = NOW(), ip_address = :ip_address WHERE id = :id");
                $update_stmt->execute([
                    ':ip_address' => $_SERVER['REMOTE_ADDR'],
                    ':id' => $user['id']
                ]);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đăng nhập thành công! Chúc bạn chơi game vui vẻ.',
                    'redirect' => (int) $user['is_admin'] === 1
                        ? webgoc_url('admin/')
                        : webgoc_url('forum')
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
            } elseif ($user && (int) $user['ban'] === 1) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.']);
                exit();
            }
        } catch (PDOException $e) {
            error_log("Lỗi đăng nhập: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi khi đăng nhập. Vui lòng thử lại.']);
            exit();
        }
    } elseif ($action === 'register') {
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
            // Kiểm tra tên đăng nhập đã tồn tại
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.']);
                exit();
            }

            // Thêm tài khoản mới vào database
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
            echo json_encode(['status' => 'success', 'message' => 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay bây giờ.']);
            exit();
        } catch (PDOException $e) {
            error_log("Lỗi đăng ký: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
        exit();
    }
}
?>
