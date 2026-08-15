<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

function game_int(string $key, int $min, int $max): int
{
    $value = $_POST[$key] ?? null;
    $raw = filter_var($value, FILTER_VALIDATE_INT);
    if ($raw === false || $raw < $min || $raw > $max) {
        throw new RuntimeException("Giá trị {$key} phải từ {$min} đến {$max}.");
    }
    return $raw;
}

function game_chance_bp(string $value): int
{
    $normalized = str_replace(',', '.', trim($value));
    if ($normalized === '' || !is_numeric($normalized)) {
        throw new RuntimeException('Tỉ lệ rơi không hợp lệ.');
    }
    $percent = (float) $normalized;
    if ($percent < 0 || $percent > 100) {
        throw new RuntimeException('Tỉ lệ rơi phải từ 0 đến 100%.');
    }
    return (int) round($percent * 100);
}

function game_boss_label(array $boss): string
{
    $name = trim((string) ($boss['boss_name'] ?? ''));
    $key = trim((string) ($boss['boss_key'] ?? ''));
    if ($key === '' || ($name !== '' && $name !== $key)) {
        return $name !== '' ? $name : ('Boss ' . (int) ($boss['boss_id'] ?? 0));
    }

    $exact = [
        'BROLY' => 'Broly', 'BUJIN' => 'Bujin', 'KOGU' => 'Kogu',
        'ZANGYA' => 'Zangya', 'BIDO' => 'Bido', 'BOJACK' => 'Bojack',
        'SUPER_BOJACK' => 'Super Bojack', 'KUKU' => 'Kuku',
        'MAP_DAU_DINH' => 'Mập đầu đinh', 'RAMBO' => 'Rambo',
        'DRABURA' => 'Drabura', 'BUI_BUI' => 'Bui Bui',
        'BUI_BUI_2' => 'Bui Bui 2', 'YA_CON' => 'Yacon',
        'DRABURA_2' => 'Drabura 2', 'GOKU' => 'Goku', 'CADIC' => 'Cadic',
        'MABU_12H' => 'Mabư 12h', 'DRABURA_3' => 'Drabura 3',
        'MABU' => 'Mabư', 'SUPERBU' => 'Super Bư', 'FIDE' => 'Fide',
        'DR_KORE' => 'Dr. Kôrê', 'PIC' => 'Pic', 'POC' => 'Poc',
        'KING_KONG' => 'King Kong', 'XEN_BO_HUNG' => 'Xên bọ hung',
        'SIEU_BO_HUNG' => 'Siêu bọ hung', 'COOLER' => 'Cooler',
        'KHIDOT' => 'Khỉ Đột', 'NGUYETTHAN' => 'Nguyệt Thần',
        'NHATTHAN' => 'Nhật Thần', 'GOLDEN_FRIEZA' => 'Golden Frieza',
        'BIMA' => 'Bí Ma', 'MATROI' => 'Ma Trơi', 'DOI' => 'Dơi',
        'ONG_GIA_NOEL' => 'Ông Già Noel', 'SON_TINH' => 'Sơn Tinh',
        'THUY_TINH' => 'Thủy Tinh', 'LAN_CON' => 'Lân Con',
        'SOI_HEC_QUYN1' => 'Sói Hẹc Quyn', 'O_DO1' => 'Ô Đô',
        'Virut' => 'Virut', 'MAT_TROI' => 'Mặt Trời',
        'BLACK_GOKU' => 'Black Goku', 'CUMBER' => 'Cumber',
        'AN_TROM' => 'Ăn trộm', 'RONG_NHI' => 'Rồng Nhí', 'BABY' => 'Baby',
        'TAU_PAY_PAY_DONG_NAM_KARIN' => 'Tàu Pây Pây Đông Nam Karin',
        'TIEU_DOI_TRUONG' => 'Tiểu đội trưởng',
        'TIEU_DOI_TRUONG_NM' => 'Tiểu đội trưởng Namek',
    ];
    if (isset($exact[$key])) {
        return $exact[$key];
    }
    if (preg_match('/^TAP_SU_(\d+)$/', $key, $match)) return 'Tập sự ' . $match[1];
    if (preg_match('/^TAN_BINH_(\d+)$/', $key, $match)) return 'Tân binh ' . $match[1];
    if (preg_match('/^CHIEN_BINH_(\d+)$/', $key, $match)) return 'Chiến binh ' . $match[1];
    if (preg_match('/^DOI_TRUONG_(\d+)$/', $key, $match)) return 'Đội trưởng ' . $match[1];
    if (preg_match('/^SO_(\d+)(_NM)?$/', $key, $match)) return 'Số ' . $match[1] . (!empty($match[2]) ? ' Namek' : '');
    if (preg_match('/^XEN_CON_(\d+)$/', $key, $match)) return 'Xên con ' . $match[1];
    if (preg_match('/^DEATH_BEAM_(\d+)$/', $key, $match)) return 'Death Beam ' . $match[1];
    if (preg_match('/^ANDROID_(\d+)$/', $key, $match)) return 'Android ' . $match[1];
    return mb_convert_case(str_replace('_', ' ', mb_strtolower($key)), MB_CASE_TITLE, 'UTF-8');
}

