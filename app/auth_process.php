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
        // Luong dang ky chi ton tai o register_process.php.
        // Truoc day file nay giu mot ban sao thu hai va ghi active khac nhau,
        // khien tai khoan tao ra bi lech tuy theo endpoint duoc goi.
        require __DIR__ . '/register_process.php';
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
        exit();
    }
}
?>
