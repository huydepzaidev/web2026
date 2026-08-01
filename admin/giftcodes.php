<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

function giftcode_validate_rewards(string $rawJson): array
{
    $rewards = json_decode($rawJson, true);
    if (!is_array($rewards) || !$rewards) {
        throw new RuntimeException('Giftcode phải có ít nhất một phần thưởng.');
    }
    if (count($rewards) > 50) {
        throw new RuntimeException('Mỗi giftcode được chọn tối đa 50 phần thưởng.');
    }

    $itemIds = [];
    $optionIds = [];
    $normalized = [];
    $seenItems = [];
    foreach ($rewards as $reward) {
        if (!is_array($reward)) throw new RuntimeException('Dữ liệu phần thưởng không hợp lệ.');
        $id = filter_var($reward['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($reward['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < -3) throw new RuntimeException('ID vật phẩm không hợp lệ.');
        if ($quantity === false || $quantity < 1 || $quantity > 2000000000) {
            throw new RuntimeException('Số lượng vật phẩm phải từ 1 đến 2.000.000.000.');
        }
        if (isset($seenItems[$id])) {
            throw new RuntimeException('Một vật phẩm chỉ được xuất hiện một lần trong giftcode.');
        }
        $seenItems[$id] = true;
        if ($id >= 0) $itemIds[] = $id;

        $options = $reward['options'] ?? [];
        if (!is_array($options)) throw new RuntimeException('Danh sách option không hợp lệ.');
        if ($id < 0 && $options) throw new RuntimeException('Vàng/ngọc không sử dụng option.');
        if (count($options) > 30) throw new RuntimeException('Mỗi vật phẩm được chọn tối đa 30 option.');

        $normalizedOptions = [];
        $seenOptions = [];
        foreach ($options as $option) {
            if (!is_array($option)) throw new RuntimeException('Dữ liệu option không hợp lệ.');
            $optionId = filter_var($option['id'] ?? null, FILTER_VALIDATE_INT);
            $param = filter_var($option['param'] ?? null, FILTER_VALIDATE_INT);
            if ($optionId === false || $optionId < 0) throw new RuntimeException('ID option không hợp lệ.');
            if ($param === false || $param < -2147483648 || $param > 2147483647) {
                throw new RuntimeException('Param option vượt giới hạn số nguyên của server.');
            }
            if (isset($seenOptions[$optionId])) {
                throw new RuntimeException('Một vật phẩm không thể có hai option cùng loại.');
            }
            $seenOptions[$optionId] = true;
            $optionIds[] = $optionId;
            $normalizedOptions[] = ['id' => $optionId, 'param' => $param];
        }
        $normalized[] = ['id' => $id, 'quantity' => $quantity, 'options' => $normalizedOptions];
    }

    if ($itemIds) {
        $uniqueIds = array_values(array_unique($itemIds));
        $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
        $existing = admin_all(
            "SELECT id FROM item_template WHERE id IN ($placeholders)",
            str_repeat('i', count($uniqueIds)),
            $uniqueIds
        );
        if (count($existing) !== count($uniqueIds)) {
            throw new RuntimeException('Có vật phẩm không tồn tại trong item_template.');
        }
    }
    if ($optionIds) {
        $uniqueOptions = array_values(array_unique($optionIds));
        $placeholders = implode(',', array_fill(0, count($uniqueOptions), '?'));
        $existing = admin_all(
            "SELECT id FROM item_option_template WHERE id IN ($placeholders)",
            str_repeat('i', count($uniqueOptions)),
            $uniqueOptions
        );
        if (count($existing) !== count($uniqueOptions)) {
            throw new RuntimeException('Có option không tồn tại trong item_option_template.');
        }
    }
    return $normalized;
}

function giftcode_reward_summary(string $detail, array $itemNames): string
{
    $rewards = json_decode($detail, true);
    if (!is_array($rewards)) return 'Dữ liệu cũ không đúng định dạng';
    $parts = [];
    foreach ($rewards as $reward) {
        $id = (int) ($reward['id'] ?? 0);
        $quantity = (int) ($reward['quantity'] ?? 0);
        $name = $itemNames[$id] ?? ('Item #' . $id);
        $optionCount = is_array($reward['options'] ?? null) ? count($reward['options']) : 0;
        $parts[] = admin_number($quantity) . ' × ' . $name . ($optionCount ? " ($optionCount option)" : '');
    }
    return implode(' · ', $parts);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'giftcode_id', FILTER_VALIDATE_INT);
    try {
        if ($action === 'save') {
            $code = trim((string) ($_POST['code'] ?? ''));
            $unlimited = isset($_POST['unlimited']);
            $count = $unlimited ? -1 : max(0, (int) ($_POST['count_left'] ?? 0));
            $rewards = giftcode_validate_rewards((string) ($_POST['rewards_json'] ?? ''));
            $detail = json_encode($rewards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $expiredInput = trim((string) ($_POST['expired'] ?? ''));
            if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $code)) {
                throw new RuntimeException('Giftcode chỉ gồm chữ, số, gạch ngang/gạch dưới và dài 3–50 ký tự.');
            }
            $timestamp = strtotime($expiredInput);
            if (!$timestamp) throw new RuntimeException('Thời hạn giftcode không hợp lệ.');
            $expired = date('Y-m-d H:i:s', $timestamp);

            $duplicate = admin_one(
                'SELECT id FROM giftcode WHERE UPPER(code) = UPPER(?) AND id <> ? LIMIT 1',
                'si',
                [$code, $id ?: 0]
            );
            if ($duplicate) throw new RuntimeException('Mã giftcode này đã tồn tại.');

            if ($id) {
                $existing = admin_one('SELECT code, detail FROM giftcode WHERE id = ?', 'i', [$id]);
                if (!$existing) throw new RuntimeException('Giftcode không tồn tại.');
                admin_execute(
                    'UPDATE giftcode SET code = ?, count_left = ?, detail = ?, expired = ? WHERE id = ?',
                    'sissi',
                    [$code, $count, $detail, $expired, $id]
                );
                admin_audit('Cập nhật giftcode', 'giftcode', $id, [
                    'code' => $code, 'count_left' => $count, 'expired' => $expired,
                    'reward_count' => count($rewards),
                ]);
                admin_flash('success', 'Đã cập nhật giftcode và phần thưởng.');
            } else {
                admin_execute(
                    'INSERT INTO giftcode (code, count_left, detail, expired) VALUES (?, ?, ?, ?)',
                    'siss',
                    [$code, $count, $detail, $expired]
                );
                global $conn;
                $newId = (int) $conn->insert_id;
                admin_audit('Tạo giftcode', 'giftcode', $newId, [
                    'code' => $code, 'count_left' => $count, 'expired' => $expired,
                    'reward_count' => count($rewards),
                ]);
                admin_flash('success', 'Đã tạo giftcode với ' . count($rewards) . ' phần thưởng.');
            }
        } elseif ($action === 'delete' && $id) {
            $gift = admin_one('SELECT code FROM giftcode WHERE id = ?', 'i', [$id]);
            if (!$gift) throw new RuntimeException('Giftcode không tồn tại.');
            admin_execute('DELETE FROM giftcode WHERE id = ?', 'i', [$id]);
            admin_audit('Xóa giftcode', 'giftcode', $id, ['code' => $gift['code']]);
            admin_flash('success', 'Đã xóa giftcode.');
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
        $returnPath = $id ? 'giftcodes.php?edit=' . $id : 'giftcodes.php?create=1';
        admin_redirect($returnPath);
    }
    admin_redirect('giftcodes.php');
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $editId ? admin_one('SELECT * FROM giftcode WHERE id = ?', 'i', [$editId]) : null;
$showForm = isset($_GET['create']) || $editing;
$giftcodes = admin_all('SELECT * FROM giftcode ORDER BY id DESC');
$activeCount = (int) admin_scalar('SELECT COUNT(*) FROM giftcode WHERE count_left <> 0 AND expired > NOW()');
$usesLeft = (int) admin_scalar('SELECT COALESCE(SUM(CASE WHEN count_left > 0 THEN count_left ELSE 0 END), 0) FROM giftcode WHERE expired > NOW()');

$options = admin_all('SELECT id, NAME AS name FROM item_option_template ORDER BY id');
foreach ($options as &$option) $option['id'] = (int) $option['id'];
unset($option);

$itemNames = [-1 => 'Vàng', -2 => 'Ngọc', -3 => 'Ngọc khóa'];
foreach (admin_all("SELECT id, NAME AS name FROM item_template WHERE NAME <> '' ORDER BY id") as $item) {
    $itemNames[(int) $item['id']] = $item['name'];
}

$initialRewards = [];
if ($editing) {
    $decoded = json_decode((string) $editing['detail'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $reward) {
            $rewardId = (int) ($reward['id'] ?? 0);
            $initialRewards[] = [
                'id' => $rewardId,
                'name' => $itemNames[$rewardId] ?? ('Item #' . $rewardId),
                'quantity' => max(1, (int) ($reward['quantity'] ?? 1)),
                'options' => is_array($reward['options'] ?? null) ? $reward['options'] : [],
            ];
        }
    }
}

admin_render_header('Quản lý giftcode', 'giftcodes', 'Chọn vật phẩm và option trực tiếp từ database');
?>
<section class="metric-grid">
    <article class="metric-card"><span class="metric-icon orange">⌘</span><div><small>TỔNG GIFTCODE</small><strong><?= admin_number(count($giftcodes)) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon green">✓</span><div><small>ĐANG CÒN HIỆU LỰC</small><strong><?= admin_number($activeCount) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon blue">∞</span><div><small>LƯỢT DÙNG CÓ GIỚI HẠN</small><strong><?= admin_number($usesLeft) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon red">×</span><div><small>HẾT HẠN / HẾT LƯỢT</small><strong><?= admin_number(count($giftcodes) - $activeCount) ?></strong></div></article>
</section>

<?php if ($showForm): ?>
<section class="panel" style="margin-bottom:20px">
    <div class="panel-head">
        <div><h2><?= $editing ? 'Chỉnh sửa giftcode' : 'Tạo giftcode mới' ?></h2><p>Không cần nhớ ID: tìm item và option bằng tên từ database</p></div>
        <span class="spacer"></span><a class="btn btn-secondary btn-sm" href="<?= admin_escape(admin_url('giftcodes.php')) ?>">Đóng</a>
    </div>
    <div class="panel-body">
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="save">
            <?php if ($editing): ?><input type="hidden" name="giftcode_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
            <div class="form-group"><label>MÃ GIFTCODE</label><input class="form-control mono" name="code" maxlength="50" value="<?= admin_escape($editing['code'] ?? '') ?>" placeholder="TANTHU2026" required></div>
            <div class="form-group"><label>SỐ LƯỢT CÒN LẠI</label><input class="form-control" type="number" min="0" max="2000000000" name="count_left" value="<?= (int) (($editing['count_left'] ?? 100) < 0 ? 100 : ($editing['count_left'] ?? 100)) ?>" required></div>
            <div class="form-group"><label>HẾT HẠN</label><input class="form-control" type="datetime-local" name="expired" value="<?= $editing ? admin_escape(date('Y-m-d\TH:i', strtotime($editing['expired']))) : admin_escape(date('Y-m-d\TH:i', strtotime('+30 days'))) ?>" required></div>
            <div class="form-group"><label>GIỚI HẠN</label><label class="checkbox-control"><input type="checkbox" name="unlimited" value="1" <?= $editing && (int) $editing['count_left'] < 0 ? 'checked' : '' ?>> Không giới hạn lượt sử dụng</label></div>

            <div class="form-group full gift-builder" data-gift-builder>
                <input type="hidden" name="rewards_json" id="rewards_json">
                <div class="builder-head">
                    <div><label>DANH SÁCH PHẦN THƯỞNG</label><p class="help">Có thể thêm nhiều vật phẩm; mỗi vật phẩm có nhiều option và param riêng.</p></div>
                    <button class="btn btn-primary" type="button" data-open-catalog>+ Chọn vật phẩm từ database</button>
                </div>
                <div class="reward-empty" data-reward-empty>
                    <strong>Chưa có phần thưởng</strong>
                    Bấm “Chọn vật phẩm từ database” để tìm theo tên hoặc ID.
                </div>
                <div class="reward-list" data-reward-list></div>
            </div>
            <div class="form-actions gift-form-actions">
                <span>Vật phẩm và option sẽ được kiểm tra lại với database trước khi lưu.</span>
                <a class="btn btn-secondary" href="<?= admin_escape(admin_url('giftcodes.php')) ?>">Hủy</a>
                <button class="btn btn-primary" type="submit"><?= $editing ? 'Lưu giftcode' : 'Tạo giftcode' ?></button>
            </div>
        </form>
    </div>
</section>

<div class="catalog-modal" id="itemCatalogModal" hidden>
    <div class="catalog-backdrop" data-close-catalog></div>
    <section class="catalog-dialog" role="dialog" aria-modal="true" aria-labelledby="catalogTitle">
        <header><div><h2 id="catalogTitle">Chọn vật phẩm</h2><p>Dữ liệu trực tiếp từ bảng item_template</p></div><button type="button" data-close-catalog>×</button></header>
        <div class="catalog-search"><input class="form-control" type="search" data-catalog-search placeholder="Nhập tên vật phẩm hoặc ID..."></div>
        <div class="catalog-results" data-catalog-results></div>
        <footer><button class="btn btn-secondary" type="button" data-catalog-prev>← Trước</button><span data-catalog-page>Trang 1/1</span><button class="btn btn-secondary" type="button" data-catalog-next>Tiếp →</button></footer>
    </section>
</div>
<script>
window.giftCodeBuilderConfig = <?= json_encode([
    'catalogUrl' => admin_url('api/catalog.php'),
    'options' => $options,
    'initialRewards' => $initialRewards,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= admin_escape(admin_url('assets/giftcode-builder.js?v=20260731-2')) ?>"></script>
<?php endif; ?>

<div class="toolbar"><a class="btn btn-primary" href="<?= admin_escape(admin_url('giftcodes.php?create=1')) ?>">+ Tạo giftcode bằng bảng chọn</a><span class="count"><?= count($giftcodes) ?> mã</span></div>
<section class="panel"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Giftcode</th><th>Phần thưởng</th><th>Còn lại</th><th>Trạng thái</th><th>Hết hạn</th><th class="text-right">Thao tác</th></tr></thead><tbody>
    <?php foreach ($giftcodes as $gift):
        $isUnlimited = (int) $gift['count_left'] < 0;
        $isActive = ((int) $gift['count_left'] !== 0) && strtotime($gift['expired']) > time();
    ?>
    <tr>
        <td><div class="code-value"><code><?= admin_escape($gift['code']) ?></code><button class="btn btn-secondary btn-sm" type="button" data-copy="<?= admin_escape($gift['code']) ?>">Sao chép</button></div></td>
        <td class="gift-summary"><?= admin_escape(mb_strimwidth(giftcode_reward_summary($gift['detail'], $itemNames), 0, 160, '…')) ?></td>
        <td><strong><?= $isUnlimited ? 'Không giới hạn' : admin_number($gift['count_left']) ?></strong></td>
        <td><?= $isActive ? '<span class="badge badge-green">Hiệu lực</span>' : '<span class="badge badge-red">Hết hạn</span>' ?></td>
        <td class="nowrap"><?= admin_escape(admin_datetime($gift['expired'])) ?></td>
        <td class="text-right"><div class="actions" style="justify-content:flex-end"><a class="btn btn-blue btn-sm" href="?edit=<?= (int) $gift['id'] ?>">Sửa</a><form method="post" data-confirm="Xóa giftcode <?= admin_escape($gift['code']) ?>?"><?= admin_csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="giftcode_id" value="<?= (int) $gift['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Xóa</button></form></div></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$giftcodes): ?><tr><td colspan="6"><div class="empty"><strong>Chưa có giftcode</strong>Tạo mã quà tặng đầu tiên cho người chơi.</div></td></tr><?php endif; ?>
    </tbody>
</table></div></section>
<?php admin_render_footer(); ?>
