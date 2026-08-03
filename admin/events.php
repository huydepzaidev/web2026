<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

function event_key_from_request(string $source = 'get'): string
{
    $values = $source === 'post' ? $_POST : $_GET;
    $key = trim((string) ($values['event'] ?? ''));
    if ($key !== '' && !preg_match('/^[a-z0-9_]{1,40}$/', $key)) {
        throw new RuntimeException('Mã sự kiện không hợp lệ.');
    }
    return $key;
}

function event_status_badge(array $event): string
{
    if (!empty($event['pending_status'])) {
        return '<span class="badge badge-orange">Đang xử lý</span>';
    }
    return (int) $event['enabled'] === 1
        ? '<span class="badge badge-green">Đang bật</span>'
        : '<span class="badge badge-gray">Đã tắt</span>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    try {
        $eventKey = event_key_from_request('post');
        if ($eventKey === '') {
            throw new RuntimeException('Chưa chọn sự kiện.');
        }
        if (($_POST['acknowledge_cleanup'] ?? '') !== '1') {
            throw new RuntimeException('Bạn phải xác nhận đã hiểu dữ liệu vật phẩm event sẽ bị reset.');
        }

        $conn->begin_transaction();
        try {
            $event = admin_one(
                'SELECT event_key, event_name, enabled FROM game_event_catalog WHERE event_key=? FOR UPDATE',
                's',
                [$eventKey]
            );
            if (!$event) {
                throw new RuntimeException('Không tìm thấy sự kiện.');
            }
            $pending = (int) admin_scalar(
                'SELECT COUNT(*) FROM game_event_command WHERE event_key=? AND status IN ("PENDING","PROCESSING")',
                's',
                [$eventKey]
            );
            if ($pending > 0) {
                throw new RuntimeException('Sự kiện này đang có lệnh chờ xử lý.');
            }
            $targetEnabled = (int) $event['enabled'] === 1 ? 0 : 1;
            admin_execute(
                'INSERT INTO game_event_command (event_key, target_enabled, requested_by) VALUES (?,?,?)',
                'sis',
                [$eventKey, $targetEnabled, (string) $admin_user['username']]
            );
            $commandId = (int) $conn->insert_id;
            admin_execute(
                'UPDATE game_event_catalog SET updated_by=? WHERE event_key=?',
                'ss',
                [(string) $admin_user['username'], $eventKey]
            );
            $conn->commit();

            admin_audit(
                $targetEnabled === 1 ? 'Yêu cầu bật sự kiện' : 'Yêu cầu tắt sự kiện',
                'game_event_catalog',
                null,
                ['event_key' => $eventKey, 'command_id' => $commandId, 'target_enabled' => $targetEnabled]
            );
            admin_flash(
                'success',
                'Đã gửi lệnh ' . ($targetEnabled === 1 ? 'bật' : 'tắt') . ' “' .
                (string) $event['event_name'] . '”. Game server sẽ reset dữ liệu rồi cập nhật trạng thái.'
            );
        } catch (Throwable $error) {
            $conn->rollback();
            throw $error;
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
    }
    admin_redirect('events.php?event=' . rawurlencode((string) ($_POST['event'] ?? '')));
}

$events = admin_all(
    'SELECT e.*,
       (SELECT COUNT(*) FROM game_event_item i WHERE i.event_key=e.event_key) AS item_count,
       (SELECT COUNT(*) FROM game_event_npc n WHERE n.event_key=e.event_key) AS npc_count,
       (SELECT COUNT(*) FROM game_event_boss b WHERE b.event_key=e.event_key) AS boss_count,
       (SELECT c.status FROM game_event_command c
        WHERE c.event_key=e.event_key AND c.status IN ("PENDING","PROCESSING")
        ORDER BY c.id DESC LIMIT 1) AS pending_status
     FROM game_event_catalog e ORDER BY e.sort_order,e.event_name'
);

