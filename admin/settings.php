<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'save_public') {
            $fields = ['Title', 'ServerName', 'Fanpage', 'Group', 'Zalo', 'EmailSupport', 'AccountBank', 'NumberBank', 'NameBank', 'Android', 'Windows', 'IPhone', 'Java'];
            $values = [];
            foreach ($fields as $field) {
                $values[$field] = trim((string) ($_POST[$field] ?? ''));
            }
            if ($values['Title'] === '' || $values['ServerName'] === '') {
                throw new RuntimeException('Tên website và tên máy chủ không được để trống.');
            }
            $numberBank = $values['NumberBank'] === '' ? null : (int) preg_replace('/\D+/', '', $values['NumberBank']);
            admin_execute(
                'UPDATE settings SET Title=?, ServerName=?, Fanpage=?, `Group`=?, Zalo=?, EmailSupport=?,
                 AccountBank=?, NumberBank=?, NameBank=?, Android=?, Windows=?, IPhone=?, Java=?',
                'sssssssisssss',
                [$values['Title'], $values['ServerName'], $values['Fanpage'], $values['Group'], $values['Zalo'], $values['EmailSupport'], $values['AccountBank'], $numberBank, $values['NameBank'], $values['Android'], $values['Windows'], $values['IPhone'], $values['Java']]
            );
            admin_audit('Cập nhật cấu hình website', 'settings', null, ['fields' => array_keys($values)]);
            admin_flash('success', 'Đã lưu cấu hình website.');
        } elseif ($action === 'save_server') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $serverName = trim((string) ($_POST['tenmaychu'] ?? ''));
            $domain = trim((string) ($_POST['domain'] ?? ''));
            $state = (string) ($_POST['trangthai'] ?? 'hoatdong');
            if (!in_array($state, ['hoatdong', 'baotri'], true)) throw new RuntimeException('Trạng thái máy chủ không hợp lệ.');
            if ($title === '' || $serverName === '') throw new RuntimeException('Tiêu đề và tên máy chủ không được để trống.');
            admin_execute(
                'UPDATE adminpanel SET title = ?, tenmaychu = ?, domain = ?, trangthai = ?, android = ?, iphone = ?, windows = ?, java = ?, giatri = ?',
                'ssssssssi',
                [$title, $serverName, $domain, $state, trim((string) ($_POST['android'] ?? '')), trim((string) ($_POST['iphone'] ?? '')), trim((string) ($_POST['windows'] ?? '')), trim((string) ($_POST['java'] ?? '')), max(0, (int) ($_POST['giatri'] ?? 0))]
            );
            admin_audit('Cập nhật trạng thái máy chủ', 'adminpanel', null, ['server' => $serverName, 'status' => $state]);
            admin_flash('success', 'Đã lưu cấu hình máy chủ.');
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
    }
    admin_redirect('settings.php');
}