function game_boss_group_label(string $group): string
{
    return [
        'GLOBAL' => 'Boss thường', 'ANDROID' => 'Android', 'CELL' => 'Xên',
        'NAMEK' => 'Tiểu đội sát thủ Namek', 'BOJACK' => 'Nhóm Bojack',
        'BOSS_12H' => 'Boss 12 giờ', 'MAJIN' => 'Mabư',
        'YARDART' => 'Yardart', 'EVENT' => 'Boss sự kiện',
        'MINI' => 'Mini boss', 'RUNTIME' => 'Boss runtime',
    ][$group] ?? $group;
}

function game_render_boss_options(array $bosses, string $metric): void
{
    $currentGroup = null;
    foreach ($bosses as $boss) {
        $group = (string) ($boss['boss_group'] ?? 'RUNTIME');
        if ($group !== $currentGroup) {
            if ($currentGroup !== null) echo '</optgroup>';
            echo '<optgroup label="' . admin_escape(game_boss_group_label($group)) . '">';
            $currentGroup = $group;
        }
        $suffix = $metric === 'drop'
            ? ((int) ($boss['drop_count'] ?? 0) . ' item')
            : ((int) ($boss['active_instances'] ?? 0) . ' instance');
        echo '<option value="' . (int) $boss['boss_id'] . '">'
            . admin_escape(game_boss_label($boss)) . ' · ID ' . (int) $boss['boss_id']
            . ' · ' . admin_escape($suffix) . '</option>';
    }
    if ($currentGroup !== null) echo '</optgroup>';
}