$selectedKey = event_key_from_request();
if ($selectedKey === '' && $events) {
    $selectedKey = (string) $events[0]['event_key'];
}
$selected = null;
foreach ($events as $candidate) {
    if ((string) $candidate['event_key'] === $selectedKey) {
        $selected = $candidate;
        break;
    }
}
if (!$selected && $events) {
    $selected = $events[0];
    $selectedKey = (string) $selected['event_key'];
}

$items = $selected ? admin_all(
    'SELECT ei.item_id,ei.item_role,ei.purge_on_reset,i.NAME AS item_name,i.type
     FROM game_event_item ei
     LEFT JOIN item_template i ON i.id=ei.item_id
     WHERE ei.event_key=? ORDER BY ei.item_role,i.NAME,ei.item_id',
    's',
    [$selectedKey]
) : [];
$npcs = $selected ? admin_all(
    'SELECT en.*,n.NAME AS npc_name,m.NAME AS map_name
     FROM game_event_npc en
     LEFT JOIN npc_template n ON n.id=en.npc_id
     LEFT JOIN map_template m ON m.id=en.map_id
     WHERE en.event_key=? ORDER BY en.managed_runtime DESC,en.npc_id,en.map_id',
    's',
    [$selectedKey]
) : [];
$bosses = $selected ? admin_all(
    'SELECT eb.*,bc.boss_name,bc.active_instances,bc.last_seen_at
     FROM game_event_boss eb
     LEFT JOIN game_boss_catalog bc ON bc.boss_id=eb.boss_id
     WHERE eb.event_key=? ORDER BY eb.boss_id',
    's',
    [$selectedKey]
) : [];
$commands = $selected ? admin_all(
    'SELECT c.*,
       (SELECT COUNT(*) FROM game_event_player_backup b WHERE b.command_id=c.id) AS backup_players
     FROM game_event_command c WHERE c.event_key=? ORDER BY c.id DESC LIMIT 15',
    's',
    [$selectedKey]
) : [];

admin_render_header(
    'Control panel sự kiện',
    'events',
    'Chọn sự kiện để xem toàn bộ item, NPC, boss và điều khiển trạng thái trong game'
);
?>

<section class="panel event-picker-panel">
    <div class="panel-body">
        <form method="get" class="event-picker-form">
            <div class="form-group full">
                <label for="event-picker">CHỌN SỰ KIỆN</label>
                <select class="form-control" id="event-picker" name="event" onchange="this.form.submit()">
                    <?php foreach ($events as $event): ?>
                        <option value="<?= admin_escape($event['event_key']) ?>" <?= $selectedKey === $event['event_key'] ? 'selected' : '' ?>>
                            <?= admin_escape($event['event_name']) ?> · <?= (int) $event['enabled'] === 1 ? 'Đang bật' : 'Đã tắt' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript><button class="btn btn-primary" type="submit">Xem sự kiện</button></noscript>
        </form>
    </div>
</section>

<?php if ($selected): ?>
<div class="metric-grid event-metrics">
    <div class="metric-card"><span class="metric-icon <?= (int) $selected['enabled'] === 1 ? 'green' : 'red' ?>">●</span><span><small>TRẠNG THÁI</small><strong><?= (int) $selected['enabled'] === 1 ? 'Đang bật' : 'Đã tắt' ?></strong></span></div>
    <div class="metric-card"><span class="metric-icon blue">◆</span><span><small>VẬT PHẨM</small><strong><?= count($items) ?></strong></span></div>
    <div class="metric-card"><span class="metric-icon orange">♟</span><span><small>NPC / BOSS</small><strong><?= count($npcs) ?> / <?= count($bosses) ?></strong></span></div>
    <div class="metric-card"><span class="metric-icon green">↻</span><span><small>SỐ LẦN RESET</small><strong><?= (int) $selected['reset_version'] ?></strong></span></div>
</div>

