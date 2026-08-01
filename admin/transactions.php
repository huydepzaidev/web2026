<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$tab = (string) ($_GET['tab'] ?? 'payments');
if (!in_array($tab, ['payments', 'bank', 'cards'], true)) {
    $tab = 'payments';
}
$search = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$where = [];
$types = '';
$params = [];

if ($tab === 'payments') {
    if ($search !== '') {
        $where[] = '(name LIKE ? OR refNo LIKE ? OR card_serial LIKE ?)';
        $term = '%' . $search . '%';
        $types .= 'sss';
        array_push($params, $term, $term, $term);
    }
    if ($status === 'credited') $where[] = 'is_credited = 1';
    if ($status === 'pending') $where[] = 'is_credited = 0';
    $table = 'payments';
} elseif ($tab === 'bank') {
    if ($search !== '') {
        $where[] = '(username LIKE ? OR transaction_id LIKE ? OR description LIKE ?)';
        $term = '%' . $search . '%';
        $types .= 'sss';
        array_push($params, $term, $term, $term);
    }
    if ($status === 'credited') $where[] = 'is_credited = 1';
    if ($status === 'pending') $where[] = 'is_credited = 0';
    $table = 'bank_transfers';
} else {
    if ($search !== '') {
        $where[] = '(user_nap LIKE ? OR serial LIKE ? OR request_id LIKE ?)';
        $term = '%' . $search . '%';
        $types .= 'sss';
        array_push($params, $term, $term, $term);
    }
    if ($status === 'credited') $where[] = 'status = 1';
    if ($status === 'pending') $where[] = 'status <> 1';
    $table = 'napthe';
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) admin_scalar("SELECT COUNT(*) FROM `$table`" . $whereSql, $types, $params);
[$page, $pages, $offset, $perPage] = admin_page($total);
$rows = admin_all("SELECT * FROM `$table`" . $whereSql . ' ORDER BY id DESC LIMIT ? OFFSET ?', $types . 'ii', array_merge($params, [$perPage, $offset]));

$totals = [
    'payments' => (float) admin_scalar('SELECT COALESCE(SUM(final_credited_amount),0) FROM payments WHERE is_credited = 1'),
    'bank' => (float) admin_scalar('SELECT COALESCE(SUM(amount),0) FROM bank_transfers WHERE is_credited = 1'),
    'cards' => (float) admin_scalar('SELECT COALESCE(SUM(amount),0) FROM napthe WHERE status = 1'),
    'pending' => (int) admin_scalar('SELECT COUNT(*) FROM payments WHERE is_credited = 0') +
        (int) admin_scalar('SELECT COUNT(*) FROM bank_transfers WHERE is_credited = 0') +
        (int) admin_scalar('SELECT COUNT(*) FROM napthe WHERE status <> 1'),
];

