<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/connect.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');

function admin_url(string $path = ''): string
{
    return webgoc_url('admin/' . ltrim($path, '/'));
}

function admin_escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_redirect(string $path = ''): never
{
    header('Location: ' . admin_url($path));
    exit;
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function admin_take_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return is_array($flash) ? $flash : null;
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf'];
}

function admin_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        admin_escape(admin_csrf_token()) . '">';
}

function admin_require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    $token = (string) ($_POST['csrf_token'] ?? '');
    if ($token === '' || !hash_equals(admin_csrf_token(), $token)) {
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
    }
}

function admin_bind(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '') {
        return;
    }
    $stmt->bind_param($types, ...$params);
}

function admin_all(string $sql, string $types = '', array $params = []): array
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị truy vấn.');
    }
    admin_bind($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function admin_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = admin_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function admin_scalar(string $sql, string $types = '', array $params = []): mixed
{
    $row = admin_one($sql, $types, $params);
    return $row ? array_values($row)[0] : null;
}

function admin_execute(string $sql, string $types = '', array $params = []): int
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị truy vấn.');
    }
    admin_bind($stmt, $types, $params);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

function admin_audit(string $action, string $targetType, ?int $targetId, array $detail = []): void
{
    global $conn, $admin_user;
    static $available = null;
    if ($available === null) {
        $result = $conn->query("SHOW TABLES LIKE 'admin_audit_log'");
        $available = $result && $result->num_rows > 0;
    }
    if (!$available) {
        return;
    }
    $stmt = $conn->prepare(
        'INSERT INTO admin_audit_log
         (admin_id, admin_username, action_name, target_type, target_id, detail_json, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return;
    }
    $adminId = (int) ($admin_user['id'] ?? 0);
    $adminName = (string) ($admin_user['username'] ?? '');
    $json = json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $stmt->bind_param('isssiss', $adminId, $adminName, $action, $targetType, $targetId, $json, $ip);
    $stmt->execute();
    $stmt->close();
}

function admin_number(mixed $value): string
{
    return number_format((float) $value, 0, ',', '.');
}

function admin_datetime(?string $value): string
{
    if (!$value || str_starts_with($value, '2002-')) {
        return 'Chưa có';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $value;
}

function admin_page(int $total, int $perPage = 20): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
    return [$page, $pages, ($page - 1) * $perPage, $perPage];
}

function admin_query_url(array $changes): string
{
    $query = array_merge($_GET, $changes);
    foreach ($query as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        }
    }
    return '?' . http_build_query($query);
}

if (empty($_SESSION['user_id'])) {
    header('Location: ' . webgoc_url('app/login.php') . '?nav=' . rawurlencode(admin_url()));
    exit;
}

$admin_user = admin_one(
    'SELECT id, username, is_admin, ban, active, last_time_login
     FROM account WHERE id = ? LIMIT 1',
    'i',
    [(int) $_SESSION['user_id']]
);

if (!$admin_user || (int) $admin_user['is_admin'] !== 1 || (int) $admin_user['ban'] === 1) {
    unset($_SESSION['is_admin']);
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Không có quyền truy cập</title>
        <style>
            body{margin:0;background:#0b1020;color:#e8edf7;font:16px/1.5 system-ui;display:grid;place-items:center;min-height:100vh}
            main{max-width:430px;text-align:center;padding:36px;background:#151c31;border:1px solid #2a3555;border-radius:18px}
            a{display:inline-block;margin-top:12px;color:#fff;background:#ef6c37;padding:10px 16px;border-radius:9px;text-decoration:none}
        </style>
    </head>
    <body><main><h1>Không có quyền truy cập</h1><p>Tài khoản này không có quyền quản trị website.</p><a href="<?= admin_escape(webgoc_url('forum')) ?>">Quay lại website</a></main></body>
    </html>
    <?php
    exit;
}

$_SESSION['is_admin'] = 1;
$_SESSION['username'] = $admin_user['username'];