<div class="grid-2 event-overview-grid">
    <section class="panel">
        <div class="panel-head"><div><h2><?= admin_escape($selected['event_name']) ?></h2><p class="mono"><?= admin_escape($selected['event_key']) ?></p></div><?= event_status_badge($selected) ?></div>
        <div class="panel-body event-summary">
            <p><?= admin_escape($selected['summary']) ?></p>
            <dl class="event-facts">
                <div><dt>Thay đổi gần nhất</dt><dd><?= admin_escape(admin_datetime($selected['last_changed_at'])) ?></dd></div>
                <div><dt>Người yêu cầu</dt><dd><?= admin_escape($selected['updated_by'] ?: 'Hệ thống') ?></dd></div>
                <div><dt>Kết quả gần nhất</dt><dd><?= admin_escape($selected['last_result'] ?: 'Chưa có thao tác') ?></dd></div>
            </dl>
        </div>
    </section>

    <section class="panel event-danger-panel">
        <div class="panel-head"><div><h2><?= (int) $selected['enabled'] === 1 ? 'Tắt sự kiện' : 'Bật sự kiện' ?></h2><p>Game server xử lý trực tiếp, không sửa dữ liệu từ PHP</p></div></div>
        <div class="panel-body">
            <div class="event-warning">
                <strong>Reset toàn bộ trước khi đổi trạng thái</strong>
                <p>Item trong trang bị, hành trang, rương, vòng quay, mua lại, pet và dưới đất sẽ bị xóa. Bộ đếm rơi đồ event cũng về 0. Dữ liệu gốc được sao lưu theo mã lệnh.</p>
            </div>
            <form method="post" data-confirm="<?= admin_escape(((int) $selected['enabled'] === 1 ? 'TẮT' : 'BẬT') . ' sự kiện ' . $selected['event_name'] . '? Toàn bộ vật phẩm liên quan trong mọi tài khoản sẽ được reset.') ?>">
                <?= admin_csrf_field() ?>
                <input type="hidden" name="event" value="<?= admin_escape($selectedKey) ?>">
                <label class="checkbox-control event-ack"><input type="checkbox" name="acknowledge_cleanup" value="1" required> Tôi hiểu thao tác này reset vật phẩm event của toàn bộ người chơi.</label>
                <div class="form-actions">
                    <button class="btn <?= (int) $selected['enabled'] === 1 ? 'btn-danger' : 'btn-primary' ?>" type="submit" <?= !empty($selected['pending_status']) ? 'disabled' : '' ?>>
                        <?= !empty($selected['pending_status']) ? 'Đang chờ game server…' : ((int) $selected['enabled'] === 1 ? 'Tắt và reset sự kiện' : 'Reset và bật sự kiện') ?>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><div><h2>Vật phẩm sự kiện</h2><p>Tất cả item dưới đây bị khóa nhận khi event tắt và bị xóa trong mỗi lần reset</p></div><span class="badge badge-blue"><?= count($items) ?> item</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>ID</th><th>Vật phẩm</th><th>Nhóm</th><th>Type</th><th>Khi reset</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?><tr>
            <td class="mono"><?= (int) $item['item_id'] ?></td>
            <td><strong><?= admin_escape($item['item_name'] ?: 'Item không tồn tại') ?></strong></td>
            <td><?= admin_escape($item['item_role']) ?></td>
            <td><?= $item['type'] === null ? '—' : (int) $item['type'] ?></td>
            <td><span class="badge <?= (int) $item['purge_on_reset'] === 1 ? 'badge-red' : 'badge-gray' ?>"><?= (int) $item['purge_on_reset'] === 1 ? 'Xóa' : 'Giữ' ?></span></td>
        </tr><?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="5"><div class="empty"><strong>Không có item riêng</strong>Sự kiện này chỉ điều khiển logic hoặc mốc thưởng.</div></td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>

