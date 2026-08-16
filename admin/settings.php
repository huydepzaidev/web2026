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
            // Synchronize download links to adminpanel table for compatibility
            admin_execute(
                'UPDATE adminpanel SET android=?, windows=?, iphone=?, java=?',
                'ssss',
                [$values['Android'], $values['Windows'], $values['IPhone'], $values['Java']]
            );
            admin_audit('Cập nhật cấu hình website', 'settings', null, ['fields' => array_keys($values)]);
            admin_flash('success', 'Đã lưu cấu hình website và link tải game.');
        } elseif ($action === 'save_server') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $serverName = trim((string) ($_POST['tenmaychu'] ?? ''));
            $domain = trim((string) ($_POST['domain'] ?? ''));
            $state = (string) ($_POST['trangthai'] ?? 'hoatdong');
            if (!in_array($state, ['hoatdong', 'baotri'], true)) throw new RuntimeException('Trạng thái máy chủ không hợp lệ.');
            if ($title === '' || $serverName === '') throw new RuntimeException('Tiêu đề và tên máy chủ không được để trống.');
            admin_execute(
                'UPDATE adminpanel SET title = ?, tenmaychu = ?, domain = ?, trangthai = ?, giatri = ?',
                'ssssi',
                [$title, $serverName, $domain, $state, max(0, (int) ($_POST['giatri'] ?? 0))]
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
admin_render_header('Cấu hình website', 'settings', 'Quản lý thông tin hiển thị, link tải game, liên hệ và trạng thái máy chủ');
?>
<div class="note">💡 <strong>Mẹo:</strong> Bạn chỉ cần ném link tải (Google Drive, MediaFire, Mega, TestFlight hoặc direct link) vào ô tương ứng rồi nhấn <strong>Lưu</strong>. Người chơi bấm nút tải trên web sẽ lập tức tải từ link đó. Nếu để trống, web sẽ tự dùng file mặc định trong thư mục <code>down/</code>.</div>

<div class="grid-2">
    <div class="stack">
        <section class="panel">
            <div class="panel-head"><div><h2>🎮 Cấu hình Link Tải Game</h2><p>Link tải ứng dụng trên Website và Diễn Đàn</p></div></div>
            <div class="panel-body"><form class="form-grid" method="post">
                <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_public">
                <!-- Keep hidden fields for website information so single form or separate sections save cleanly -->
                <input type="hidden" name="Title" value="<?= admin_escape($settings['Title'] ?? 'Chú Bé Rồng Online') ?>">
                <input type="hidden" name="ServerName" value="<?= admin_escape($settings['ServerName'] ?? 'Chú Bé Rồng') ?>">
                <input type="hidden" name="Fanpage" value="<?= admin_escape($settings['Fanpage'] ?? '') ?>">
                <input type="hidden" name="Group" value="<?= admin_escape($settings['Group'] ?? '') ?>">
                <input type="hidden" name="Zalo" value="<?= admin_escape($settings['Zalo'] ?? '') ?>">
                <input type="hidden" name="EmailSupport" value="<?= admin_escape($settings['EmailSupport'] ?? '') ?>">
                <input type="hidden" name="AccountBank" value="<?= admin_escape($settings['AccountBank'] ?? '') ?>">
                <input type="hidden" name="NumberBank" value="<?= admin_escape($settings['NumberBank'] ?? '') ?>">
                <input type="hidden" name="NameBank" value="<?= admin_escape($settings['NameBank'] ?? '') ?>">

                <div class="form-group full">
                    <label>LINK TẢI ANDROID (APK / Play Store)</label>
                    <input class="form-control" name="Android" placeholder="Ví dụ: https://drive.google.com/file/d/... hoặc down/NRO.apk" value="<?= admin_escape($settings['Android'] ?? '') ?>">
                    <small style="color:var(--text-muted); font-size:12px;">Mặc định: <code>down/NRO.apk</code></small>
                </div>
                <div class="form-group full">
                    <label>LINK TẢI PC / WINDOWS (RAR / ZIP / EXE)</label>
                    <input class="form-control" name="Windows" placeholder="Ví dụ: https://drive.google.com/file/d/... hoặc down/PC.rar" value="<?= admin_escape($settings['Windows'] ?? '') ?>">
                    <small style="color:var(--text-muted); font-size:12px;">Mặc định: <code>down/PC.rar</code></small>
                </div>
                <div class="form-group full">
                    <label>LINK TẢI IPHONE / IOS (TestFlight / IPA / AppStore)</label>
                    <input class="form-control" name="IPhone" placeholder="Ví dụ: https://testflight.apple.com/join/..." value="<?= admin_escape($settings['IPhone'] ?? '') ?>">
                    <small style="color:var(--text-muted); font-size:12px;">Mặc định: link TestFlight</small>
                </div>
                <div class="form-group full">
                    <label>LINK TẢI JAVA (JAR)</label>
                    <input class="form-control" name="Java" placeholder="Ví dụ: down/JAR.jar hoặc link drive" value="<?= admin_escape($settings['Java'] ?? '') ?>">
                    <small style="color:var(--text-muted); font-size:12px;">Mặc định: <code>down/JAR.jar</code></small>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">💾 Lưu link tải game</button></div>
            </form></div>
        </section>

        <section class="panel">
            <div class="panel-head"><div><h2>Thông tin chung website</h2><p>Tên thương hiệu, liên hệ và nạp tiền</p></div></div>
            <div class="panel-body"><form class="form-grid" method="post">
                <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_public">
                <!-- Keep download links in this form too so submitting either form preserves everything -->
                <input type="hidden" name="Android" value="<?= admin_escape($settings['Android'] ?? '') ?>">
                <input type="hidden" name="Windows" value="<?= admin_escape($settings['Windows'] ?? '') ?>">
                <input type="hidden" name="IPhone" value="<?= admin_escape($settings['IPhone'] ?? '') ?>">
                <input type="hidden" name="Java" value="<?= admin_escape($settings['Java'] ?? '') ?>">

                <div class="form-group"><label>TÊN WEBSITE</label><input class="form-control" name="Title" maxlength="100" value="<?= admin_escape($settings['Title'] ?? '') ?>" required></div>
                <div class="form-group"><label>TÊN MÁY CHỦ</label><input class="form-control" name="ServerName" maxlength="100" value="<?= admin_escape($settings['ServerName'] ?? '') ?>" required></div>
                <div class="form-group"><label>FANPAGE</label><input class="form-control" name="Fanpage" maxlength="100" value="<?= admin_escape($settings['Fanpage'] ?? '') ?>"></div>
                <div class="form-group"><label>NHÓM CỘNG ĐỒNG</label><input class="form-control" name="Group" maxlength="100" value="<?= admin_escape($settings['Group'] ?? '') ?>"></div>
                <div class="form-group"><label>ZALO (BOX ZALO)</label><input class="form-control" name="Zalo" maxlength="255" value="<?= admin_escape($settings['Zalo'] ?? '') ?>"></div>
                <div class="form-group"><label>EMAIL HỖ TRỢ</label><input class="form-control" type="email" name="EmailSupport" maxlength="50" value="<?= admin_escape($settings['EmailSupport'] ?? '') ?>"></div>
                <div class="form-group"><label>NGÂN HÀNG</label><input class="form-control" name="AccountBank" maxlength="50" value="<?= admin_escape($settings['AccountBank'] ?? '') ?>"></div>
                <div class="form-group"><label>SỐ TÀI KHOẢN</label><input class="form-control" inputmode="numeric" name="NumberBank" maxlength="30" value="<?= admin_escape($settings['NumberBank'] ?? '') ?>"></div>
                <div class="form-group full"><label>CHỦ TÀI KHOẢN</label><input class="form-control" name="NameBank" maxlength="50" value="<?= admin_escape($settings['NameBank'] ?? '') ?>"></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu thông tin website</button></div>
            </form></div>
        </section>
    </div>

    <div class="stack">
        <section class="panel">
            <div class="panel-head"><div><h2>Trạng thái máy chủ</h2><p>Cấu hình máy chủ và quy đổi</p></div></div>
            <div class="panel-body"><form class="form-grid" method="post">
                <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_server">
                <div class="form-group full"><label>TIÊU ĐỀ</label><input class="form-control" name="title" value="<?= admin_escape($server['title'] ?? '') ?>" required></div>
                <div class="form-group full"><label>TÊN MÁY CHỦ</label><input class="form-control" name="tenmaychu" value="<?= admin_escape($server['tenmaychu'] ?? '') ?>" required></div>
                <div class="form-group full"><label>DOMAIN</label><input class="form-control" type="url" name="domain" value="<?= admin_escape($server['domain'] ?? '') ?>"></div>
                <div class="form-group"><label>TRẠNG THÁI</label><select class="form-control" name="trangthai"><option value="hoatdong" <?= ($server['trangthai'] ?? '') === 'hoatdong' ? 'selected' : '' ?>>Hoạt động</option><option value="baotri" <?= ($server['trangthai'] ?? '') === 'baotri' ? 'selected' : '' ?>>Bảo trì</option></select></div>
                <div class="form-group"><label>GIÁ TRỊ QUY ĐỔI</label><input class="form-control" type="number" min="0" name="giatri" value="<?= (int) ($server['giatri'] ?? 0) ?>"></div>
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

