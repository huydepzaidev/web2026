<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$search = trim((string) ($_GET['q'] ?? ''));
$where = '';
$types = '';
$params = [];
if ($search !== '') {
    $where = ' WHERE admin_username LIKE ? OR action_name LIKE ? OR target_type LIKE ? OR ip_address LIKE ?';
    $term = '%' . $search . '%';
    $types = 'ssss';
    $params = [$term, $term, $term, $term];
}
try {
    $total = (int) admin_scalar('SELECT COUNT(*) FROM admin_audit_log' . $where, $types, $params);
    [$page, $pages, $offset, $perPage] = admin_page($total, 30);
    $logs = admin_all(
        'SELECT * FROM admin_audit_log' . $where . ' ORDER BY id DESC LIMIT ? OFFSET ?',
        $types . 'ii',
        array_merge($params, [$perPage, $offset])
    );
} catch (Throwable) {
    $total = 0; $page = 1; $pages = 1; $logs = [];
}

admin_render_header('Nhật ký quản trị', 'logs', 'Theo dõi ai đã thay đổi gì và vào thời điểm nào');
?>
<form class="toolbar" method="get">
    <div class="search-box"><input type="search" name="q" value="<?= admin_escape($search) ?>" placeholder="Admin, hành động, đối tượng hoặc IP..."><button type="submit">Tìm kiếm</button></div>
    <span class="count"><?= admin_number($total) ?> hoạt động</span>
</form>
<section class="panel"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Thời gian</th><th>Quản trị viên</th><th>Hành động</th><th>Đối tượng</th><th>Địa chỉ IP</th><th>Chi tiết</th></tr></thead><tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td class="nowrap"><?= admin_escape(admin_datetime($log['created_at'])) ?></td>
            <td><div class="cell-user"><span class="mini-avatar"><?= admin_escape(mb_strtoupper(mb_substr($log['admin_username'], 0, 1))) ?></span><strong><?= admin_escape($log['admin_username']) ?></strong></div></td>
            <td><strong><?= admin_escape($log['action_name']) ?></strong></td>
            <td><span class="badge badge-blue"><?= admin_escape($log['target_type']) ?><?= $log['target_id'] !== null ? ' #' . (int) $log['target_id'] : '' ?></span></td>
            <td class="mono text-muted"><?= admin_escape($log['ip_address']) ?></td>
            <td><code class="text-muted"><?= admin_escape(mb_strimwidth((string) $log['detail_json'], 0, 90, '…')) ?></code></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$logs): ?><tr><td colspan="6"><div class="empty"><strong>Chưa có nhật ký</strong>Hoạt động quản trị mới sẽ được lưu tại đây.</div></td></tr><?php endif; ?>
    </tbody>
</table></div></section>
<?php admin_pagination($page, $pages); admin_render_footer(); ?>

