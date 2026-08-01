<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $targetId = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? '');
    $target = $targetId ? admin_one(
        'SELECT id, username, is_admin, ban, active, vnd, tongnap, vang, thoi_vang, event_point, vip, tichdiem
         FROM account WHERE id = ?',
        'i',
        [$targetId]
    ) : null;

    if (!$target) {
        admin_flash('error', 'Không tìm thấy tài khoản cần thao tác.');
        admin_redirect('accounts.php');
    }

    try {
        if ($action === 'toggle_ban') {
            if ((int) $target['id'] === (int) $admin_user['id']) {
                throw new RuntimeException('Bạn không thể tự khóa tài khoản đang đăng nhập.');
            }
            $newValue = (int) $target['ban'] === 1 ? 0 : 1;
            admin_execute('UPDATE account SET ban = ?, update_time = NOW() WHERE id = ?', 'ii', [$newValue, $targetId]);
            admin_audit($newValue ? 'Khóa tài khoản' : 'Mở khóa tài khoản', 'account', $targetId, ['username' => $target['username']]);
            admin_flash('success', $newValue ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
        } elseif ($action === 'toggle_active') {
            $newValue = (int) $target['active'] === 1 ? 0 : 1;
            admin_execute('UPDATE account SET active = ?, update_time = NOW() WHERE id = ?', 'ii', [$newValue, $targetId]);
            admin_audit($newValue ? 'Mở thành viên' : 'Hủy thành viên', 'account', $targetId, ['username' => $target['username']]);
            admin_flash('success', $newValue ? 'Đã mở thành viên.' : 'Đã hủy trạng thái thành viên.');
        } elseif ($action === 'toggle_admin') {
            if ((int) $target['id'] === (int) $admin_user['id']) {
                throw new RuntimeException('Bạn không thể tự gỡ quyền quản trị.');
            }
            $newValue = (int) $target['is_admin'] === 1 ? 0 : 1;
            if ($newValue === 0 && (int) admin_scalar('SELECT COUNT(*) FROM account WHERE is_admin = 1') <= 1) {
                throw new RuntimeException('Hệ thống phải còn ít nhất một quản trị viên.');
            }
            admin_execute('UPDATE account SET is_admin = ?, update_time = NOW() WHERE id = ?', 'ii', [$newValue, $targetId]);
            admin_audit($newValue ? 'Cấp quyền admin' : 'Gỡ quyền admin', 'account', $targetId, ['username' => $target['username']]);
            admin_flash('success', $newValue ? 'Đã cấp quyền quản trị.' : 'Đã gỡ quyền quản trị.');
        } elseif ($action === 'save_resources') {
            $fields = ['vnd', 'tongnap', 'vang', 'thoi_vang', 'event_point', 'vip', 'tichdiem'];
            $values = [];
            foreach ($fields as $field) {
                $raw = trim((string) ($_POST[$field] ?? '0'));
                if (!preg_match('/^\d+$/', $raw)) {
                    throw new RuntimeException('Các giá trị tài nguyên phải là số nguyên không âm.');
                }
                $values[$field] = min((int) $raw, PHP_INT_MAX);
            }
            admin_execute(
                'UPDATE account SET vnd = ?, tongnap = ?, vang = ?, thoi_vang = ?, event_point = ?, vip = ?, tichdiem = ?, update_time = NOW() WHERE id = ?',
                'iiiiiiii',
                [$values['vnd'], $values['tongnap'], $values['vang'], $values['thoi_vang'], $values['event_point'], $values['vip'], $values['tichdiem'], $targetId]
            );
            $before = array_intersect_key($target, array_flip($fields));
            admin_audit('Cập nhật tài nguyên', 'account', $targetId, ['username' => $target['username'], 'before' => $before, 'after' => $values]);
            admin_flash('success', 'Đã cập nhật tài nguyên cho ' . $target['username'] . '.');
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
    }

    $returnQuery = trim((string) ($_POST['return_query'] ?? ''));
    admin_redirect('accounts.php' . ($returnQuery !== '' ? '?' . $returnQuery : ''));
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$where = [];
$types = '';
$params = [];

if ($search !== '') {
    $where[] = '(a.username LIKE ? OR a.email LIKE ? OR p.name LIKE ?)';
    $term = '%' . $search . '%';
    $types .= 'sss';
    array_push($params, $term, $term, $term);
}
if ($status === 'admin') {
    $where[] = 'a.is_admin = 1';
} elseif ($status === 'banned') {
    $where[] = 'a.ban = 1';
} elseif ($status === 'member') {
    $where[] = 'a.active = 1';
} elseif ($status === 'normal') {
    $where[] = 'a.ban = 0 AND a.is_admin = 0';
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) admin_scalar(
    'SELECT COUNT(DISTINCT a.id) FROM account a LEFT JOIN player p ON p.account_id = a.id' . $whereSql,
    $types,
    $params
);
[$page, $pages, $offset, $perPage] = admin_page($total);

$accounts = admin_all(
    'SELECT a.id, a.username, a.email, a.is_admin, a.ban, a.active, a.vnd, a.tongnap,
            a.vang, a.thoi_vang, a.event_point, a.vip, a.tichdiem, a.create_time,
            a.last_time_login, a.ip_address, p.id AS player_id, p.name AS player_name,
            p.gender, p.head, p.rank, p.data_point, p.data_inventory
     FROM account a LEFT JOIN player p ON p.account_id = a.id' . $whereSql .
    ' ORDER BY a.id DESC LIMIT ? OFFSET ?',
    $types . 'ii',
    array_merge($params, [$perPage, $offset])
);

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = null;
if ($editId) {
    foreach ($accounts as $account) {
        if ((int) $account['id'] === $editId) {
            $editing = $account;
            break;
        }
    }
    if (!$editing) {
        $editing = admin_one(
            'SELECT a.*, p.name AS player_name FROM account a LEFT JOIN player p ON p.account_id = a.id WHERE a.id = ?',
            'i',
            [$editId]
        );
    }
}

admin_render_header('Tài khoản & nhân vật', 'accounts', 'Tìm kiếm, phân quyền và quản lý tài nguyên người chơi');
?>
<?php if ($editing): ?>
<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Chỉnh tài nguyên · <?= admin_escape($editing['username']) ?></h2><p>Nhân vật: <?= admin_escape($editing['player_name'] ?: 'Chưa tạo') ?> · ID <?= (int) $editing['id'] ?></p></div><span class="spacer"></span><a class="btn btn-secondary btn-sm" href="<?= admin_escape(admin_url('accounts.php')) ?>">Đóng</a></div>
    <div class="panel-body">
        <div class="note">Giá trị được lưu trực tiếp vào tài khoản game. Hãy kiểm tra kỹ trước khi cập nhật; thay đổi sẽ được ghi vào nhật ký quản trị.</div>
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?>
            <input type="hidden" name="action" value="save_resources">
            <input type="hidden" name="account_id" value="<?= (int) $editing['id'] ?>">
            <input type="hidden" name="return_query" value="<?= admin_escape(http_build_query(['q' => $search, 'status' => $status, 'page' => $page])) ?>">
            <div class="form-group"><label>SỐ DƯ VND</label><input class="form-control" type="number" min="0" name="vnd" value="<?= (int) $editing['vnd'] ?>" required></div>
            <div class="form-group"><label>TỔNG NẠP</label><input class="form-control" type="number" min="0" name="tongnap" value="<?= (int) $editing['tongnap'] ?>" required></div>
            <div class="form-group"><label>VÀNG WEB</label><input class="form-control" type="number" min="0" name="vang" value="<?= (int) $editing['vang'] ?>" required></div>
            <div class="form-group"><label>THỎI VÀNG</label><input class="form-control" type="number" min="0" name="thoi_vang" value="<?= (int) $editing['thoi_vang'] ?>" required></div>
            <div class="form-group"><label>ĐIỂM SỰ KIỆN</label><input class="form-control" type="number" min="0" name="event_point" value="<?= (int) $editing['event_point'] ?>" required></div>
            <div class="form-group"><label>VIP</label><input class="form-control" type="number" min="0" name="vip" value="<?= (int) $editing['vip'] ?>" required></div>
            <div class="form-group"><label>ĐIỂM DIỄN ĐÀN</label><input class="form-control" type="number" min="0" name="tichdiem" value="<?= (int) $editing['tichdiem'] ?>" required></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu tài nguyên</button></div>
        </form>
    </div>
</section>
<?php endif; ?>

<form class="toolbar" method="get">
    <div class="search-box"><input type="search" name="q" value="<?= admin_escape($search) ?>" placeholder="Tên tài khoản, nhân vật hoặc email..."><button type="submit">Tìm kiếm</button></div>
    <select class="filter-select" name="status" onchange="this.form.submit()">
        <option value="">Tất cả trạng thái</option>
        <option value="admin" <?= $status === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
        <option value="banned" <?= $status === 'banned' ? 'selected' : '' ?>>Đã khóa</option>
        <option value="member" <?= $status === 'member' ? 'selected' : '' ?>>Đã mở thành viên</option>
        <option value="normal" <?= $status === 'normal' ? 'selected' : '' ?>>Bình thường</option>
    </select>
    <span class="count"><?= admin_number($total) ?> kết quả</span>
</form>

<section class="panel">
    <div class="panel-head"><div><h2>Danh sách tài khoản</h2><p>Hiển thị <?= count($accounts) ?> / <?= $total ?> tài khoản</p></div></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Tài khoản / nhân vật</th><th>Số dư</th><th>Trạng thái</th><th>Lần cuối đăng nhập</th><th class="text-right">Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><div class="cell-user"><span class="mini-avatar"><?= admin_escape(mb_strtoupper(mb_substr($account['username'], 0, 1))) ?></span><div><strong><?= admin_escape($account['username']) ?> <span class="text-muted">#<?= (int) $account['id'] ?></span></strong><small><?= admin_escape($account['player_name'] ?: 'Chưa tạo nhân vật') ?><?= $account['ip_address'] ? ' · ' . admin_escape($account['ip_address']) : '' ?></small></div></div></td>
                    <td><strong><?= admin_number($account['vnd']) ?>đ</strong><br><small class="text-muted">Nạp: <?= admin_number($account['tongnap']) ?></small></td>
                    <td>
                        <?php if ((int) $account['ban'] === 1): ?><span class="badge badge-red">Đã khóa</span>
                        <?php elseif ((int) $account['is_admin'] === 1): ?><span class="badge badge-orange">Admin</span>
                        <?php elseif ((int) $account['active'] === 1): ?><span class="badge badge-green">Thành viên</span>
                        <?php else: ?><span class="badge badge-gray">Chưa thành viên</span><?php endif; ?>
                    </td>
                    <td class="nowrap"><?= admin_escape(admin_datetime($account['last_time_login'])) ?><br><small class="text-muted">Tạo <?= admin_escape(admin_datetime($account['create_time'])) ?></small></td>
                    <td class="text-right">
                        <div class="actions" style="justify-content:flex-end">
                            <a class="btn btn-blue btn-sm" href="<?= admin_escape(admin_query_url(['edit' => $account['id']])) ?>">Tài nguyên</a>
                            <form method="post" data-confirm="<?= (int) $account['ban'] === 1 ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?' ?>">
                                <?= admin_csrf_field() ?><input type="hidden" name="action" value="toggle_ban"><input type="hidden" name="account_id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="return_query" value="<?= admin_escape(http_build_query($_GET)) ?>">
                                <button class="btn <?= (int) $account['ban'] === 1 ? 'btn-secondary' : 'btn-danger' ?> btn-sm" type="submit"><?= (int) $account['ban'] === 1 ? 'Mở khóa' : 'Khóa' ?></button>
                            </form>
                            <form method="post" data-confirm="<?= (int) $account['is_admin'] === 1 ? 'Gỡ quyền quản trị của tài khoản này?' : 'Cấp toàn quyền quản trị cho tài khoản này?' ?>">
                                <?= admin_csrf_field() ?><input type="hidden" name="action" value="toggle_admin"><input type="hidden" name="account_id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="return_query" value="<?= admin_escape(http_build_query($_GET)) ?>">
                                <button class="btn btn-secondary btn-sm" type="submit"><?= (int) $account['is_admin'] === 1 ? 'Gỡ admin' : 'Cấp admin' ?></button>
                            </form>
                            <form method="post" data-confirm="<?= (int) $account['active'] === 1 ? 'Hủy trạng thái thành viên?' : 'Mở thành viên cho tài khoản này?' ?>">
                                <?= admin_csrf_field() ?><input type="hidden" name="action" value="toggle_active"><input type="hidden" name="account_id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="return_query" value="<?= admin_escape(http_build_query($_GET)) ?>">
                                <button class="btn btn-secondary btn-sm" type="submit"><?= (int) $account['active'] === 1 ? 'Hủy TV' : 'Mở TV' ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$accounts): ?><tr><td colspan="5"><div class="empty"><strong>Không tìm thấy tài khoản</strong>Thử thay đổi từ khóa hoặc bộ lọc.</div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_pagination($page, $pages); admin_render_footer(); ?>

