<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$metrics = [
    'accounts' => (int) admin_scalar('SELECT COUNT(*) FROM account'),
    'players' => (int) admin_scalar('SELECT COUNT(*) FROM player'),
    'posts' => (int) admin_scalar('SELECT COUNT(*) FROM posts'),
    'banned' => (int) admin_scalar('SELECT COUNT(*) FROM account WHERE ban = 1'),
    'today' => (int) admin_scalar('SELECT COUNT(*) FROM account WHERE DATE(create_time) = CURDATE()'),
    'admins' => (int) admin_scalar('SELECT COUNT(*) FROM account WHERE is_admin = 1'),
];

$credited = (float) admin_scalar(
    'SELECT COALESCE(SUM(final_credited_amount), 0) FROM payments WHERE is_credited = 1'
);
$bankCredited = (float) admin_scalar(
    'SELECT COALESCE(SUM(amount), 0) FROM bank_transfers WHERE is_credited = 1'
);
$metrics['revenue'] = $credited + $bankCredited;

$recentAccounts = admin_all(
    'SELECT a.id, a.username, a.create_time, a.last_time_login, a.is_admin, a.ban, p.name AS player_name
     FROM account a LEFT JOIN player p ON p.account_id = a.id
     ORDER BY a.id DESC LIMIT 7'
);
$recentPosts = admin_all(
    'SELECT id, tieude, username, ghimbai, created_at FROM posts ORDER BY id DESC LIMIT 5'
);
$auditLogs = [];
try {
    $auditLogs = admin_all(
        'SELECT admin_username, action_name, target_type, created_at
         FROM admin_audit_log ORDER BY id DESC LIMIT 6'
    );
} catch (Throwable) {
    $auditLogs = [];
}

admin_render_header('Tổng quan', 'dashboard', 'Toàn cảnh hoạt động của máy chủ và website');
?>
<section class="metric-grid">
    <article class="metric-card"><span class="metric-icon orange">◎</span><div><small>TỔNG TÀI KHOẢN</small><strong><?= admin_number($metrics['accounts']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon blue">♟</span><div><small>NHÂN VẬT</small><strong><?= admin_number($metrics['players']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon green">₫</span><div><small>DOANH THU ĐÃ CỘNG</small><strong><?= admin_number($metrics['revenue']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon red">!</span><div><small>TÀI KHOẢN BỊ KHÓA</small><strong><?= admin_number($metrics['banned']) ?></strong></div></article>
</section>

<div class="grid-2">
    <section class="panel">
        <div class="panel-head"><div><h2>Tài khoản mới nhất</h2><p><?= $metrics['today'] ?> tài khoản đăng ký hôm nay · <?= $metrics['admins'] ?> quản trị viên</p></div><span class="spacer"></span><a class="link-more" href="<?= admin_escape(admin_url('accounts.php')) ?>">Xem tất cả →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Tài khoản</th><th>Trạng thái</th><th>Ngày tạo</th><th>Lần cuối đăng nhập</th></tr></thead>
                <tbody>
                <?php foreach ($recentAccounts as $account): ?>
                    <tr>
                        <td><div class="cell-user"><span class="mini-avatar"><?= admin_escape(mb_strtoupper(mb_substr($account['username'], 0, 1))) ?></span><div><strong><?= admin_escape($account['username']) ?></strong><small><?= admin_escape($account['player_name'] ?: 'Chưa tạo nhân vật') ?></small></div></div></td>
                        <td>
                            <?php if ((int) $account['ban'] === 1): ?><span class="badge badge-red">Đã khóa</span>
                            <?php elseif ((int) $account['is_admin'] === 1): ?><span class="badge badge-orange">Admin</span>
                            <?php else: ?><span class="badge badge-green">Hoạt động</span><?php endif; ?>
                        </td>
                        <td class="nowrap"><?= admin_escape(admin_datetime($account['create_time'])) ?></td>
                        <td class="nowrap text-muted"><?= admin_escape(admin_datetime($account['last_time_login'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentAccounts): ?><tr><td colspan="4"><div class="empty">Chưa có tài khoản.</div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="stack">
        <section class="panel">
            <div class="panel-head"><div><h2>Thao tác nhanh</h2><p>Các công việc quản trị thường dùng</p></div></div>
            <div class="panel-body quick-list">
                <a class="quick-item" href="<?= admin_escape(admin_url('accounts.php')) ?>"><span class="quick-icon">♟</span><span><strong>Quản lý tài khoản</strong><small>Khóa, thành viên, số dư và quyền</small></span><b>›</b></a>
                <a class="quick-item" href="<?= admin_escape(admin_url('posts.php?create=1')) ?>"><span class="quick-icon">✎</span><span><strong>Đăng thông báo mới</strong><small>Tạo và ghim bài lên diễn đàn</small></span><b>›</b></a>
                <a class="quick-item" href="<?= admin_escape(admin_url('giftcodes.php?create=1')) ?>"><span class="quick-icon">⌘</span><span><strong>Tạo giftcode</strong><small>Phát hành mã quà tặng mới</small></span><b>›</b></a>
                <a class="quick-item" href="<?= admin_escape(admin_url('mailboxes.php?create=1')) ?>"><span class="quick-icon">✉</span><span><strong>Trao quà ngay</strong><small>Gửi quà Admin bất kỳ lúc nào, không cần chờ chốt Top</small></span><b>›</b></a>
                <a class="quick-item" href="<?= admin_escape(admin_url('settings.php')) ?>"><span class="quick-icon">⚙</span><span><strong>Cấu hình website</strong><small>Tên server, liên hệ và tải game</small></span><b>›</b></a>
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Hoạt động gần đây</h2><p>Nhật ký thao tác quản trị</p></div><span class="spacer"></span><a class="link-more" href="<?= admin_escape(admin_url('logs.php')) ?>">Chi tiết</a></div>
            <div class="panel-body activity">
                <?php foreach ($auditLogs as $log): ?>
                    <div class="activity-item"><strong><?= admin_escape($log['admin_username']) ?></strong> · <?= admin_escape($log['action_name']) ?><small><?= admin_escape($log['target_type']) ?> · <?= admin_escape(admin_datetime($log['created_at'])) ?></small></div>
                <?php endforeach; ?>
                <?php if (!$auditLogs): ?><div class="empty"><strong>Chưa có hoạt động</strong>Nhật ký sẽ xuất hiện khi admin thực hiện thay đổi.</div><?php endif; ?>
            </div>
        </section>
    </div>
</div>

<section class="panel" style="margin-top:20px">
    <div class="panel-head"><div><h2>Bài viết mới</h2><p><?= $metrics['posts'] ?> bài viết trên diễn đàn</p></div><span class="spacer"></span><a class="link-more" href="<?= admin_escape(admin_url('posts.php')) ?>">Quản lý bài viết →</a></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Tiêu đề</th><th>Tác giả</th><th>Hiển thị</th><th>Thời gian</th></tr></thead><tbody>
    <?php foreach ($recentPosts as $post): ?><tr><td><strong><?= admin_escape($post['tieude']) ?></strong></td><td><?= admin_escape($post['username']) ?></td><td><?= (int) $post['ghimbai'] === 1 ? '<span class="badge badge-orange">Đang ghim</span>' : '<span class="badge badge-gray">Bình thường</span>' ?></td><td class="nowrap text-muted"><?= admin_escape(admin_datetime($post['created_at'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php admin_render_footer(); ?>