admin_render_header('Nạp tiền & giao dịch', 'transactions', 'Theo dõi lịch sử thanh toán từ mọi kênh');
?>
<section class="metric-grid">
    <article class="metric-card"><span class="metric-icon green">₫</span><div><small>THẺ ĐÃ CỘNG</small><strong><?= admin_number($totals['payments']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon blue">↔</span><div><small>CHUYỂN KHOẢN ĐÃ CỘNG</small><strong><?= admin_number($totals['bank']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon orange">▣</span><div><small>NẠP THẺ CŨ</small><strong><?= admin_number($totals['cards']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon red">!</span><div><small>CHƯA CỘNG / LỖI</small><strong><?= admin_number($totals['pending']) ?></strong></div></article>
</section>
<nav class="tabs">
    <a class="<?= $tab === 'payments' ? 'active' : '' ?>" href="?tab=payments">Thanh toán thẻ</a>
    <a class="<?= $tab === 'bank' ? 'active' : '' ?>" href="?tab=bank">Chuyển khoản</a>
    <a class="<?= $tab === 'cards' ? 'active' : '' ?>" href="?tab=cards">Nạp thẻ cũ</a>
</nav>
<form class="toolbar" method="get">
    <input type="hidden" name="tab" value="<?= admin_escape($tab) ?>">
    <div class="search-box"><input type="search" name="q" value="<?= admin_escape($search) ?>" placeholder="Tài khoản, mã giao dịch, serial..."><button type="submit">Tìm kiếm</button></div>
    <select class="filter-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option><option value="credited" <?= $status === 'credited' ? 'selected' : '' ?>>Đã cộng</option><option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chưa cộng / lỗi</option></select>
    <span class="count"><?= admin_number($total) ?> giao dịch</span>
</form>
<section class="panel">
    <div class="table-wrap"><table class="data-table">
        <?php if ($tab === 'payments'): ?>
            <thead><tr><th>ID / tài khoản</th><th>Nhà mạng</th><th>Khai báo</th><th>Thực nhận</th><th>Trạng thái</th><th>Thời gian</th></tr></thead><tbody>
            <?php foreach ($rows as $row): ?><tr><td><strong><?= admin_escape($row['name']) ?></strong><br><small class="mono text-muted">#<?= (int) $row['id'] ?> · <?= admin_escape($row['refNo']) ?></small></td><td><?= admin_escape($row['card_telco']) ?><br><small class="mono text-muted"><?= admin_escape($row['card_serial']) ?></small></td><td><?= admin_number($row['declared_amount']) ?>đ</td><td><strong><?= admin_number($row['final_credited_amount']) ?>đ</strong></td><td><?= (int) $row['is_credited'] === 1 ? '<span class="badge badge-green">Đã cộng</span>' : '<span class="badge badge-red">' . admin_escape($row['status_text'] ?: 'Chưa cộng') . '</span>' ?></td><td class="nowrap"><?= admin_escape(admin_datetime($row['date'])) ?></td></tr><?php endforeach; ?>
        <?php elseif ($tab === 'bank'): ?>
            <thead><tr><th>Mã giao dịch</th><th>Tài khoản</th><th>Số tiền</th><th>Nội dung</th><th>Trạng thái</th><th>Thời gian</th></tr></thead><tbody>
            <?php foreach ($rows as $row): ?><tr><td class="mono"><?= admin_escape($row['transaction_id']) ?></td><td><strong><?= admin_escape($row['username']) ?></strong><br><small class="text-muted"><?= admin_escape($row['sender_bank_name']) ?></small></td><td><strong><?= admin_number($row['amount']) ?>đ</strong></td><td><?= admin_escape(mb_strimwidth($row['description'], 0, 70, '…')) ?></td><td><?= (int) $row['is_credited'] === 1 ? '<span class="badge badge-green">Đã cộng</span>' : '<span class="badge badge-orange">' . admin_escape($row['status']) . '</span>' ?></td><td class="nowrap"><?= admin_escape(admin_datetime($row['created_at'])) ?></td></tr><?php endforeach; ?>
        <?php else: ?>
            <thead><tr><th>Tài khoản</th><th>Nhà mạng</th><th>Serial</th><th>Mệnh giá</th><th>Trạng thái</th><th>Thời gian</th></tr></thead><tbody>
            <?php foreach ($rows as $row): ?><tr><td><strong><?= admin_escape($row['user_nap']) ?></strong><br><small class="mono text-muted"><?= admin_escape($row['request_id']) ?></small></td><td><?= admin_escape($row['telco']) ?></td><td class="mono"><?= admin_escape($row['serial']) ?></td><td><strong><?= admin_number($row['amount']) ?>đ</strong></td><td><?= (int) $row['status'] === 1 ? '<span class="badge badge-green">Thành công</span>' : '<span class="badge badge-orange">Đang xử lý / lỗi</span>' ?></td><td class="nowrap"><?= admin_escape(admin_datetime($row['created_at'])) ?></td></tr><?php endforeach; ?>
        <?php endif; ?>
        <?php if (!$rows): ?><tr><td colspan="6"><div class="empty"><strong>Chưa có giao dịch</strong>Không có dữ liệu phù hợp với bộ lọc.</div></td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>
<?php admin_pagination($page, $pages); admin_render_footer(); ?>