function game_queue_command(string $type, ?int $bossId = null): void
{
    global $admin_user;
    $allowed = [
        'RELOAD_CONFIG', 'RESPAWN_BOSS', 'RESPAWN_ALL',
        'START_MAINTENANCE', 'STOP_MAINTENANCE',
    ];
    if (!in_array($type, $allowed, true)) {
        throw new RuntimeException('Lệnh server không hợp lệ.');
    }
    if (($type === 'RESPAWN_BOSS') !== ($bossId !== null)) {
        throw new RuntimeException('Boss của lệnh gọi lại không hợp lệ.');
    }
    $existing = admin_scalar(
        'SELECT COUNT(*) FROM game_server_command
         WHERE command_type=? AND status IN ("PENDING","PROCESSING")
         AND ((boss_id IS NULL AND ? IS NULL) OR boss_id=?)',
        'sii',
        [$type, $bossId, $bossId]
    );
    if ((int) $existing > 0) {
        return;
    }
    admin_execute(
        'INSERT INTO game_server_command (command_type, boss_id, requested_by) VALUES (?, ?, ?)',
        'sis',
        [$type, $bossId, (string) $admin_user['username']]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'save_config') {
            $expRate = game_int('exp_rate', 1, 100);
            $dropRate = game_int('drop_rate_percent', 0, 1000);
            $countdown = game_int('maintenance_countdown_seconds', 10, 3600);
            $stuckSeconds = game_int('boss_stuck_seconds', 10, 3600);
            $refreshSeconds = game_int('config_refresh_seconds', 2, 60);
            $maintenanceTime = trim((string) ($_POST['maintenance_time'] ?? ''));
            $time = DateTimeImmutable::createFromFormat('!H:i', $maintenanceTime);
            if (!$time || $time->format('H:i') !== $maintenanceTime) {
                throw new RuntimeException('Giờ bảo trì không hợp lệ.');
            }
            $maintenanceEnabled = isset($_POST['auto_maintenance_enabled']) ? 1 : 0;
            $watchdogEnabled = isset($_POST['boss_watchdog_enabled']) ? 1 : 0;
            // Upsert để panel vẫn hoạt động nếu database mới chỉ tạo bảng mà
            // chưa có dòng singleton id=1. Server sẽ đọc cùng snapshot này,
            // không cần sửa Config.properties hay khởi động lại.
            admin_execute(
                'INSERT INTO game_server_config
                 (id, exp_rate, drop_rate_percent, auto_maintenance_enabled,
                  maintenance_time, maintenance_countdown_seconds,
                  boss_watchdog_enabled, boss_stuck_seconds,
                  config_refresh_seconds, updated_by)
                 VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                  exp_rate=VALUES(exp_rate), drop_rate_percent=VALUES(drop_rate_percent),
                  auto_maintenance_enabled=VALUES(auto_maintenance_enabled),
                  maintenance_time=VALUES(maintenance_time),
                  maintenance_countdown_seconds=VALUES(maintenance_countdown_seconds),
                  boss_watchdog_enabled=VALUES(boss_watchdog_enabled),
                  boss_stuck_seconds=VALUES(boss_stuck_seconds),
                  config_refresh_seconds=VALUES(config_refresh_seconds),
                  updated_by=VALUES(updated_by)',
                'iiisiiiis',
                [
                    $expRate, $dropRate, $maintenanceEnabled, $time->format('H:i:s'),
                    $countdown, $watchdogEnabled, $stuckSeconds, $refreshSeconds,
                    (string) $admin_user['username'],
                ]
            );
            game_queue_command('RELOAD_CONFIG');
            admin_audit('Cập nhật cấu hình game server', 'game_server_config', 1, [
                'exp_rate' => $expRate,
                'drop_rate_percent' => $dropRate,
                'maintenance_enabled' => $maintenanceEnabled,
                'maintenance_time' => $time->format('H:i:s'),
                'watchdog_enabled' => $watchdogEnabled,
            ]);
            admin_flash('success', 'Đã gửi cập nhật EXP ngay cho game server; Quy Lão Kame sẽ thông báo toàn server, không cần reset.');
        } elseif ($action === 'save_login_notice') {
            $noticeEnabled = isset($_POST['login_notice_enabled']) ? 1 : 0;
            $noticeText = str_replace(["\r\n", "\r"], "\n", trim((string) ($_POST['login_notice_text'] ?? '')));
            if ($noticeEnabled === 1 && $noticeText === '') {
                throw new RuntimeException('Nội dung thông báo không được để trống khi đang bật.');
            }
            if (mb_strlen($noticeText, 'UTF-8') > 1000) {
                throw new RuntimeException('Nội dung thông báo tối đa 1.000 ký tự.');
            }
            admin_execute(
                'INSERT INTO game_server_config
                 (id, login_notice_enabled, login_notice_text, updated_by)
                 VALUES (1, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                  login_notice_enabled=VALUES(login_notice_enabled),
                  login_notice_text=VALUES(login_notice_text),
                  updated_by=VALUES(updated_by)',
                'iss',
                [$noticeEnabled, $noticeText, (string) $admin_user['username']]
            );
            admin_audit('Cập nhật thông báo đăng nhập', 'game_server_config', 1, [
                'login_notice_enabled' => $noticeEnabled,
                'login_notice_length' => mb_strlen($noticeText, 'UTF-8'),
            ]);
            admin_flash('success', 'Đã lưu thông báo. Server tự đồng bộ; người chơi đăng nhập lại sẽ thấy nội dung mới, không cần chạy lại server.');
        } elseif ($action === 'save_divine_turn') {
            $enabled = isset($_POST['divine_turn_enabled']) ? 1 : 0;
            $pity = game_int('pity_blank_turns', 1, 100);
            $fields = [
                'one_zero_bp', 'one_one_bp', 'one_two_bp',
                'two_zero_bp', 'two_one_bp', 'two_two_bp',
                'multi_zero_bp', 'multi_one_bp', 'multi_two_bp', 'multi_three_bp',
            ];
            $values = [];
            foreach ($fields as $field) {
                $values[$field] = game_chance_bp((string) ($_POST[$field] ?? ''));
            }
            if ($values['one_zero_bp'] + $values['one_one_bp'] + $values['one_two_bp'] !== 10000
                    || $values['two_zero_bp'] + $values['two_one_bp'] + $values['two_two_bp'] !== 10000
                    || $values['multi_zero_bp'] + $values['multi_one_bp']
                    + $values['multi_two_bp'] + $values['multi_three_bp'] !== 10000) {
                throw new RuntimeException('Mỗi nhóm tỷ lệ phải có tổng đúng 100%.');
            }
            admin_execute(
                'INSERT INTO game_divine_turn_config
                 (id,enabled,one_zero_bp,one_one_bp,one_two_bp,two_zero_bp,two_one_bp,two_two_bp,
                  multi_zero_bp,multi_one_bp,multi_two_bp,multi_three_bp,pity_blank_turns,updated_by)
                 VALUES (1,?,?,?,?,?,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE enabled=VALUES(enabled),one_zero_bp=VALUES(one_zero_bp),
                  one_one_bp=VALUES(one_one_bp),one_two_bp=VALUES(one_two_bp),
                  two_zero_bp=VALUES(two_zero_bp),two_one_bp=VALUES(two_one_bp),two_two_bp=VALUES(two_two_bp),
                  multi_zero_bp=VALUES(multi_zero_bp),multi_one_bp=VALUES(multi_one_bp),
                  multi_two_bp=VALUES(multi_two_bp),multi_three_bp=VALUES(multi_three_bp),
                  pity_blank_turns=VALUES(pity_blank_turns),updated_by=VALUES(updated_by)',
                'iiiiiiiiiiiis',
                [$enabled, $values['one_zero_bp'], $values['one_one_bp'], $values['one_two_bp'],
                 $values['two_zero_bp'], $values['two_one_bp'], $values['two_two_bp'],
                 $values['multi_zero_bp'], $values['multi_one_bp'], $values['multi_two_bp'],
                 $values['multi_three_bp'], $pity, (string) $admin_user['username']]
            );
            admin_audit('Cập nhật tỷ lệ đồ Thần Linh theo lượt', 'game_divine_turn_config', 1, $values);
            admin_flash('success', 'Đã lưu tỷ lệ đồ Thần Linh theo lượt; server tự nạp trong tối đa 5 giây.');
        } elseif ($action === 'add_drop') {
            $bossId = game_int('boss_id', -2000000000, 2000000000);
            if (!admin_one('SELECT boss_id FROM game_boss_catalog WHERE boss_id=?', 'i', [$bossId])) {
                throw new RuntimeException('Boss không tồn tại trong danh mục server.');
            }
            $kind = (string) ($_POST['drop_kind'] ?? 'ITEM');
            if ($kind !== 'ITEM') {
                throw new RuntimeException('Loại vật phẩm không hợp lệ.');
            }
            $itemId = null;
            if ($kind === 'ITEM') {
                $itemId = game_int('item_id', 0, 32767);
                if (!admin_one('SELECT id FROM item_template WHERE id=?', 'i', [$itemId])) {
                    throw new RuntimeException('Item ID không tồn tại trong item_template.');
                }
            }
            $chanceBp = game_chance_bp((string) ($_POST['chance_percent'] ?? ''));
            $quantityMin = game_int('quantity_min', 1, 9999);
            $quantityMax = game_int('quantity_max', 1, 9999);
            if ($quantityMax < $quantityMin) {
                throw new RuntimeException('Số lượng tối đa không được nhỏ hơn tối thiểu.');
            }
            admin_execute(
                'INSERT INTO game_boss_drop
                 (boss_id, drop_kind, item_id, chance_bp, quantity_min, quantity_max, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                'isiiiis',
                [
                    $bossId, $kind, $itemId, $chanceBp, $quantityMin, $quantityMax,
                    (string) $admin_user['username'],
                ]
            );
            $dropId = (int) $conn->insert_id;
            game_queue_command('RELOAD_CONFIG');
            admin_audit('Thêm vật phẩm rơi boss', 'game_boss_drop', $dropId, [
                'boss_id' => $bossId, 'kind' => $kind, 'item_id' => $itemId,
                'chance_bp' => $chanceBp, 'quantity' => [$quantityMin, $quantityMax],
            ]);
            admin_flash('success', 'Đã thêm vật phẩm vào boss. Một boss có thể tiếp tục thêm nhiều dòng khác.');
        } elseif ($action === 'toggle_drop') {
            $dropId = game_int('drop_id', 1, PHP_INT_MAX);
            $row = admin_one('SELECT enabled, drop_kind FROM game_boss_drop WHERE id=?', 'i', [$dropId]);
            if (!$row) {
                throw new RuntimeException('Không tìm thấy cấu hình rơi đồ.');
            }
            if ($row['drop_kind'] === 'DIVINE_RANDOM') {
                throw new RuntimeException('Dòng Thần Linh cũ đã được thay bằng bảng tỷ lệ theo lượt.');
            }
            $enabled = (int) $row['enabled'] === 1 ? 0 : 1;
            admin_execute('UPDATE game_boss_drop SET enabled=? WHERE id=?', 'ii', [$enabled, $dropId]);
            game_queue_command('RELOAD_CONFIG');
            admin_audit($enabled ? 'Bật vật phẩm rơi boss' : 'Tắt vật phẩm rơi boss', 'game_boss_drop', $dropId);
            admin_flash('success', $enabled ? 'Đã bật vật phẩm.' : 'Đã tạm tắt vật phẩm.');
        } elseif ($action === 'delete_drop') {
            $dropId = game_int('drop_id', 1, PHP_INT_MAX);
            $row = admin_one('SELECT boss_id, drop_kind, item_id FROM game_boss_drop WHERE id=?', 'i', [$dropId]);
            if (!$row) {
                throw new RuntimeException('Không tìm thấy cấu hình rơi đồ.');
            }
            admin_execute('DELETE FROM game_boss_drop WHERE id=?', 'i', [$dropId]);
            game_queue_command('RELOAD_CONFIG');
            admin_audit('Xóa vật phẩm rơi boss', 'game_boss_drop', $dropId, $row);
            admin_flash('success', 'Đã xóa vật phẩm khỏi boss.');
        } elseif ($action === 'server_command') {
            $type = (string) ($_POST['command_type'] ?? '');
            $bossId = $type === 'RESPAWN_BOSS'
                ? game_int('boss_id', -2000000000, 2000000000)
                : null;
            if ($bossId !== null && !admin_one(
                'SELECT boss_id FROM game_boss_catalog WHERE boss_id=? AND active_instances>0',
                'i',
                [$bossId]
            )) {
                throw new RuntimeException('Boss này không có instance đang chạy; không tạo mới để tránh sai luồng sự kiện/phó bản.');
            }
            game_queue_command($type, $bossId);
            admin_audit('Gửi lệnh game server', 'game_server_command', null, [
                'command' => $type, 'boss_id' => $bossId,
            ]);
            if ($type === 'START_MAINTENANCE') {
                admin_flash('success', 'Đã gửi lệnh BẢO TRÌ: server sẽ lưu và kick người chơi thường, chỉ admin được đăng nhập.');
            } elseif ($type === 'STOP_MAINTENANCE') {
                admin_flash('success', 'Đã gửi lệnh KẾT THÚC BẢO TRÌ: server sẽ mở lại đăng nhập cho người chơi.');
            } else {
                admin_flash('success', 'Đã gửi lệnh. Server sẽ xử lý ở chu kỳ đồng bộ kế tiếp.');
            }
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
    }
    admin_redirect('game-server.php');
}

$config = admin_one('SELECT * FROM game_server_config WHERE id=1') ?? [];
$divineTurn = admin_one('SELECT * FROM game_divine_turn_config WHERE id=1') ?? [
    'enabled' => 1,
    'one_zero_bp' => 6500, 'one_one_bp' => 3000, 'one_two_bp' => 500,
    'two_zero_bp' => 5000, 'two_one_bp' => 3500, 'two_two_bp' => 1500,
    'multi_zero_bp' => 4000, 'multi_one_bp' => 3500,
    'multi_two_bp' => 2000, 'multi_three_bp' => 500,
    'pity_blank_turns' => 5,
];
$runtime = admin_one('SELECT * FROM game_server_runtime WHERE id=1') ?? [];
$bosses = admin_all(
    'SELECT c.*, COUNT(d.id) AS drop_count
     FROM game_boss_catalog c
     LEFT JOIN game_boss_drop d ON d.boss_id=c.boss_id
     GROUP BY c.boss_id, c.boss_key, c.boss_name, c.boss_group,
              c.active_instances, c.last_seen_at, c.updated_at
     ORDER BY FIELD(c.boss_group,"GLOBAL","ANDROID","CELL","NAMEK","BOJACK","BOSS_12H","MAJIN","YARDART","EVENT","MINI","RUNTIME"),
              c.boss_name, c.boss_id'
);
$rescueBosses = array_values(array_filter(
    $bosses,
    static fn(array $boss): bool => (int) $boss['active_instances'] > 0
));
$items = admin_all('SELECT id, NAME FROM item_template ORDER BY id');
$drops = admin_all(
    'SELECT d.*, c.boss_name, c.boss_key, c.boss_group, i.NAME AS item_name
     FROM game_boss_drop d
     LEFT JOIN game_boss_catalog c ON c.boss_id=d.boss_id
     LEFT JOIN item_template i ON i.id=d.item_id
     ORDER BY c.boss_name, d.id'
);
$commands = admin_all(
    'SELECT * FROM game_server_command ORDER BY id DESC LIMIT 12'
);
$heartbeat = !empty($runtime['last_heartbeat']) ? strtotime((string) $runtime['last_heartbeat']) : false;
$isOnline = (int) ($runtime['server_online'] ?? 0) === 1
    && $heartbeat !== false
    && $heartbeat >= time() - 75;
$maintenanceMode = (int) ($runtime['admin_only_mode'] ?? 0) === 1;

admin_render_header(
    'Vận hành game server',
    'game-server',
    'EXP, tỉ lệ rơi đồ, lịch bảo trì và khôi phục boss đều lấy từ database'
);
?>
<div class="metric-grid">
    <div class="metric-card"><span class="metric-icon <?= !$isOnline ? 'red' : ($maintenanceMode ? 'orange' : 'green') ?>">●</span><span><small>GAME SERVER</small><strong><?= !$isOnline ? 'Offline' : ($maintenanceMode ? 'Bảo trì' : 'Online') ?></strong></span></div>
    <div class="metric-card"><span class="metric-icon orange">×</span><span><small>EXP SERVER</small><strong><?= (int) ($config['exp_rate'] ?? 1) ?></strong></span></div>
    <div class="metric-card"><span class="metric-icon blue">%</span><span><small>HỆ SỐ DROP</small><strong><?= (int) ($config['drop_rate_percent'] ?? 100) ?>%</strong></span></div>
    <div class="metric-card"><span class="metric-icon green">♛</span><span><small>BOSS CẤU HÌNH / DROP RULE</small><strong><?= count($bosses) ?> / <?= count($drops) ?></strong></span></div>
</div>

<?php if (!empty($runtime['last_error'])): ?>
    <div class="alert alert-error"><span>!</span><div><strong>Lỗi gần nhất của server</strong><br><?= admin_escape($runtime['last_error']) ?></div></div>
<?php endif; ?>

<section class="panel server-config-grid">
    <div class="panel-head"><div><h2>Thông báo khi đăng nhập</h2><p>Popup quản trị hiện cho người chơi sau khi vào game</p></div></div>
    <div class="panel-body">
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_login_notice">
            <div class="form-group full">
                <label class="checkbox-control"><input type="checkbox" name="login_notice_enabled" <?= (int) ($config['login_notice_enabled'] ?? 1) === 1 ? 'checked' : '' ?>> Bật popup thông báo khi nhân vật đăng nhập</label>
            </div>
            <div class="form-group full">
                <label>NỘI DUNG THÔNG BÁO</label>
                <textarea class="form-control" name="login_notice_text" maxlength="1000" rows="6" placeholder="Nhập nội dung hiển thị cho người chơi..."><?= admin_escape((string) ($config['login_notice_text'] ?? 'X3 Kinh nghiệm đến hết ngày 11/5.' . "\n" . 'Sự kiện Goku Day.' . "\n" . 'Đua TOP nhận quà cực khủng.' . "\n" . 'Tích điểm đổi quà.' . "\n" . 'Chi tiết xem tại diễn đàn, fanpage.')) ?></textarea>
                <p class="help">Giữ xuống dòng như nội dung muốn hiển thị; tối đa 1.000 ký tự. Sau khi lưu, người chơi đăng nhập lại sẽ thấy nội dung mới mà không cần chạy lại server.</p>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu thông báo</button></div>
        </form>
    </div>
</section>

<div class="grid-2 server-config-grid">
    <section class="panel">
        <div class="panel-head"><div><h2>Cấu hình vận hành</h2><p>Server tự nạp lại theo chu kỳ, không sửa Config.properties</p></div></div>
        <div class="panel-body">
            <form class="form-grid" method="post">
                <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_config">
                <div class="form-group">
                    <label>EXP SERVER (X LẦN)</label>
                    <input class="form-control" type="number" min="1" max="100" name="exp_rate" value="<?= (int) ($config['exp_rate'] ?? 3) ?>" required>
                    <p class="help">Áp dụng trực tiếp vào tiềm năng/sức mạnh nhận được.</p>
                </div>
                <div class="form-group">
                    <label>HỆ SỐ TỈ LỆ RƠI ĐỒ (%)</label>
                    <input class="form-control" type="number" min="0" max="1000" name="drop_rate_percent" value="<?= (int) ($config['drop_rate_percent'] ?? 100) ?>" required>
                    <p class="help">100% = đúng tỉ lệ từng dòng; 200% = gấp đôi; tối đa vẫn là 100% thực tế.</p>
                </div>
                <div class="form-group">
                    <label>GIỜ BẢO TRÌ HÀNG NGÀY</label>
                    <input class="form-control" type="time" name="maintenance_time" value="<?= admin_escape(substr((string) ($config['maintenance_time'] ?? '04:30'), 0, 5)) ?>" required>
                </div>
                <div class="form-group">
                    <label>ĐẾM NGƯỢC BẢO TRÌ (GIÂY)</label>
                    <input class="form-control" type="number" min="10" max="3600" name="maintenance_countdown_seconds" value="<?= (int) ($config['maintenance_countdown_seconds'] ?? 300) ?>" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-control"><input type="checkbox" name="auto_maintenance_enabled" <?= (int) ($config['auto_maintenance_enabled'] ?? 0) === 1 ? 'checked' : '' ?>> Bật bảo trì tự động mỗi ngày</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-control"><input type="checkbox" name="boss_watchdog_enabled" <?= (int) ($config['boss_watchdog_enabled'] ?? 1) === 1 ? 'checked' : '' ?>> Tự gọi lại boss khi update lỗi hoặc bị kẹt</label>
                </div>
                <div class="form-group">
                    <label>THỜI GIAN XÁC ĐỊNH BOSS KẸT (GIÂY)</label>
                    <input class="form-control" type="number" min="10" max="3600" name="boss_stuck_seconds" value="<?= (int) ($config['boss_stuck_seconds'] ?? 120) ?>" required>
                </div>
                <div class="form-group">
                    <label>CHU KỲ ĐỌC DATABASE (GIÂY)</label>
                    <input class="form-control" type="number" min="2" max="60" name="config_refresh_seconds" value="<?= (int) ($config['config_refresh_seconds'] ?? 5) ?>" required>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu và nạp lại cấu hình</button></div>
            </form>
        </div>
    </section>

    <div class="stack">
        <section class="panel maintenance-panel <?= $maintenanceMode ? 'is-active' : '' ?>">
            <div class="panel-head"><div><h2>Bảo trì &amp; kick session</h2><p>Lưu dữ liệu trước khi ngắt kết nối người chơi thường</p></div><span class="spacer"></span><span class="badge <?= $maintenanceMode ? 'badge-orange' : 'badge-green' ?>"><?= $maintenanceMode ? 'CHỈ ADMIN' : 'ĐANG MỞ' ?></span></div>
            <div class="panel-body">
                <?php if ($maintenanceMode): ?>
                    <div class="maintenance-warning active"><strong>Đang khóa login người chơi</strong><p>Chỉ account có <code>is_admin=1</code> được phép đăng nhập. Khởi động lại server cũng tự xóa chế độ này.</p></div>
                    <div class="maintenance-actions">
                        <form method="post" data-confirm="Thử lưu và kick lại các session người chơi thường còn sót?">
                            <?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="START_MAINTENANCE">
                            <button class="btn btn-maintenance-retry" type="submit" <?= !$isOnline ? 'disabled' : '' ?>>↻ THỬ LƯU &amp; KICK LẠI</button>
                        </form>
                        <form method="post" data-confirm="Kết thúc bảo trì và cho phép toàn bộ người chơi đăng nhập lại?">
                            <?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="STOP_MAINTENANCE">
                            <button class="btn btn-maintenance-end" type="submit" <?= !$isOnline ? 'disabled' : '' ?>>✓ KẾT THÚC BẢO TRÌ</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="maintenance-warning"><strong>Kick toàn bộ người chơi thường</strong><p>Server sẽ bật khóa login trước, lưu từng nhân vật tối đa 3 lần rồi mới kick. Admin đang online được giữ lại.</p></div>
                    <form method="post" data-confirm="Bật bảo trì ngay? Người chơi thường sẽ được lưu dữ liệu và kick; chỉ admin được đăng nhập.">
                        <?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="START_MAINTENANCE">
                        <button class="btn btn-maintenance" type="submit" <?= !$isOnline ? 'disabled' : '' ?>>⚠ BẢO TRÌ · KICK ALL</button>
                    </form>
                <?php endif; ?>
                <?php if (!$isOnline): ?><p class="help maintenance-help">Button đang khóa vì game server không có heartbeat hợp lệ.</p><?php endif; ?>
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Cứu hộ boss</h2><p>Đưa boss về trạng thái sạch và gọi lại an toàn</p></div></div>
            <div class="panel-body">
                <form class="form-grid" method="post">
                    <?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="RESPAWN_BOSS">
                    <div class="form-group full"><label>CHỌN BOSS CẦN GỌI LẠI</label>
                        <select class="form-control" name="boss_id" required <?= !$rescueBosses ? 'disabled' : '' ?>>
                            <?php if ($rescueBosses): game_render_boss_options($rescueBosses, 'instance'); else: ?>
                                <option>Chưa có instance — cần khởi động JAR mới</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-actions"><button class="btn btn-blue" type="submit" <?= !$rescueBosses ? 'disabled' : '' ?>>Gọi lại boss đã chọn</button></div>
                </form>
                <div class="server-command-row">
                    <form method="post"><?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="RELOAD_CONFIG"><button class="btn btn-secondary" type="submit">Nạp lại cấu hình</button></form>
                    <form method="post" data-confirm="Gọi lại toàn bộ boss đang quản lý?"><?= admin_csrf_field() ?><input type="hidden" name="action" value="server_command"><input type="hidden" name="command_type" value="RESPAWN_ALL"><button class="btn btn-danger" type="submit">Gọi lại toàn bộ boss</button></form>
                </div>
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Trạng thái đồng bộ</h2><p>Heartbeat và dữ liệu server gần nhất</p></div></div>
            <div class="panel-body quick-list">
                <div class="quick-item"><span class="quick-icon">♥</span><span><strong>Heartbeat</strong><small><?= admin_escape(admin_datetime($runtime['last_heartbeat'] ?? null)) ?></small></span><span class="badge <?= $isOnline ? 'badge-green' : 'badge-red' ?>"><?= $isOnline ? 'Tốt' : 'Mất kết nối' ?></span></div>
                <div class="quick-item"><span class="quick-icon">↻</span><span><strong>Nạp cấu hình</strong><small><?= admin_escape(admin_datetime($runtime['last_config_load'] ?? null)) ?></small></span></div>
                <div class="quick-item"><span class="quick-icon">⚙</span><span><strong>Cập nhật bởi</strong><small><?= admin_escape($config['updated_by'] ?? 'Hệ thống') ?> · <?= admin_escape(admin_datetime($config['updated_at'] ?? null)) ?></small></span></div>
            </div>
        </section>
    </div>
</div>

<section class="panel">
    <div class="panel-head"><div><h2>Đồ Thần Linh theo lượt boss</h2><p>Boss 12h và 14h giữ nguyên cơ chế riêng; mỗi nhóm phải cộng đúng 100%</p></div></div>
    <div class="panel-body">
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="save_divine_turn">
            <div class="form-group full">
                <label><input type="checkbox" name="divine_turn_enabled" value="1" <?= (int) $divineTurn['enabled'] === 1 ? 'checked' : '' ?>> BẬT RƠI ĐỒ THẦN LINH THEO LƯỢT</label>
            </div>
            <?php
            $divineFields = [
                'one_zero_bp' => '1 boss · 0 món', 'one_one_bp' => '1 boss · 1 món', 'one_two_bp' => '1 boss · 2 món',
                'two_zero_bp' => '2 boss/hình thái · 0 món', 'two_one_bp' => '2 boss/hình thái · 1 món', 'two_two_bp' => '2 boss/hình thái · 2 món',
                'multi_zero_bp' => 'Từ 3 boss/hình thái · 0 món', 'multi_one_bp' => 'Từ 3 boss/hình thái · 1 món',
                'multi_two_bp' => 'Từ 3 boss/hình thái · 2 món', 'multi_three_bp' => 'Từ 3 boss/hình thái · 3 món',
            ];
            foreach ($divineFields as $field => $label):
            ?>
                <div class="form-group"><label><?= admin_escape($label) ?> (%)</label>
                    <input class="form-control" type="number" min="0" max="100" step="0.01"
                           name="<?= admin_escape($field) ?>"
                           value="<?= admin_escape(number_format((int) $divineTurn[$field] / 100, 2, '.', '')) ?>" required>
                </div>
            <?php endforeach; ?>
            <div class="form-group"><label>SỐ LƯỢT TRẮNG TRƯỚC KHI BẢO HIỂM</label>
                <input class="form-control" type="number" min="1" max="100" name="pity_blank_turns"
                       value="<?= (int) $divineTurn['pity_blank_turns'] ?>" required>
                <p class="help">Mặc định 5: sau 5 lượt liên tiếp không rơi, lượt thứ 6 chắc chắn có 1 món.</p>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu tỷ lệ theo lượt</button></div>
        </form>
    </div>
</section>

<section class="panel server-drop-builder">
    <div class="panel-head"><div><h2>Thêm vật phẩm cho boss</h2><p>Mỗi lần lưu tạo một dòng; cùng một boss có thể thêm bao nhiêu item tùy ý</p></div></div>
    <div class="panel-body">
        <form class="form-grid drop-add-grid" method="post" data-drop-form>
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="add_drop">
            <div class="form-group">
                <label>BOSS</label>
                <select class="form-control" name="boss_id" required>
                    <?php game_render_boss_options($bosses, 'drop'); ?>
                </select>
            </div>
            <div class="form-group">
                <label>LOẠI PHẦN THƯỞNG</label>
                <select class="form-control" name="drop_kind" data-drop-kind>
                    <option value="ITEM">Item cụ thể trong database</option>
                </select>
            </div>
            <div class="form-group" data-item-field>
                <label>ITEM</label>
                <input class="form-control" type="number" min="0" max="32767" name="item_id" list="game-item-list" placeholder="Nhập ID hoặc chọn gợi ý" required>
                <datalist id="game-item-list">
                    <?php foreach ($items as $item): ?><option value="<?= (int) $item['id'] ?>"><?= admin_escape($item['NAME']) ?></option><?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label>TỈ LỆ GỐC (%)</label>
                <input class="form-control" type="number" min="0" max="100" step="0.01" name="chance_percent" value="1" required>
                <p class="help">Hỗ trợ chính xác 0,01%. Hệ số drop toàn server sẽ nhân thêm vào.</p>
            </div>
            <div class="form-group">
                <label>SỐ LƯỢNG TỐI THIỂU</label>
                <input class="form-control" type="number" min="1" max="9999" name="quantity_min" value="1" required>
            </div>
            <div class="form-group">
                <label>SỐ LƯỢNG TỐI ĐA</label>
                <input class="form-control" type="number" min="1" max="9999" name="quantity_max" value="1" required>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Thêm item vào boss</button></div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><h2>Danh sách rơi đồ theo boss</h2><p>Đồ Thần Linh được random đủ áo, quần, găng, giày, nhẫn và hành tinh/chỉ số</p></div><span class="spacer"></span><span class="badge badge-blue"><?= count($drops) ?> cấu hình</span></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Boss</th><th>Vật phẩm</th><th>Tỉ lệ gốc</th><th>Số lượng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($drops as $drop): ?>
                <tr>
                    <td><strong><?= admin_escape(game_boss_label($drop)) ?></strong><br><small class="text-muted mono">ID <?= (int) $drop['boss_id'] ?></small></td>
                    <td><?php if ($drop['drop_kind'] === 'DIVINE_RANDOM'): ?><span class="badge badge-orange">Đồ Thần Linh ngẫu nhiên</span><?php else: ?><strong><?= admin_escape($drop['item_name'] ?? 'Item không tồn tại') ?></strong><br><small class="text-muted mono">Item <?= (int) $drop['item_id'] ?></small><?php endif; ?></td>
                    <td><strong><?= admin_escape(number_format((int) $drop['chance_bp'] / 100, 2, ',', '.')) ?>%</strong><br><small class="text-muted">Thực tế hiện tại: <?= admin_escape(number_format(min(100, ((int) $drop['chance_bp'] / 100) * ((int) ($config['drop_rate_percent'] ?? 100) / 100)), 2, ',', '.')) ?>%</small></td>
                    <td><?= (int) $drop['quantity_min'] === (int) $drop['quantity_max'] ? (int) $drop['quantity_min'] : ((int) $drop['quantity_min'] . '–' . (int) $drop['quantity_max']) ?></td>
                    <td><span class="badge <?= (int) $drop['enabled'] === 1 ? 'badge-green' : 'badge-gray' ?>"><?= (int) $drop['enabled'] === 1 ? 'Đang bật' : 'Đã tắt' ?></span></td>
                    <td><div class="actions">
                        <form method="post"><?= admin_csrf_field() ?><input type="hidden" name="action" value="toggle_drop"><input type="hidden" name="drop_id" value="<?= (int) $drop['id'] ?>"><button class="btn btn-sm btn-secondary" type="submit"><?= (int) $drop['enabled'] === 1 ? 'Tắt' : 'Bật' ?></button></form>
                        <form method="post" data-confirm="Xóa hẳn vật phẩm này khỏi boss?"><?= admin_csrf_field() ?><input type="hidden" name="action" value="delete_drop"><input type="hidden" name="drop_id" value="<?= (int) $drop['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Xóa</button></form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$drops): ?><tr><td colspan="6"><div class="empty"><strong>Chưa có vật phẩm tùy chỉnh</strong>Hãy thêm item hoặc đồ Thần Linh ngẫu nhiên ở biểu mẫu phía trên.</div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel server-command-log">
    <div class="panel-head"><div><h2>Lệnh server gần đây</h2><p>Theo dõi lệnh đã nhận, hoàn tất hoặc thất bại</p></div></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>ID</th><th>Lệnh</th><th>Boss</th><th>Người gửi</th><th>Trạng thái</th><th>Kết quả</th><th>Thời gian</th></tr></thead>
        <tbody><?php foreach ($commands as $command): ?><tr>
            <td class="mono">#<?= (int) $command['id'] ?></td>
            <td><?= admin_escape($command['command_type']) ?></td>
            <td><?= $command['boss_id'] === null ? '—' : (int) $command['boss_id'] ?></td>
            <td><?= admin_escape($command['requested_by']) ?></td>
            <td><span class="badge <?= $command['status'] === 'DONE' ? 'badge-green' : ($command['status'] === 'FAILED' ? 'badge-red' : 'badge-orange') ?>"><?= admin_escape($command['status']) ?></span></td>
            <td><?= admin_escape($command['result_message'] ?? 'Đang chờ server') ?></td>
            <td class="nowrap"><?= admin_escape(admin_datetime($command['created_at'])) ?></td>
        </tr><?php endforeach; ?></tbody>
    </table></div>
</section>

<script>
(() => {
    const kind = document.querySelector('[data-drop-kind]');
    const itemField = document.querySelector('[data-item-field]');
    const itemInput = itemField?.querySelector('input');
    const sync = () => {
        const divine = kind?.value === 'DIVINE_RANDOM';
        if (itemField) itemField.hidden = divine;
        if (itemInput) {
            itemInput.disabled = divine;
            itemInput.required = !divine;
        }
    };
    kind?.addEventListener('change', sync);
    sync();
})();
</script>
<?php admin_render_footer(); ?>
