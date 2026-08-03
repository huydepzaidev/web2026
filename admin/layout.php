<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function admin_icon(string $name): string
{
    $icons = [
        'dashboard' => '<path d="M4 13h6V3H4v10Zm0 8h6v-6H4v6Zm10 0h6V11h-6v10Zm0-18v6h6V3h-6Z"/>',
        'users' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3ZM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/>',
        'posts' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14l4-4h12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2Zm-2 8H7V9h10v2Zm0-3H7V6h10v2Z"/>',
        'money' => '<path d="M21 4H3c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h18c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2Zm0 14H3V6h18v12Zm-9-1c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5Zm0-8c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3Z"/>',
        'gift' => '<path d="M20 6h-2.18A3 3 0 0 0 12 5a3 3 0 0 0-5.82 1H4a2 2 0 0 0-2 2v3h9V8h2v3h9V8a2 2 0 0 0-2-2ZM9 6a1 1 0 1 1 1-1 1 1 0 0 1-1 1Zm6 0a1 1 0 1 1 1-1 1 1 0 0 1-1 1ZM3 13v7a2 2 0 0 0 2 2h6v-9H3Zm10 9h6a2 2 0 0 0 2-2v-7h-8v9Z"/>',
        'settings' => '<path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.07-.94l2.03-1.58-1.92-3.32-2.39.96a7.18 7.18 0 0 0-1.62-.94L14.87 3h-3.84l-.36 3.18c-.59.24-1.13.56-1.62.94l-2.39-.96-1.92 3.32 2.03 1.58c-.05.31-.08.65-.08.94s.03.63.08.94l-2.03 1.58 1.92 3.32 2.39-.96c.49.38 1.03.7 1.62.94l.36 3.18h3.84l.36-3.18c.59-.24 1.13-.56 1.62-.94l2.39.96 1.92-3.32-2.03-1.58ZM13 15.5A3.5 3.5 0 1 1 13 8a3.5 3.5 0 0 1 0 7.5Z"/>',
        'server' => '<path d="M4 3h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm0 10h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2Zm2-7v2h2V6H6Zm0 10v2h2v-2H6Zm4-10v2h8V6h-8Zm0 10v2h8v-2h-8Z"/>',
        'events' => '<path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2V2Zm12 8H5v10h14V10ZM5 8h14V6h-2v1h-2V6H9v1H7V6H5v2Zm2 4h4v3H7v-3Z"/>',
        'logs' => '<path d="M13 3a9 9 0 1 0 8.95 10H20a7 7 0 1 1-2.05-4.95L15 11h7V4l-2.63 2.63A8.96 8.96 0 0 0 13 3Zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12Z"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';
}

function admin_render_header(string $title, string $active = 'dashboard', string $subtitle = ''): void
{
    global $admin_user;
    $nav = [
        'dashboard' => ['index.php', 'Tổng quan', 'dashboard'],
        'accounts' => ['accounts.php', 'Tài khoản & nhân vật', 'users'],
        'posts' => ['posts.php', 'Bài viết', 'posts'],
        'transactions' => ['transactions.php', 'Nạp tiền', 'money'],
        'giftcodes' => ['giftcodes.php', 'Giftcode', 'gift'],
        'game-server' => ['game-server.php', 'Vận hành game server', 'server'],
        'events' => ['events.php', 'Control panel sự kiện', 'events'],
        'settings' => ['settings.php', 'Cấu hình website', 'settings'],
        'logs' => ['logs.php', 'Nhật ký quản trị', 'logs'],
    ];
    $flash = admin_take_flash();
    ?>
    <!doctype html>
    <html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="robots" content="noindex,nofollow">
        <title><?= admin_escape($title) ?> · Control Panel</title>
        <link rel="icon" href="<?= admin_escape(webgoc_url('images/favicon-32x32.png')) ?>">
        <link rel="stylesheet" href="<?= admin_escape(admin_url('assets/admin.css?v=20260802-events')) ?>">
    </head>
    <body>
    <div class="admin-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="<?= admin_escape(admin_url()) ?>">
                <span class="brand-mark">7</span>
                <span><strong>NGỌC RỒNG</strong><small>CONTROL PANEL</small></span>
            </a>
            <nav class="side-nav" aria-label="Điều hướng quản trị">
                <p class="nav-label">QUẢN TRỊ</p>
                <?php foreach ($nav as $key => [$href, $label, $icon]): ?>
                    <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= admin_escape(admin_url($href)) ?>">
                        <?= admin_icon($icon) ?><span><?= admin_escape($label) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="side-footer">
                <div class="admin-avatar"><?= admin_escape(mb_strtoupper(mb_substr($admin_user['username'], 0, 1))) ?></div>
                <div><strong><?= admin_escape($admin_user['username']) ?></strong><small>Quản trị viên</small></div>
                <a class="logout-icon" href="<?= admin_escape(webgoc_url('app/logout.php')) ?>" title="Đăng xuất">↗</a>
            </div>
        </aside>
        <div class="main-area">
            <header class="topbar">
                <button class="menu-toggle" type="button" data-sidebar-toggle aria-label="Mở menu">☰</button>
                <div class="page-heading">
                    <h1><?= admin_escape($title) ?></h1>
                    <?php if ($subtitle !== ''): ?><p><?= admin_escape($subtitle) ?></p><?php endif; ?>
                </div>
                <div class="top-actions">
                    <a class="view-site" href="<?= admin_escape(webgoc_url()) ?>" target="_blank" rel="noopener">Xem website ↗</a>
                    <span class="status-dot"><i></i> Hệ thống online</span>
                </div>
            </header>
            <main class="content">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= admin_escape($flash['type']) ?>" role="alert">
                        <span><?= $flash['type'] === 'success' ? '✓' : '!' ?></span>
                        <?= admin_escape($flash['message']) ?>
                        <button type="button" data-dismiss>×</button>
                    </div>
                <?php endif; ?>
    <?php
}

function admin_render_footer(): void
{
    ?>
            </main>
        </div>
    </div>
    <div class="sidebar-overlay" data-sidebar-toggle></div>
    <script src="<?= admin_escape(admin_url('assets/admin.js?v=20260731')) ?>"></script>
    </body>
    </html>
    <?php
}

function admin_pagination(int $page, int $pages): void
{
    if ($pages <= 1) {
        return;
    }
    ?>
    <nav class="pagination" aria-label="Phân trang">
        <a class="<?= $page <= 1 ? 'disabled' : '' ?>" href="<?= admin_escape(admin_query_url(['page' => max(1, $page - 1)])) ?>">←</a>
        <?php
        $start = max(1, $page - 2);
        $end = min($pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
            <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= admin_escape(admin_query_url(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="<?= $page >= $pages ? 'disabled' : '' ?>" href="<?= admin_escape(admin_query_url(['page' => min($pages, $page + 1)])) ?>">→</a>
    </nav>
    <?php
}