$settings = admin_one('SELECT * FROM settings') ?? [];
$server = admin_one('SELECT * FROM adminpanel LIMIT 1') ?? [];
admin_render_header('Cấu hình website', 'settings', 'Quản lý thông tin hiển thị, liên hệ và trạng thái máy chủ');
?>
<div class="note">Các khóa bí mật thanh toán, mật khẩu ngân hàng và API không được hiển thị trong control panel để tránh lộ thông tin nhạy cảm.</div>
<div class="grid-2">
    <section class="panel">
        <div class="panel-head"><div><h2>Thông tin website</h2><p>Tên thương hiệu, liên hệ và tài khoản nhận tiền</p></div></div>
        <div class="panel-body"><form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_public">
            <div class="form-group"><label>TÊN WEBSITE</label><input class="form-control" name="Title" maxlength="100" value="<?= admin_escape($settings['Title'] ?? '') ?>" required></div>
            <div class="form-group"><label>TÊN MÁY CHỦ</label><input class="form-control" name="ServerName" maxlength="100" value="<?= admin_escape($settings['ServerName'] ?? '') ?>" required></div>
            <div class="form-group"><label>FANPAGE</label><input class="form-control" name="Fanpage" maxlength="100" value="<?= admin_escape($settings['Fanpage'] ?? '') ?>"></div>
            <div class="form-group"><label>NHÓM CỘNG ĐỒNG</label><input class="form-control" name="Group" maxlength="100" value="<?= admin_escape($settings['Group'] ?? '') ?>"></div>
            <div class="form-group"><label>ZALO</label><input class="form-control" name="Zalo" maxlength="100" value="<?= admin_escape($settings['Zalo'] ?? '') ?>"></div>
            <div class="form-group"><label>EMAIL HỖ TRỢ</label><input class="form-control" type="email" name="EmailSupport" maxlength="50" value="<?= admin_escape($settings['EmailSupport'] ?? '') ?>"></div>
            <div class="form-group"><label>NGÂN HÀNG</label><input class="form-control" name="AccountBank" maxlength="50" value="<?= admin_escape($settings['AccountBank'] ?? '') ?>"></div>
            <div class="form-group"><label>SỐ TÀI KHOẢN</label><input class="form-control" inputmode="numeric" name="NumberBank" maxlength="30" value="<?= admin_escape($settings['NumberBank'] ?? '') ?>"></div>
            <div class="form-group full"><label>CHỦ TÀI KHOẢN</label><input class="form-control" name="NameBank" maxlength="50" value="<?= admin_escape($settings['NameBank'] ?? '') ?>"></div>
            <div class="form-group"><label>LINK ANDROID</label><input class="form-control" name="Android" maxlength="255" value="<?= admin_escape($settings['Android'] ?? '') ?>"></div>
            <div class="form-group"><label>LINK WINDOWS</label><input class="form-control" name="Windows" maxlength="255" value="<?= admin_escape($settings['Windows'] ?? '') ?>"></div>
            <div class="form-group"><label>LINK IPHONE</label><input class="form-control" name="IPhone" maxlength="255" value="<?= admin_escape($settings['IPhone'] ?? '') ?>"></div>
            <div class="form-group"><label>LINK JAVA</label><input class="form-control" name="Java" maxlength="255" value="<?= admin_escape($settings['Java'] ?? '') ?>"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu thông tin website</button></div>
        </form></div>
    </section>
    <div class="stack">
        <section class="panel">
            <div class="panel-head"><div><h2>Trạng thái máy chủ</h2><p>Cấu hình bảng adminpanel của server</p></div></div>
            <div class="panel-body"><form class="form-grid" method="post">
                <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_server">
                <div class="form-group full"><label>TIÊU ĐỀ</label><input class="form-control" name="title" value="<?= admin_escape($server['title'] ?? '') ?>" required></div>
                <div class="form-group full"><label>TÊN MÁY CHỦ</label><input class="form-control" name="tenmaychu" value="<?= admin_escape($server['tenmaychu'] ?? '') ?>" required></div>
                <div class="form-group full"><label>DOMAIN</label><input class="form-control" type="url" name="domain" value="<?= admin_escape($server['domain'] ?? '') ?>"></div>
                <div class="form-group"><label>TRẠNG THÁI</label><select class="form-control" name="trangthai"><option value="hoatdong" <?= ($server['trangthai'] ?? '') === 'hoatdong' ? 'selected' : '' ?>>Hoạt động</option><option value="baotri" <?= ($server['trangthai'] ?? '') === 'baotri' ? 'selected' : '' ?>>Bảo trì</option></select></div>
                <div class="form-group"><label>GIÁ TRỊ QUY ĐỔI</label><input class="form-control" type="number" min="0" name="giatri" value="<?= (int) ($server['giatri'] ?? 0) ?>"></div>
                <div class="form-group"><label>ANDROID</label><input class="form-control" name="android" value="<?= admin_escape($server['android'] ?? '') ?>"></div>
                <div class="form-group"><label>IPHONE</label><input class="form-control" name="iphone" value="<?= admin_escape($server['iphone'] ?? '') ?>"></div>
                <div class="form-group"><label>WINDOWS</label><input class="form-control" name="windows" value="<?= admin_escape($server['windows'] ?? '') ?>"></div>
                <div class="form-group"><label>JAVA</label><input class="form-control" name="java" value="<?= admin_escape($server['java'] ?? '') ?>"></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu trạng thái máy chủ</button></div>
            </form></div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>An toàn hệ thống</h2><p>Trạng thái bảo vệ control panel</p></div></div>
            <div class="panel-body quick-list">
                <div class="quick-item"><span class="quick-icon">✓</span><span><strong>Kiểm tra quyền mỗi request</strong><small>Dựa trên account.is_admin trong database</small></span><span class="badge badge-green">Bật</span></div>
                <div class="quick-item"><span class="quick-icon">✓</span><span><strong>Chống giả mạo biểu mẫu</strong><small>CSRF token cho mọi thay đổi</small></span><span class="badge badge-green">Bật</span></div>
                <div class="quick-item"><span class="quick-icon">✓</span><span><strong>Nhật ký thao tác</strong><small>Lưu admin, IP và dữ liệu thay đổi</small></span><span class="badge badge-green">Bật</span></div>
            </div>
        </section>
    </div>
</div>
<?php admin_render_footer(); ?>