<div class="grid-2 event-resource-grid">
    <section class="panel">
        <div class="panel-head"><div><h2>NPC liên quan</h2><p>NPC riêng được gỡ/tạo lại; NPC dùng chung chỉ hiển thị tham chiếu</p></div></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>NPC</th><th>Vị trí</th><th>Quản lý</th></tr></thead>
            <tbody>
            <?php foreach ($npcs as $npc): ?><tr>
                <td><strong><?= admin_escape($npc['npc_name'] ?: ('NPC ' . $npc['npc_id'])) ?></strong><br><small class="text-muted"><?= admin_escape($npc['npc_role']) ?></small></td>
                <td><?= $npc['map_id'] === null ? 'Theo logic game' : admin_escape(($npc['map_name'] ?: 'Map') . ' · ' . $npc['map_id'] . ' (' . $npc['x'] . ',' . $npc['y'] . ')') ?></td>
                <td><span class="badge <?= (int) $npc['managed_runtime'] === 1 ? 'badge-green' : 'badge-gray' ?>"><?= (int) $npc['managed_runtime'] === 1 ? 'Bật/tắt runtime' : 'NPC dùng chung' ?></span></td>
            </tr><?php endforeach; ?>
            <?php if (!$npcs): ?><tr><td colspan="3"><div class="empty">Không có NPC riêng.</div></td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Boss sự kiện</h2><p>Boss bị đưa khỏi map khi tắt và tạo sạch khi bật</p></div></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Boss</th><th>Cấu hình</th><th>Runtime</th></tr></thead>
            <tbody>
            <?php foreach ($bosses as $boss): ?><tr>
                <td><strong><?= admin_escape($boss['boss_name'] ?: $boss['boss_role']) ?></strong><br><small class="text-muted mono">ID <?= (int) $boss['boss_id'] ?></small></td>
                <td><?= (int) $boss['quantity'] > 0 ? ((int) $boss['quantity'] . ' instance') : 'Sinh kèm boss cha' ?></td>
                <td><span class="badge <?= (int) ($boss['active_instances'] ?? 0) > 0 ? 'badge-green' : 'badge-gray' ?>"><?= (int) ($boss['active_instances'] ?? 0) ?> đang chạy</span></td>
            </tr><?php endforeach; ?>
            <?php if (!$bosses): ?><tr><td colspan="3"><div class="empty">Không có boss riêng.</div></td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><div><h2>Lịch sử điều khiển</h2><p>Backup player được giữ theo từng mã lệnh</p></div></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Lệnh</th><th>Thao tác</th><th>Người gửi</th><th>Trạng thái</th><th>Backup</th><th>Kết quả</th><th>Thời gian</th></tr></thead>
        <tbody>
        <?php foreach ($commands as $command): ?><tr>
            <td class="mono">#<?= (int) $command['id'] ?></td>
            <td><strong><?= (int) $command['target_enabled'] === 1 ? 'Bật' : 'Tắt' ?></strong></td>
            <td><?= admin_escape($command['requested_by']) ?></td>
            <td><span class="badge <?= $command['status'] === 'DONE' ? 'badge-green' : ($command['status'] === 'FAILED' ? 'badge-red' : 'badge-orange') ?>"><?= admin_escape($command['status']) ?></span></td>
            <td><?= (int) $command['backup_players'] ?> player</td>
            <td><?= admin_escape($command['result_message'] ?: 'Đang chờ game server') ?></td>
            <td class="nowrap"><?= admin_escape(admin_datetime($command['created_at'])) ?></td>
        </tr><?php endforeach; ?>
        <?php if (!$commands): ?><tr><td colspan="7"><div class="empty">Chưa có thao tác bật/tắt.</div></td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>
<?php else: ?>
<section class="panel"><div class="panel-body"><div class="empty"><strong>Chưa có catalog sự kiện</strong>Hãy chạy migration event control panel.</div></div></section>
<?php endif; ?>

<?php admin_render_footer(); ?>
