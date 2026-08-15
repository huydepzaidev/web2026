<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/top-reward-rules.php';

function mailbox_rankings(): array
{
    return [
        'top_boss' => ['name' => 'Đại Thiên Sứ · Top săn Boss', 'score' => 'boss_count', 'period' => 'WEEKLY'],
        'summer' => ['name' => 'Đại Thiên Sứ · Top sự kiện', 'score' => 'point_summer_cards', 'period' => 'WEEKLY'],
        'top_power' => ['name' => 'Đại Thiên Sứ · Top sức mạnh', 'score' => 'power', 'period' => 'LIFETIME'],
        'top_task' => ['name' => 'Đại Thiên Sứ · Top nhiệm vụ', 'score' => 'task_id', 'period' => 'LIFETIME'],
        'childrens_day' => ['name' => 'Quốc tế Thiếu nhi', 'score' => 'point_sukien', 'period' => 'MANUAL'],
        'sugarcane' => ['name' => 'Nước mía', 'score' => 'point_sukien1', 'period' => 'MANUAL'],
        'fruit_ice_cream' => ['name' => 'Kem trái cây', 'score' => 'point_sukien2', 'period' => 'MANUAL'],
        'top_up' => ['name' => 'Đua Top nạp', 'score' => 'tongnap', 'period' => 'MANUAL'],
    ];
}

function mailbox_ranking_key(string $source): string
{
    $values = $source === 'post' ? $_POST : $_GET;
    $key = trim((string) ($values['ranking'] ?? 'top_boss'));
    if (!array_key_exists($key, mailbox_rankings())) {
        throw new RuntimeException('Loại bảng xếp hạng không hợp lệ.');
    }
    return $key;
}

function mailbox_reward_group_for_rank(int $rank): string
{
    if ($rank >= 1 && $rank <= 3) {
        return (string) $rank;
    }
    if ($rank >= 4 && $rank <= 10) {
        return '4-10';
    }
    throw new RuntimeException('Hạng Top phải từ 1 đến 10.');
}

function mailbox_ranking_period(
    string $rankingKey,
    ?string $manualKey = null,
    ?string $weeklyDate = null
): array
{
    $definition = mailbox_rankings()[$rankingKey] ?? null;
    if (!$definition) {
        throw new RuntimeException('Loại bảng xếp hạng không hợp lệ.');
    }
    $periodType = (string) $definition['period'];
    if ($periodType === 'LIFETIME') {
        return [
            'type' => 'LIFETIME',
            'key' => 'lifetime',
            'ranking_date' => null,
            'label' => 'Toàn máy chủ · chỉ trao một lần',
            'locked' => true,
        ];
    }
    if ($periodType === 'WEEKLY') {
        $zone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $today = new DateTimeImmutable('today', $zone);
        $currentWeekStart = $today->modify('-' . ((int) $today->format('N') - 1) . ' days');
        $dateText = trim((string) ($weeklyDate ?? $currentWeekStart->format('Y-m-d')));
        $weekStart = DateTimeImmutable::createFromFormat('!Y-m-d', $dateText, $zone);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (!$weekStart || ($dateErrors !== false
            && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $weekStart->format('Y-m-d') !== $dateText
            || (int) $weekStart->format('N') !== 1
            || $weekStart > $currentWeekStart) {
            throw new RuntimeException('Kỳ tuần không hợp lệ.');
        }
        $weekEnd = $weekStart->modify('+6 days');
        return [
            'type' => 'WEEKLY',
            'key' => 'week-' . $weekStart->format('Ymd'),
            'ranking_date' => $weekStart->format('Y-m-d'),
            'label' => 'Tuần ' . $weekStart->format('d/m/Y') . '–' . $weekEnd->format('d/m/Y'),
            'locked' => true,
        ];
    }
    $key = trim((string) ($manualKey ?? ($rankingKey . '-' . date('Ymd-Hi'))));
    if (!preg_match('/^[a-zA-Z0-9_-]{3,80}$/', $key)) {
        throw new RuntimeException('Mã đợt chốt chỉ gồm chữ, số, gạch ngang/gạch dưới và dài 3–80 ký tự.');
    }
    return [
        'type' => 'MANUAL',
        'key' => $key,
        'ranking_date' => null,
        'label' => 'Đợt do Admin đặt',
        'locked' => false,
    ];
}

function mailbox_weekly_period_options(string $rankingKey): array
{
    $rankingType = match ($rankingKey) {
        'top_boss' => 'BOSS',
        'summer' => 'SUMMER_EVENT',
        default => null,
    };
    if ($rankingType === null) {
        return [];
    }
    $rows = admin_all(
        'SELECT ranking_date,COUNT(*) AS player_count,SUM(score) AS total_score '
        . 'FROM daily_ranking_score WHERE ranking_type=? '
        . 'GROUP BY ranking_date ORDER BY ranking_date DESC LIMIT 12',
        's',
        [$rankingType]
    );
    $current = mailbox_ranking_period($rankingKey);
    $foundCurrent = false;
    foreach ($rows as &$row) {
        $row['period'] = mailbox_ranking_period($rankingKey, null, (string) $row['ranking_date']);
        if ($row['ranking_date'] === $current['ranking_date']) {
            $foundCurrent = true;
        }
    }
    unset($row);
    if (!$foundCurrent) {
        array_unshift($rows, [
            'ranking_date' => $current['ranking_date'],
            'player_count' => 0,
            'total_score' => 0,
            'period' => $current,
        ]);
    }
    return $rows;
}

function mailbox_existing_period_command(string $rankingKey, array $period): ?array
{
    if ($period['type'] === 'LIFETIME') {
        return admin_one(
            "SELECT id,status FROM top_reward_command WHERE ranking_key=? AND status<>'FAILED' ORDER BY id LIMIT 1",
            's',
            [$rankingKey]
        );
    }
    if ($period['type'] === 'WEEKLY') {
        return admin_one(
            "SELECT id,status FROM top_reward_command "
            . "WHERE ranking_key=? AND ranking_date=? AND status<>'FAILED' ORDER BY id LIMIT 1",
            'ss',
            [$rankingKey, $period['ranking_date']]
        );
    }
    return admin_one(
        "SELECT id,status FROM top_reward_command "
        . "WHERE ranking_key=? AND batch_key=? AND status<>'FAILED' LIMIT 1",
        'ss',
        [$rankingKey, $period['key']]
    );
}

function mailbox_ranking_preview(string $rankingKey, ?string $rankingDate = null): array
{
    $rankings = mailbox_rankings();
    $column = $rankings[$rankingKey]['score'];
    if ($rankingKey === 'top_up') {
        return admin_all(
            'SELECT p.id AS player_id,p.account_id,p.name,a.username,a.tongnap AS score '
            . 'FROM account a INNER JOIN player p ON p.account_id=a.id '
            . 'WHERE a.tongnap>0 AND a.ban=0 AND a.is_admin=0 '
            . 'ORDER BY a.tongnap DESC,p.id ASC LIMIT 10'
        );
    }
    if ($rankingKey === 'top_boss') {
        return admin_all(
            "SELECT p.id AS player_id,p.account_id,p.name,a.username,d.score "
            . 'FROM daily_ranking_score d INNER JOIN player p ON p.id=d.player_id '
            . 'INNER JOIN account a ON a.id=p.account_id '
            . 'WHERE d.ranking_date=? '
            . "AND d.ranking_type='BOSS' AND d.score>0 AND a.ban=0 AND a.is_admin=0 "
            . 'ORDER BY d.score DESC,p.id ASC LIMIT 10',
            's',
            [$rankingDate ?? mailbox_ranking_period($rankingKey)['ranking_date']]
        );
    }
    if ($rankingKey === 'summer') {
        return admin_all(
            "SELECT p.id AS player_id,p.account_id,p.name,a.username,d.score "
            . 'FROM daily_ranking_score d INNER JOIN player p ON p.id=d.player_id '
            . 'INNER JOIN account a ON a.id=p.account_id '
            . 'WHERE d.ranking_date=? '
            . "AND d.ranking_type='SUMMER_EVENT' AND d.score>0 AND a.ban=0 AND a.is_admin=0 "
            . 'ORDER BY d.score DESC,p.id ASC LIMIT 10',
            's',
            [$rankingDate ?? mailbox_ranking_period($rankingKey)['ranking_date']]
        );
    }
    if ($rankingKey === 'top_power') {
        return admin_all(
            "SELECT p.id AS player_id,p.account_id,p.name,a.username,"
            . "COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.data_point, '$[1]')) AS UNSIGNED),0) AS score "
            . 'FROM player p INNER JOIN account a ON a.id=p.account_id '
            . 'WHERE a.ban=0 AND a.is_admin=0 '
            . 'ORDER BY score DESC,p.id ASC LIMIT 10'
        );
    }
    if ($rankingKey === 'top_task') {
        return admin_all(
            "SELECT p.id AS player_id,p.account_id,p.name,a.username,"
            . "COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.data_task, '$[0]')) AS UNSIGNED),0) AS score "
            . 'FROM player p INNER JOIN account a ON a.id=p.account_id '
            . 'WHERE a.ban=0 AND a.is_admin=0 '
            . "ORDER BY score DESC,COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.data_task, '$[1]')) AS UNSIGNED),0) DESC,"
            . "COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.data_task, '$[2]')) AS UNSIGNED),0) DESC,p.id ASC LIMIT 10"
        );
    }
    return admin_all(
        "SELECT p.id AS player_id,p.account_id,p.name,a.username,p.$column AS score "
        . 'FROM player p INNER JOIN account a ON a.id=p.account_id '
        . "WHERE p.$column>0 AND a.ban=0 AND a.is_admin=0 "
        . "ORDER BY p.$column DESC,p.id ASC LIMIT 10"
    );
}

function mailbox_validate_rewards(string $rawJson): array
{
    $rewards = json_decode($rawJson, true);
    if (!is_array($rewards) || !$rewards) {
        throw new RuntimeException('Thư phải có ít nhất một phần thưởng.');
    }
    if (count($rewards) > 50) {
        throw new RuntimeException('Mỗi thư được chọn tối đa 50 phần thưởng.');
    }

    $itemIds = [];
    $optionIds = [];
    $normalized = [];
    $seenItems = [];
    foreach ($rewards as $reward) {
        if (!is_array($reward)) {
            throw new RuntimeException('Dữ liệu phần thưởng không hợp lệ.');
        }
        $id = filter_var($reward['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($reward['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < -3) {
            throw new RuntimeException('ID vật phẩm không hợp lệ.');
        }
        if ($quantity === false || $quantity < 1 || $quantity > 2000000000) {
            throw new RuntimeException('Số lượng phải từ 1 đến 2.000.000.000.');
        }
        if (isset($seenItems[$id])) {
            throw new RuntimeException('Một vật phẩm chỉ được xuất hiện một lần trong thư.');
        }
        $seenItems[$id] = true;
        if ($id >= 0) {
            $itemIds[] = $id;
        }

        $options = $reward['options'] ?? [];
        if (!is_array($options) || count($options) > 30) {
            throw new RuntimeException('Danh sách option không hợp lệ.');
        }
        if ($id < 0 && $options) {
            throw new RuntimeException('Vàng/ngọc không sử dụng option.');
        }

        $normalizedOptions = [];
        $seenOptions = [];
        foreach ($options as $option) {
            if (!is_array($option)) {
                throw new RuntimeException('Dữ liệu option không hợp lệ.');
            }
            $optionId = filter_var($option['id'] ?? null, FILTER_VALIDATE_INT);
            $param = filter_var($option['param'] ?? null, FILTER_VALIDATE_INT);
            if ($optionId === false || $optionId < 0 || $param === false
                || $param < -2147483648 || $param > 2147483647) {
                throw new RuntimeException('ID hoặc param option không hợp lệ.');
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
            throw new RuntimeException('Có vật phẩm không tồn tại trong database.');
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
            throw new RuntimeException('Có option không tồn tại trong database.');
        }
    }
    return $normalized;
}

function mailbox_reward_summary(string $json, array $itemNames): string
{
    $rewards = json_decode($json, true);
    if (!is_array($rewards)) {
        return 'Dữ liệu phần thưởng bị lỗi';
    }
    $parts = [];
    foreach ($rewards as $reward) {
        $id = (int) ($reward['id'] ?? 0);
        $quantity = (int) ($reward['quantity'] ?? 0);
        $parts[] = admin_number($quantity) . ' × ' . ($itemNames[$id] ?? ('Item #' . $id));
    }
    return implode(' · ', $parts);
}

function mailbox_activation_planets(): array
{
    return [
        0 => ['name' => 'Trái Đất', 'allowed' => [127, 128, 129, 233, 245],
            'weights' => [127 => 25, 128 => 25, 129 => 25, 233 => 900, 245 => 25]],
        1 => ['name' => 'Namek', 'allowed' => [130, 131, 132, 233, 237],
            'weights' => [130 => 25, 131 => 25, 132 => 25, 233 => 900, 237 => 25]],
        2 => ['name' => 'Xayda', 'allowed' => [133, 135, 134, 233, 241],
            'weights' => [133 => 25, 135 => 25, 134 => 25, 233 => 900, 241 => 25]],
    ];
}

function mailbox_validate_activation_config(int $planet): array
{
    $planets = mailbox_activation_planets();
    if (!isset($planets[$planet])) {
        throw new RuntimeException('Hành tinh cấu hình không hợp lệ.');
    }
    $rawActivationIds = $_POST['activation_option_ids'] ?? [];
    if (!is_array($rawActivationIds)) {
        throw new RuntimeException('Danh sách set kích hoạt không hợp lệ.');
    }
    $activationIds = [];
    foreach ($rawActivationIds as $rawId) {
        $optionId = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($optionId === false || !in_array($optionId, $planets[$planet]['allowed'], true)) {
            throw new RuntimeException('Có set kích hoạt không thuộc hành tinh đã chọn.');
        }
        $activationIds[$optionId] = $optionId;
    }
    $activationIds = array_values($activationIds);
    if (!$activationIds) {
        throw new RuntimeException('Phải chọn ít nhất một set kích hoạt để random.');
    }

    $rawWeights = $_POST['activation_weights'] ?? [];
    if (!is_array($rawWeights)) {
        throw new RuntimeException('Trọng số Set kích hoạt không hợp lệ.');
    }
    $activationWeights = [];
    foreach ($activationIds as $optionId) {
        $weight = filter_var($rawWeights[$optionId] ?? null, FILTER_VALIDATE_INT);
        if ($weight === false || $weight < 1 || $weight > 1000000) {
            throw new RuntimeException('Trọng số mỗi Set phải từ 1 đến 1.000.000.');
        }
        $activationWeights[(string) $optionId] = $weight;
    }

    $bonusRaw = json_decode((string) ($_POST['bonus_options_json'] ?? '[]'), true);
    if (!is_array($bonusRaw) || count($bonusRaw) > 20) {
        throw new RuntimeException('Danh sách option tự động không hợp lệ.');
    }
    $allSetIds = array_merge(...array_column($planets, 'allowed'));
    $bonusOptions = [];
    $bonusIds = [];
    foreach ($bonusRaw as $bonus) {
        if (!is_array($bonus)) {
            throw new RuntimeException('Dữ liệu option tự động không hợp lệ.');
        }
        $optionId = filter_var($bonus['id'] ?? null, FILTER_VALIDATE_INT);
        $param = filter_var($bonus['param'] ?? null, FILTER_VALIDATE_INT);
        if ($optionId === false || $optionId < 0 || $param === false
            || $param < -2147483648 || $param > 2147483647) {
            throw new RuntimeException('ID hoặc chỉ số option tự động không hợp lệ.');
        }
        if (in_array($optionId, [102, 107], true)) {
            throw new RuntimeException('Hộp Set kích hoạt thường không được cộng option sao pha lê 102/107.');
        }
        if (in_array($optionId, $allSetIds, true)) {
            throw new RuntimeException('Option Set phải chọn ở pool random, không thêm vào option tự động.');
        }
        if (isset($bonusIds[$optionId])) {
            throw new RuntimeException('Mỗi option tự động chỉ được thêm một lần.');
        }
        $bonusIds[$optionId] = true;
        $bonusOptions[] = ['id' => $optionId, 'param' => $param];
    }
    if ($bonusIds) {
        $ids = array_keys($bonusIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $existing = admin_all(
            "SELECT id FROM item_option_template WHERE id IN ($placeholders)",
            str_repeat('i', count($ids)),
            $ids
        );
        if (count($existing) !== count($ids)) {
            throw new RuntimeException('Có option tự động không tồn tại trong database.');
        }
    }
    return [$activationIds, $activationWeights, $bonusOptions];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'save_top_config') {
            $rankingKey = mailbox_ranking_key('post');
            $rankGroup = trim((string) ($_POST['rank_position'] ?? ''));
            if (in_array($rankGroup, ['1', '2', '3'], true)) {
                $targetRanks = [(int) $rankGroup];
                $rankLabel = $rankGroup;
            } elseif ($rankGroup === '4-10') {
                $targetRanks = range(4, 10);
                $rankLabel = '4–10';
            } else {
                throw new RuntimeException('Chỉ được cấu hình riêng Top 1, Top 2, Top 3 hoặc nhóm Top 4–10.');
            }
            $title = trim((string) ($_POST['title'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $senderName = trim((string) ($_POST['sender_name'] ?? 'Admin'));
            if (mb_strlen($title) < 3 || mb_strlen($title) > 120
                || mb_strlen($message) > 500
                || mb_strlen($senderName) < 2 || mb_strlen($senderName) > 50) {
                throw new RuntimeException('Tiêu đề, lời nhắn hoặc người gửi không hợp lệ.');
            }
            $rewards = mailbox_validate_rewards((string) ($_POST['rewards_json'] ?? ''));
            $rewards = mailbox_apply_naruto_top_reward_rules($rewards, $targetRanks);
            $rewardsJson = json_encode($rewards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($rewardsJson === false) {
                throw new RuntimeException('Không thể mã hóa bộ quà Top.');
            }
            global $conn;
            $conn->begin_transaction();
            try {
                foreach ($targetRanks as $rank) {
                    admin_execute(
                        'INSERT INTO top_reward_config '
                        . '(ranking_key,rank_position,title,message,sender_name,rewards_json,updated_by) '
                        . 'VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE '
                        . 'title=VALUES(title),message=VALUES(message),sender_name=VALUES(sender_name),'
                        . 'rewards_json=VALUES(rewards_json),updated_by=VALUES(updated_by)',
                        'sissssi',
                        [$rankingKey, $rank, $title, $message, $senderName, $rewardsJson, (int) $admin_user['id']]
                    );
                }
                $conn->commit();
            } catch (Throwable $error) {
                $conn->rollback();
                throw $error;
            }
            admin_audit('Cấu hình quà Top ' . $rankLabel, 'top_reward_config', null, [
                'ranking' => $rankingKey, 'ranks' => $targetRanks,
                'reward_count' => count($rewards),
            ]);
            admin_flash('success', 'Đã lưu bộ quà mặc định Top ' . $rankLabel . '.');
        } elseif ($action === 'save_activation_config') {
            $planet = filter_input(INPUT_POST, 'planet', FILTER_VALIDATE_INT);
            if ($planet === false || $planet === null) {
                throw new RuntimeException('Hành tinh cấu hình không hợp lệ.');
            }
            [$activationIds, $activationWeights, $bonusOptions] = mailbox_validate_activation_config($planet);
            $planetDefinition = mailbox_activation_planets()[$planet];
            $activationJson = json_encode($activationIds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $weightsJson = json_encode($activationWeights, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $bonusJson = json_encode($bonusOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            admin_execute(
                'INSERT INTO activation_reward_config '
                . '(planet,planet_name,activation_options_json,activation_weights_json,bonus_options_json,enabled,updated_by) '
                . 'VALUES (?,?,?,?,?,1,?) ON DUPLICATE KEY UPDATE '
                . 'planet_name=VALUES(planet_name),activation_options_json=VALUES(activation_options_json),'
                . 'activation_weights_json=VALUES(activation_weights_json),'
                . 'bonus_options_json=VALUES(bonus_options_json),enabled=1,updated_by=VALUES(updated_by)',
                'issssi',
                [$planet, $planetDefinition['name'], $activationJson, $weightsJson, $bonusJson, (int) $admin_user['id']]
            );
            admin_audit('Cấu hình Hộp Set ' . $planetDefinition['name'],
                'activation_reward_config', $planet, [
                    'activation_options' => $activationIds,
                    'activation_weights' => $activationWeights,
                    'bonus_options' => $bonusOptions,
                ]);
            admin_flash('success', 'Đã cập nhật Hộp Set/Capsule hành tinh '
                . $planetDefinition['name'] . '. Game áp dụng ngay từ lần mở tiếp theo.');
        } elseif ($action === 'finalize_top') {
            $rankingKey = mailbox_ranking_key('post');
            $period = mailbox_ranking_period(
                $rankingKey,
                (string) ($_POST['batch_key'] ?? ''),
                isset($_POST['ranking_date']) ? (string) $_POST['ranking_date'] : null
            );
            $batchKey = $period['key'];
            $batchTitle = trim((string) ($_POST['batch_title'] ?? ''));
            if (mb_strlen($batchTitle) < 3 || mb_strlen($batchTitle) > 120) {
                throw new RuntimeException('Tên đợt chốt phải dài 3–120 ký tự.');
            }
            $configRows = admin_all(
                'SELECT rank_position,title,message,sender_name,rewards_json '
                . 'FROM top_reward_config WHERE ranking_key=? AND rank_position BETWEEN 1 AND 10 '
                . 'ORDER BY rank_position',
                's',
                [$rankingKey]
            );
            if (count($configRows) !== 10
                || array_map('intval', array_column($configRows, 'rank_position')) !== range(1, 10)) {
                throw new RuntimeException('Phải cấu hình đủ bộ quà mặc định từ Top 1 đến Top 10 trước khi chốt.');
            }
            foreach ($configRows as &$configRow) {
                $rank = (int) $configRow['rank_position'];
                $rowRewards = mailbox_validate_rewards((string) $configRow['rewards_json']);
                $rowRewards = mailbox_apply_naruto_top_reward_rules($rowRewards, [$rank]);
                $normalizedJson = json_encode(
                    $rowRewards,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($normalizedJson === false) {
                    throw new RuntimeException('Không thể chuẩn hóa bộ quà Top ' . $rank . '.');
                }
                $configRow['rewards_json'] = $normalizedJson;
            }
            unset($configRow);
            $configSnapshot = json_encode($configRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($configSnapshot === false) {
                throw new RuntimeException('Không thể tạo snapshot bộ quà Top.');
            }
            $periodCommand = mailbox_existing_period_command($rankingKey, $period);
            if ($periodCommand) {
                throw new RuntimeException('Kỳ này đã được chốt hoặc đang chờ xử lý ở lệnh #' . $periodCommand['id'] . '.');
            }
            $sameBatch = admin_one(
                'SELECT id,status FROM top_reward_command WHERE ranking_key=? AND batch_key=? LIMIT 1',
                'ss',
                [$rankingKey, $batchKey]
            );
            if ($sameBatch && $sameBatch['status'] !== 'FAILED') {
                throw new RuntimeException('Đợt này đã được chốt hoặc đang chờ xử lý ở lệnh #' . $sameBatch['id'] . '.');
            }
            $pending = (int) admin_scalar(
                "SELECT COUNT(*) FROM top_reward_command WHERE ranking_key=? AND status IN ('PENDING','PROCESSING')",
                's',
                [$rankingKey]
            );
            if ($pending > 0) {
                throw new RuntimeException('Bảng xếp hạng này đang có một lệnh chốt chờ game server xử lý.');
            }
            if ($sameBatch) {
                $commandId = (int) $sameBatch['id'];
                admin_execute(
                    "UPDATE top_reward_command SET period_type=?,batch_title=?,ranking_date=?,"
                    . "config_snapshot_json=?,requested_by=?,requested_by_name=?,status='PENDING',"
                    . "started_at=NULL,finished_at=NULL,result_message=NULL WHERE id=? AND status='FAILED'",
                    'ssssisi',
                    [
                        $period['type'], $batchTitle, $period['ranking_date'], $configSnapshot,
                        (int) $admin_user['id'], (string) $admin_user['username'], $commandId,
                    ]
                );
            } else {
                admin_execute(
                    'INSERT INTO top_reward_command '
                    . '(ranking_key,period_type,batch_key,batch_title,ranking_date,config_snapshot_json,'
                    . 'requested_by,requested_by_name) VALUES (?,?,?,?,?,?,?,?)',
                    'ssssssis',
                    [
                        $rankingKey, $period['type'], $batchKey, $batchTitle, $period['ranking_date'],
                        $configSnapshot, (int) $admin_user['id'], (string) $admin_user['username'],
                    ]
                );
                global $conn;
                $commandId = (int) $conn->insert_id;
            }
            admin_audit('Yêu cầu chốt Top 1–10', 'top_reward_command', $commandId, [
                'ranking' => $rankingKey, 'period_type' => $period['type'],
                'batch_key' => $batchKey, 'ranking_date' => $period['ranking_date'],
            ]);
            admin_flash('success', 'Đã gửi lệnh chốt Top 1–10 #' . $commandId
                . '. Game server sẽ lấy đúng kỳ và chuyển quà vào hòm thư.');
        } elseif ($action === 'send') {
            $playerName = trim((string) ($_POST['player_name'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $senderName = trim((string) ($_POST['sender_name'] ?? 'Admin'));
            $rankRaw = trim((string) ($_POST['rank_position'] ?? ''));
            $rank = $rankRaw === '' ? null : filter_var($rankRaw, FILTER_VALIDATE_INT);

            if ($playerName === '') {
                throw new RuntimeException('Vui lòng nhập tên nhân vật nhận quà.');
            }
            if (mb_strlen($title) < 3 || mb_strlen($title) > 120) {
                throw new RuntimeException('Tiêu đề thư phải dài 3–120 ký tự.');
            }
            if (mb_strlen($message) > 500 || mb_strlen($senderName) < 2 || mb_strlen($senderName) > 50) {
                throw new RuntimeException('Nội dung hoặc tên người gửi quá dài.');
            }
            if ($rank !== null && ($rank === false || $rank < 1 || $rank > 10)) {
                throw new RuntimeException('Hạng xếp hạng chỉ nhận giá trị từ 1 đến 10.');
            }

            $recipient = admin_one(
                'SELECT p.id AS player_id, p.account_id, p.name, a.username '
                . 'FROM player p INNER JOIN account a ON a.id = p.account_id '
                . 'WHERE LOWER(p.name) = LOWER(?) LIMIT 1',
                's',
                [$playerName]
            );
            if (!$recipient) {
                throw new RuntimeException('Không tìm thấy nhân vật "' . $playerName . '".');
            }

            $rewards = mailbox_validate_rewards((string) ($_POST['rewards_json'] ?? ''));
            $rewardsJson = json_encode($rewards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            admin_execute(
                'INSERT INTO player_mailbox '
                . '(account_id, player_id, title, message, sender_name, rank_position, rewards_json, created_by) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                'iisssisi',
                [
                    (int) $recipient['account_id'], (int) $recipient['player_id'], $title, $message,
                    $senderName, $rank, $rewardsJson, (int) $admin_user['id'],
                ]
            );
            global $conn;
            $mailId = (int) $conn->insert_id;
            admin_audit('Gửi quà hòm thư', 'player_mailbox', $mailId, [
                'player' => $recipient['name'], 'rank' => $rank, 'reward_count' => count($rewards),
            ]);
            admin_flash('success', 'Đã gửi thư #' . $mailId . ' cho nhân vật ' . $recipient['name'] . '.');
        } elseif ($action === 'cancel') {
            $mailId = filter_input(INPUT_POST, 'mail_id', FILTER_VALIDATE_INT);
            if (!$mailId) {
                throw new RuntimeException('Mã thư không hợp lệ.');
            }
            $affected = admin_execute(
                "UPDATE player_mailbox SET status = 'CANCELLED', cancelled_at = NOW() "
                . "WHERE id = ? AND status = 'PENDING'",
                'i',
                [$mailId]
            );
            if ($affected !== 1) {
                throw new RuntimeException('Chỉ có thể thu hồi thư chưa nhận.');
            }
            admin_audit('Thu hồi thư vật phẩm', 'player_mailbox', $mailId);
            admin_flash('success', 'Đã thu hồi thư #' . $mailId . '.');
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
        $rankingKey = trim((string) ($_POST['ranking'] ?? 'top_boss'));
        if ($action === 'save_top_config') {
            $rankGroup = trim((string) ($_POST['rank_position'] ?? '1'));
            if (!in_array($rankGroup, ['1', '2', '3', '4-10'], true)) {
                $rankGroup = '1';
            }
            $redirect = 'mailboxes.php?ranking=' . rawurlencode($rankingKey)
                . '&configure_rank=' . rawurlencode($rankGroup);
            if (!empty($_POST['ranking_date'])) {
                $redirect .= '&ranking_date=' . rawurlencode((string) $_POST['ranking_date']);
            }
            admin_redirect($redirect);
        }
        if ($action === 'finalize_top') {
            $redirect = 'mailboxes.php?ranking=' . rawurlencode($rankingKey);
            if (!empty($_POST['ranking_date'])) {
                $redirect .= '&ranking_date=' . rawurlencode((string) $_POST['ranking_date']);
            }
            admin_redirect($redirect);
        }
        admin_redirect($action === 'send' ? 'mailboxes.php?create=1' : 'mailboxes.php');
    }
    $rankingKey = trim((string) ($_POST['ranking'] ?? 'top_boss'));
    $redirect = in_array($action, ['save_top_config', 'finalize_top'], true)
        ? 'mailboxes.php?ranking=' . rawurlencode($rankingKey)
        : 'mailboxes.php';
    if (in_array($action, ['save_top_config', 'finalize_top'], true)
        && !empty($_POST['ranking_date'])) {
        $redirect .= '&ranking_date=' . rawurlencode((string) $_POST['ranking_date']);
    }
    admin_redirect($redirect);
}

$allowedStatuses = ['PENDING', 'PROCESSING', 'CLAIMED', 'CANCELLED'];
$status = strtoupper(trim((string) ($_GET['status'] ?? '')));
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}
$where = $status === '' ? '' : 'WHERE m.status = ?';
$types = $status === '' ? '' : 's';
$params = $status === '' ? [] : [$status];
$mails = admin_all(
    'SELECT m.*, p.name AS player_name, a.username FROM player_mailbox m '
    . 'INNER JOIN player p ON p.id = m.player_id '
    . 'INNER JOIN account a ON a.id = m.account_id '
    . $where . ' ORDER BY m.id DESC LIMIT 100',
    $types,
    $params
);

$counts = ['PENDING' => 0, 'PROCESSING' => 0, 'CLAIMED' => 0, 'CANCELLED' => 0];
foreach (admin_all('SELECT status, COUNT(*) AS total FROM player_mailbox GROUP BY status') as $row) {
    $counts[$row['status']] = (int) $row['total'];
}

$options = admin_all('SELECT id, NAME AS name FROM item_option_template ORDER BY id');
foreach ($options as &$option) {
    $option['id'] = (int) $option['id'];
}
unset($option);
$optionNames = [];
foreach ($options as $option) {
    $optionNames[(int) $option['id']] = $option['name'];
}

$activationPlanets = mailbox_activation_planets();
$activationConfigs = [];
foreach (admin_all('SELECT * FROM activation_reward_config ORDER BY planet') as $row) {
    $activationOptions = json_decode((string) $row['activation_options_json'], true);
    $activationWeights = json_decode((string) ($row['activation_weights_json'] ?? '{}'), true);
    $bonusOptions = json_decode((string) $row['bonus_options_json'], true);
    $activationConfigs[(int) $row['planet']] = [
        'activation_options' => is_array($activationOptions) ? array_map('intval', $activationOptions) : [],
        'activation_weights' => is_array($activationWeights) ? array_map('intval', $activationWeights) : [],
        'bonus_options' => is_array($bonusOptions) ? $bonusOptions : [],
        'updated_at' => $row['updated_at'],
    ];
}

$itemNames = [-1 => 'Vàng', -2 => 'Ngọc', -3 => 'Ngọc khóa'];
foreach (admin_all("SELECT id, NAME AS name FROM item_template WHERE NAME <> '' ORDER BY id") as $item) {
    $itemNames[(int) $item['id']] = $item['name'];
}

$rankings = mailbox_rankings();
$rankingKey = mailbox_ranking_key('get');
$ranking = $rankings[$rankingKey];
$requestedRankingDate = isset($_GET['ranking_date']) ? (string) $_GET['ranking_date'] : null;
$rankingPeriod = mailbox_ranking_period($rankingKey, null, $requestedRankingDate);
$rankingDateQuery = $rankingPeriod['ranking_date'] !== null
    ? '&ranking_date=' . rawurlencode($rankingPeriod['ranking_date'])
    : '';
$rankingReturnPath = 'mailboxes.php?ranking=' . rawurlencode($rankingKey) . $rankingDateQuery;
$weeklyPeriods = $rankingPeriod['type'] === 'WEEKLY'
    ? mailbox_weekly_period_options($rankingKey)
    : [];
$existingPeriodCommand = mailbox_existing_period_command($rankingKey, $rankingPeriod);
$rankingPreview = mailbox_ranking_preview($rankingKey, $rankingPeriod['ranking_date']);
$topConfigs = [];
foreach (admin_all(
    'SELECT * FROM top_reward_config WHERE ranking_key=? ORDER BY rank_position',
    's',
    [$rankingKey]
) as $row) {
    $topConfigs[(int) $row['rank_position']] = $row;
}

$configureRank = trim((string) ($_GET['configure_rank'] ?? ''));
if (!in_array($configureRank, ['1', '2', '3', '4-10'], true)) {
    $configureRank = null;
}
$configureRankLabel = $configureRank === '4-10' ? '4–10' : $configureRank;
$topRewardHelp = match ($configureRank) {
    '1' => 'Tự chọn quà; 5 món trong Rương hợp tác Naruto sẽ tự nhận full chỉ số và vĩnh viễn.',
    '4-10' => 'Một lần lưu sẽ áp dụng cùng bộ quà cho Top 4, 5, 6, 7, 8, 9 và 10. Vật phẩm Naruto chỉ dành cho Top 1.',
    default => 'Bộ quà này sẽ tự động chuyển cho đúng người ở hạng này khi chốt. Vật phẩm Naruto chỉ dành cho Top 1.',
};
$editingConfigRank = $configureRank === '4-10' ? 4 : (int) $configureRank;
$editingTopConfig = $configureRank ? ($topConfigs[$editingConfigRank] ?? null) : null;
$narutoTopRewardPresets = mailbox_naruto_top_reward_presets();
$topInitialRewards = [];
if ($editingTopConfig) {
    $decoded = json_decode((string) $editingTopConfig['rewards_json'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $reward) {
            $rewardId = (int) ($reward['id'] ?? 0);
            $topInitialRewards[] = [
                'id' => $rewardId,
                'name' => $itemNames[$rewardId] ?? ('Item #' . $rewardId),
                'quantity' => max(1, (int) ($reward['quantity'] ?? 1)),
                'options' => is_array($reward['options'] ?? null) ? $reward['options'] : [],
            ];
        }
    }
}

$topCommands = admin_all(
    'SELECT c.*,(SELECT COUNT(*) FROM top_reward_winner w WHERE w.command_id=c.id) AS winner_count '
    . 'FROM top_reward_command c WHERE c.ranking_key=? ORDER BY c.id DESC LIMIT 12',
    's',
    [$rankingKey]
);
$latestDoneCommand = admin_one(
    "SELECT id FROM top_reward_command WHERE ranking_key=? AND status='DONE' ORDER BY id DESC LIMIT 1",
    's',
    [$rankingKey]
);
$latestWinners = $latestDoneCommand ? admin_all(
    'SELECT w.*,m.status AS mail_status,m.claimed_at '
    . 'FROM top_reward_winner w INNER JOIN player_mailbox m ON m.id=w.mailbox_id '
    . 'WHERE w.command_id=? ORDER BY w.rank_position',
    'i',
    [(int) $latestDoneCommand['id']]
) : [];

$showForm = isset($_GET['create']) && !$configureRank;
admin_render_header('Xếp hạng & Trao quà', 'mailboxes', 'Xem điểm, chốt Top hoặc gửi quà Admin bất kỳ lúc nào qua Hòm thư tại NPC nhà');
?>
<div class="toolbar" style="margin-bottom:20px">
    <a class="btn btn-primary" href="<?= admin_escape(admin_url('mailboxes.php?create=1')) ?>">+ Trao quà ngay</a>
    <span class="text-muted">Gửi trực tiếp cho nhân vật đang online hoặc offline, không phụ thuộc kỳ chốt Top.</span>
</div>
<section class="metric-grid">
    <article class="metric-card"><span class="metric-icon orange">✉</span><div><small>CHƯA NHẬN</small><strong><?= admin_number($counts['PENDING']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon blue">…</span><div><small>ĐANG XỬ LÝ</small><strong><?= admin_number($counts['PROCESSING']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon green">✓</span><div><small>ĐÃ NHẬN</small><strong><?= admin_number($counts['CLAIMED']) ?></strong></div></article>
    <article class="metric-card"><span class="metric-icon red">×</span><div><small>ĐÃ THU HỒI</small><strong><?= admin_number($counts['CANCELLED']) ?></strong></div></article>
</section>

<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Chốt Top 1–10 tự động</h2><p>Quà được snapshot khi bấm chốt; game server khóa đúng player_id, account_id và đúng kỳ trao thưởng</p></div></div>
    <div class="panel-body">
        <form method="get" class="event-picker-form" style="margin-bottom:18px">
            <div class="form-group full">
                <label for="ranking-picker">CHỌN BẢNG XẾP HẠNG</label>
                <select class="form-control" id="ranking-picker" name="ranking" onchange="this.form.submit()">
                    <?php foreach ($rankings as $key => $definition): ?>
                        <option value="<?= admin_escape($key) ?>" <?= $rankingKey === $key ? 'selected' : '' ?>><?= admin_escape($definition['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($weeklyPeriods): ?>
                <div class="form-group full">
                    <label for="ranking-period-picker">CHỌN KỲ TUẦN</label>
                    <select class="form-control" id="ranking-period-picker" name="ranking_date" onchange="this.form.submit()">
                        <?php foreach ($weeklyPeriods as $weeklyPeriod): ?>
                            <option value="<?= admin_escape($weeklyPeriod['ranking_date']) ?>" <?= $rankingPeriod['ranking_date'] === $weeklyPeriod['ranking_date'] ? 'selected' : '' ?>>
                                <?= admin_escape($weeklyPeriod['period']['label']) ?> · <?= admin_number($weeklyPeriod['player_count']) ?> người có điểm
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <noscript><button class="btn btn-primary" type="submit">Xem bảng Top</button></noscript>
        </form>

        <div class="grid-2">
            <section class="panel">
                <div class="panel-head"><div><h2>Top 1–10 hiện tại · <?= admin_escape($ranking['name']) ?></h2><p><?= admin_escape($rankingPeriod['label']) ?>; chỉ người chơi hợp lệ, không tính tài khoản Admin hoặc bị khóa</p></div></div>
                <div class="table-wrap"><table class="data-table">
                    <thead><tr><th>Hạng</th><th>Nhân vật</th><th>Tài khoản đích</th><th><?= $rankingKey === 'top_task' ? 'Nhiệm vụ' : 'Điểm' ?></th><th class="text-right">Quà</th></tr></thead>
                    <tbody>
                    <?php foreach ($rankingPreview as $index => $top):
                        $rowRank = $index + 1;
                        $rowConfigKey = mailbox_reward_group_for_rank($rowRank);
                        $rowConfigLabel = $rowRank <= 3 ? ('Top ' . $rowRank) : 'Top 4–10';
                        $rowConfigRanks = $rowRank <= 3 ? [$rowRank] : range(4, 10);
                        $rowConfigReady = count(array_filter(
                            $rowConfigRanks,
                            static fn (int $rankPosition): bool => isset($topConfigs[$rankPosition])
                        )) === count($rowConfigRanks);
                        $rowConfig = $rowConfigReady ? $topConfigs[$rowConfigRanks[0]] : null;
                    ?>
                        <tr>
                            <td><span class="badge badge-blue">Top <?= $rowRank ?></span></td>
                            <td><strong><?= admin_escape($top['name']) ?></strong><br><small class="mono">player_id: <?= (int) $top['player_id'] ?></small></td>
                            <td><?= admin_escape($top['username']) ?><br><small class="mono">account_id: <?= (int) $top['account_id'] ?></small></td>
                            <td><strong><?= $rankingKey === 'top_task' ? 'Nhiệm vụ ' . admin_number($top['score']) : admin_number($top['score']) ?></strong></td>
                            <td class="text-right">
                                <a class="btn <?= $rowConfigReady ? 'btn-blue' : 'btn-secondary' ?> btn-sm" href="<?= admin_escape(admin_url('mailboxes.php?ranking=' . rawurlencode($rankingKey) . '&configure_rank=' . rawurlencode($rowConfigKey) . $rankingDateQuery)) ?>">
                                    <?= $rowConfigReady ? 'Sửa ' : 'Cài ' ?><?= $rowConfigLabel ?>
                                </a>
                                <?php if ($rowConfig): ?><br><small class="text-muted"><?= admin_escape(mb_strimwidth(mailbox_reward_summary($rowConfig['rewards_json'], $itemNames), 0, 42, '…')) ?></small><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rankingPreview): ?><tr><td colspan="5"><div class="empty"><strong>Chưa có xếp hạng</strong>Chưa có người chơi hợp lệ đạt điểm ở bảng này. Vẫn có thể cấu hình trước trong bảng Bộ quà mặc định bên cạnh.</div></td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </section>

            <section class="panel">
                <div class="panel-head"><div><h2>Bộ quà mặc định</h2><p>Quà được chụp lại theo từng hạng khi tạo thư</p></div></div>
                <div class="table-wrap"><table class="data-table">
                    <thead><tr><th>Hạng</th><th>Phần thưởng</th><th class="text-right">Cấu hình</th></tr></thead>
                    <tbody>
                    <?php foreach ([
                        ['key' => '1', 'label' => '1', 'ranks' => [1]],
                        ['key' => '2', 'label' => '2', 'ranks' => [2]],
                        ['key' => '3', 'label' => '3', 'ranks' => [3]],
                        ['key' => '4-10', 'label' => '4–10', 'ranks' => range(4, 10)],
                    ] as $configGroup):
                        $configuredRanks = array_filter(
                            $configGroup['ranks'],
                            static fn (int $rankPosition): bool => isset($topConfigs[$rankPosition])
                        );
                        $config = count($configuredRanks) === count($configGroup['ranks'])
                            ? $topConfigs[$configGroup['ranks'][0]]
                            : null;
                    ?>
                        <tr>
                            <td><span class="badge <?= $config ? 'badge-green' : 'badge-red' ?>">Top <?= $configGroup['label'] ?></span></td>
                            <td class="gift-summary"><?= $config ? admin_escape(mb_strimwidth(mailbox_reward_summary($config['rewards_json'], $itemNames), 0, 105, '…')) : '<span class="text-muted">Chưa cấu hình</span>' ?></td>
                            <td class="text-right"><a class="btn btn-blue btn-sm" href="<?= admin_escape(admin_url('mailboxes.php?ranking=' . rawurlencode($rankingKey) . '&configure_rank=' . rawurlencode($configGroup['key']) . $rankingDateQuery)) ?>"><?= $config ? 'Sửa quà' : 'Thiết lập' ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </section>
        </div>

        <form method="post" class="form-grid" style="margin-top:18px" data-confirm="Chốt bảng <?= admin_escape($ranking['name']) ?>? Game server sẽ lấy Top 1–10 của đúng kỳ <?= admin_escape($rankingPeriod['label']) ?> và gửi vào hòm thư.">
            <?= admin_csrf_field() ?>
            <input type="hidden" name="action" value="finalize_top">
            <input type="hidden" name="ranking" value="<?= admin_escape($rankingKey) ?>">
            <?php if ($rankingPeriod['ranking_date'] !== null): ?><input type="hidden" name="ranking_date" value="<?= admin_escape($rankingPeriod['ranking_date']) ?>"><?php endif; ?>
            <div class="form-group"><label><?= $rankingPeriod['locked'] ? 'MÃ KỲ TỰ ĐỘNG' : 'MÃ ĐỢT CHỐT (KHÔNG ĐƯỢC TRÙNG)' ?></label><input class="form-control mono" name="batch_key" maxlength="80" value="<?= admin_escape($rankingPeriod['key']) ?>" <?= $rankingPeriod['locked'] ? 'readonly' : '' ?> required></div>
            <div class="form-group"><label>TÊN ĐỢT TRAO GIẢI</label><input class="form-control" name="batch_title" maxlength="120" value="<?= admin_escape('Chốt top ' . $ranking['name'] . ' · ' . $rankingPeriod['label']) ?>" required></div>
            <div class="form-actions">
                <span class="text-muted" style="margin-right:auto;font-size:11px"><?= $existingPeriodCommand ? 'Kỳ này đã có lệnh #' . (int) $existingPeriodCommand['id'] . ' (' . admin_escape($existingPeriodCommand['status']) . ').' : admin_escape($rankingPeriod['label']) . ' chỉ được chốt một lần.' ?></span>
                <button class="btn btn-primary" type="submit" <?= count($topConfigs) !== 10 || !$rankingPreview || $existingPeriodCommand ? 'disabled' : '' ?>><?= $existingPeriodCommand ? 'Kỳ này đã chốt' : 'Chốt Top 1–10 và chuyển quà' ?></button>
            </div>
        </form>
    </div>
</section>

<section class="panel" id="activation-set-config" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Cấu hình Hộp Set và Capsule kích hoạt</h2><p>Thay đổi trực tiếp trong database; trang bị mở sau khi lưu sẽ tự nhận pool Set và option cộng thêm mới</p></div></div>
    <div class="panel-body">
        <div class="note" style="margin-bottom:16px"><strong>Phạm vi:</strong> Hộp quà Set kích hoạt (ID 1538) sinh đủ 5 món SKH thường cùng một Set; Capsule 1 món (ID 1559) dùng cùng cấu hình theo hành tinh nhân vật. Hai option sao pha lê 102/107 luôn bị loại bỏ.</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px">
        <?php foreach ($activationPlanets as $planetId => $planetDefinition):
            $activationConfig = $activationConfigs[$planetId] ?? [
                'activation_options' => $planetDefinition['allowed'],
                'activation_weights' => $planetDefinition['weights'],
                'bonus_options' => [],
                'updated_at' => null,
            ];
        ?>
            <form method="post" class="panel" style="margin:0" data-activation-config data-bonus-options="<?= admin_escape(json_encode($activationConfig['bonus_options'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
                <?= admin_csrf_field() ?>
                <input type="hidden" name="action" value="save_activation_config">
                <input type="hidden" name="planet" value="<?= $planetId ?>">
                <input type="hidden" name="bonus_options_json" value="[]" data-bonus-json>
                <div class="panel-head"><div><h2><?= admin_escape($planetDefinition['name']) ?></h2><p><?= $activationConfig['updated_at'] ? 'Cập nhật ' . admin_escape(admin_datetime($activationConfig['updated_at'])) : 'Cấu hình mặc định' ?></p></div></div>
                <div class="panel-body">
                    <div class="form-group full">
                        <label>POOL SET KÍCH HOẠT RANDOM</label>
                        <p class="help">Dùng chung cho Hộp ID 1538 và Capsule ID 1559: Set Gohan 90%; bốn Set còn lại mỗi Set 2,5%.</p>
                        <div style="display:grid;gap:8px;margin-top:8px">
                        <?php foreach ($planetDefinition['allowed'] as $activationOptionId): ?>
                            <label data-set-weight-row style="display:grid;grid-template-columns:auto minmax(0,1fr) 92px 58px;align-items:center;gap:8px;font-weight:600">
                                <input type="checkbox" name="activation_option_ids[]" value="<?= $activationOptionId ?>" <?= in_array($activationOptionId, $activationConfig['activation_options'], true) ? 'checked' : '' ?>>
                                <span><?= admin_escape($optionNames[$activationOptionId] ?? ('Option #' . $activationOptionId)) ?> <small class="mono">#<?= $activationOptionId ?></small></span>
                                <input class="form-control mono" type="number" min="1" max="1000000" name="activation_weights[<?= $activationOptionId ?>]" value="<?= (int) ($activationConfig['activation_weights'][$activationOptionId] ?? $planetDefinition['weights'][$activationOptionId]) ?>" title="Trọng số random" required>
                                <small class="mono text-muted" data-weight-percent></small>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group full" style="margin-top:14px">
                        <div style="display:flex;align-items:center;gap:8px"><label style="margin:0">OPTION TỰ ĐỘNG CỘNG THÊM</label><span class="spacer"></span><button class="btn btn-blue btn-sm" type="button" data-add-bonus>+ Thêm option</button></div>
                        <p class="help">Nếu trùng option sẵn có trên món đồ, chỉ số mới sẽ thay thế chỉ số cũ.</p>
                        <div data-bonus-list style="display:grid;gap:8px;margin-top:10px"></div>
                    </div>
                    <div class="form-actions" style="margin-top:16px"><button class="btn btn-primary" type="submit">Lưu cấu hình <?= admin_escape($planetDefinition['name']) ?></button></div>
                </div>
            </form>
        <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
(() => {
    const catalog = <?= json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const setIds = new Set(<?= json_encode(array_values(array_unique(array_merge(...array_column($activationPlanets, 'allowed'))))) ?>);
    const bonusCatalog = catalog.filter(option => !setIds.has(Number(option.id)) && ![102, 107].includes(Number(option.id)));
    document.querySelectorAll('[data-activation-config]').forEach(form => {
        const list = form.querySelector('[data-bonus-list]');
        const hidden = form.querySelector('[data-bonus-json]');
        const weightRows = [...form.querySelectorAll('[data-set-weight-row]')];
        const refreshWeightPercent = () => {
            const selected = weightRows.filter(row => row.querySelector('input[type="checkbox"]').checked);
            const total = selected.reduce((sum, row) => sum + Math.max(0, Number(row.querySelector('input[type="number"]').value) || 0), 0);
            weightRows.forEach(row => {
                const checked = row.querySelector('input[type="checkbox"]').checked;
                const weight = Math.max(0, Number(row.querySelector('input[type="number"]').value) || 0);
                row.querySelector('[data-weight-percent]').textContent = checked && total > 0
                    ? `${(weight * 100 / total).toFixed(2)}%` : '—';
            });
        };
        weightRows.forEach(row => {
            row.querySelector('input[type="checkbox"]').addEventListener('change', refreshWeightPercent);
            row.querySelector('input[type="number"]').addEventListener('input', refreshWeightPercent);
        });
        refreshWeightPercent();
        let rows = [];
        try { rows = JSON.parse(form.dataset.bonusOptions || '[]'); } catch (_) { rows = []; }

        const sync = () => {
            hidden.value = JSON.stringify(rows.map(row => ({id: Number(row.id), param: Number(row.param)})));
        };
        const render = () => {
            list.replaceChildren();
            if (!rows.length) {
                const empty = document.createElement('div');
                empty.className = 'text-muted';
                empty.textContent = 'Chưa cộng thêm option.';
                list.appendChild(empty);
                sync();
                return;
            }
            rows.forEach((row, index) => {
                const line = document.createElement('div');
                line.style.cssText = 'display:grid;grid-template-columns:minmax(0,1fr) 100px auto;gap:8px;align-items:center';
                const select = document.createElement('select');
                select.className = 'form-control';
                bonusCatalog.forEach(option => {
                    const item = document.createElement('option');
                    item.value = option.id;
                    item.textContent = `#${option.id} · ${option.name}`;
                    item.selected = Number(option.id) === Number(row.id);
                    select.appendChild(item);
                });
                select.addEventListener('change', () => { rows[index].id = Number(select.value); sync(); });
                const param = document.createElement('input');
                param.className = 'form-control mono';
                param.type = 'number';
                param.value = Number(row.param || 0);
                param.title = 'Chỉ số option';
                param.addEventListener('input', () => { rows[index].param = Number(param.value); sync(); });
                const remove = document.createElement('button');
                remove.className = 'btn btn-danger btn-sm';
                remove.type = 'button';
                remove.textContent = '×';
                remove.addEventListener('click', () => { rows.splice(index, 1); render(); });
                line.append(select, param, remove);
                list.appendChild(line);
            });
            sync();
        };
        form.querySelector('[data-add-bonus]').addEventListener('click', () => {
            if (!bonusCatalog.length || rows.length >= 20) return;
            rows.push({id: Number(bonusCatalog[0].id), param: 0});
            render();
        });
        form.addEventListener('submit', sync);
        render();
    });
})();
</script>

<?php if ($configureRank): ?>
<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Cấu hình quà mặc định Top <?= $configureRankLabel ?></h2><p><?= admin_escape($ranking['name']) ?><?= $configureRank === '4-10' ? ' · áp dụng chung cho cả 7 hạng' : '' ?></p></div><span class="spacer"></span><a class="btn btn-secondary btn-sm" href="<?= admin_escape(admin_url($rankingReturnPath)) ?>">Đóng</a></div>
    <div class="panel-body">
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?>
            <input type="hidden" name="action" value="save_top_config">
            <input type="hidden" name="ranking" value="<?= admin_escape($rankingKey) ?>">
            <?php if ($rankingPeriod['ranking_date'] !== null): ?><input type="hidden" name="ranking_date" value="<?= admin_escape($rankingPeriod['ranking_date']) ?>"><?php endif; ?>
            <input type="hidden" name="rank_position" value="<?= admin_escape($configureRank) ?>">
            <div class="form-group"><label>TIÊU ĐỀ THƯ</label><input class="form-control" name="title" maxlength="120" value="<?= admin_escape($editingTopConfig['title'] ?? ('Quà Top ' . $configureRankLabel)) ?>" required></div>
            <div class="form-group"><label>NGƯỜI GỬI HIỂN THỊ</label><input class="form-control" name="sender_name" maxlength="50" value="<?= admin_escape($editingTopConfig['sender_name'] ?? 'Admin') ?>" required></div>
            <div class="form-group full"><label>LỜI NHẮN</label><textarea class="form-control" name="message" maxlength="500"><?= admin_escape($editingTopConfig['message'] ?? 'Chúc mừng bạn đã đạt thứ hạng cao trong sự kiện.') ?></textarea></div>
            <div class="form-group full gift-builder" data-gift-builder>
                <input type="hidden" name="rewards_json" id="rewards_json">
                <div class="builder-head"><div><label>VẬT PHẨM MẶC ĐỊNH TOP <?= $configureRankLabel ?></label><p class="help"><?= admin_escape($topRewardHelp) ?></p></div><button class="btn btn-primary" type="button" data-open-catalog>+ Chọn vật phẩm từ database</button></div>
                <div class="reward-empty" data-reward-empty><strong>Chưa có phần thưởng</strong>Chọn vật phẩm, số lượng và option cho Top <?= $configureRankLabel ?>.</div>
                <div class="reward-list" data-reward-list></div>
            </div>
            <div class="form-actions gift-form-actions"><span>Cấu hình cũ chỉ bị thay sau khi bấm lưu.</span><a class="btn btn-secondary" href="<?= admin_escape(admin_url($rankingReturnPath)) ?>">Hủy</a><button class="btn btn-primary" type="submit">Lưu quà Top <?= $configureRankLabel ?></button></div>
        </form>
    </div>
</section>
<div class="catalog-modal" id="itemCatalogModal" hidden>
    <div class="catalog-backdrop" data-close-catalog></div>
    <section class="catalog-dialog" role="dialog" aria-modal="true" aria-labelledby="catalogTitleTop">
        <header><div><h2 id="catalogTitleTop">Chọn vật phẩm Top <?= $configureRankLabel ?></h2><p>Dữ liệu trực tiếp từ item_template</p></div><button type="button" data-close-catalog>×</button></header>
        <div class="catalog-search"><input class="form-control" type="search" data-catalog-search placeholder="Nhập tên vật phẩm hoặc ID..."></div>
        <div class="catalog-results" data-catalog-results></div>
        <footer><button class="btn btn-secondary" type="button" data-catalog-prev>← Trước</button><span data-catalog-page>Trang 1/1</span><button class="btn btn-secondary" type="button" data-catalog-next>Tiếp →</button></footer>
    </section>
</div>
<script>
window.giftCodeBuilderConfig = <?= json_encode([
    'catalogUrl' => admin_url('api/catalog.php'),
    'options' => $options,
    'initialRewards' => $topInitialRewards,
    'fixedItemOptions' => $configureRank === '1' ? $narutoTopRewardPresets : [],
    'blockedItemIds' => $configureRank === '1' ? [] : array_keys($narutoTopRewardPresets),
    'requiredMessage' => 'Phải có ít nhất một phần thưởng mặc định.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= admin_escape(admin_url('assets/giftcode-builder.js?v=20260815-naruto-top')) ?>"></script>
<?php endif; ?>

<?php if ($showForm): ?>
<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Trao quà ngay cho người chơi</h2><p>Không cần chờ chốt Top; người chơi nhận tại mục Hòm thư của NPC nhà</p></div><span class="spacer"></span><a class="btn btn-secondary btn-sm" href="<?= admin_escape(admin_url('mailboxes.php')) ?>">Đóng</a></div>
    <div class="panel-body">
        <div class="note"><strong>Trao quà bất kỳ lúc nào:</strong> nhập đúng tên nhân vật, chọn vật phẩm rồi gửi. Có thể gửi cho người chơi đang online, offline hoặc tài khoản admin để test. Chỉ chọn hạng Top 1–10 khi muốn thư hiển thị nhãn Top; quà test/quà Admin thì để trống hạng.</div>
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="send">
            <div class="form-group"><label>TÊN NHÂN VẬT NHẬN</label><input class="form-control" name="player_name" maxlength="20" placeholder="Nhập chính xác tên trong game" required></div>
            <div class="form-group"><label>HẠNG XẾP HẠNG</label><select class="form-control" name="rank_position"><option value="">Quà Admin / không xếp hạng</option><?php for ($manualRank = 1; $manualRank <= 10; $manualRank++): ?><option value="<?= $manualRank ?>">Top <?= $manualRank ?></option><?php endfor; ?></select></div>
            <div class="form-group"><label>TIÊU ĐỀ THƯ</label><input class="form-control" name="title" maxlength="120" value="Quà xếp hạng" required></div>
            <div class="form-group"><label>NGƯỜI GỬI HIỂN THỊ</label><input class="form-control" name="sender_name" maxlength="50" value="Admin" required></div>
            <div class="form-group full"><label>LỜI NHẮN / NỘI DUNG</label><textarea class="form-control" name="message" maxlength="500" placeholder="Ví dụ: Phần thưởng đua top tháng 8/2026"></textarea></div>

            <div class="form-group full gift-builder" data-gift-builder>
                <input type="hidden" name="rewards_json" id="rewards_json">
                <div class="builder-head"><div><label>DANH SÁCH VẬT PHẨM</label><p class="help">Chọn vật phẩm, số lượng và các option sẽ trao.</p></div><button class="btn btn-primary" type="button" data-open-catalog>+ Chọn vật phẩm từ database</button></div>
                <div class="reward-empty" data-reward-empty><strong>Chưa có phần thưởng</strong>Bấm nút phía trên để tìm theo tên hoặc ID.</div>
                <div class="reward-list" data-reward-list></div>
            </div>
            <div class="form-actions gift-form-actions"><span>Thư có hiệu lực ngay; thư chưa nhận có thể thu hồi.</span><a class="btn btn-secondary" href="<?= admin_escape(admin_url('mailboxes.php')) ?>">Hủy</a><button class="btn btn-primary" type="submit">Trao quà ngay</button></div>
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
    'initialRewards' => [],
    'requiredMessage' => 'Hòm thư phải có ít nhất một phần thưởng.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= admin_escape(admin_url('assets/giftcode-builder.js?v=20260803-mailbox')) ?>"></script>
<?php endif; ?>

<div class="grid-2" style="margin-bottom:20px">
    <section class="panel">
        <div class="panel-head"><div><h2>Lịch sử chốt top</h2><p><?= admin_escape($ranking['name']) ?></p></div></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Lệnh</th><th>Đợt</th><th>Trạng thái</th><th>Kết quả</th><th>Thời gian</th></tr></thead>
            <tbody>
            <?php foreach ($topCommands as $command):
                $commandBadge = match ($command['status']) {
                    'DONE' => ['green', 'Đã chốt'], 'FAILED' => ['red', 'Lỗi'],
                    'PROCESSING' => ['blue', 'Đang xử lý'], default => ['orange', 'Đang chờ'],
                };
            ?>
                <tr><td class="mono">#<?= (int) $command['id'] ?></td><td><strong><?= admin_escape($command['batch_title']) ?></strong><br><small class="mono"><?= admin_escape($command['batch_key']) ?></small></td><td><span class="badge badge-<?= $commandBadge[0] ?>"><?= $commandBadge[1] ?></span></td><td><?= (int) $command['winner_count'] ?> người<br><small class="text-muted"><?= admin_escape($command['result_message'] ?? '') ?></small></td><td class="nowrap"><?= admin_escape(admin_datetime($command['created_at'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$topCommands): ?><tr><td colspan="5"><div class="empty"><strong>Chưa chốt đợt nào</strong>Lịch sử sẽ xuất hiện sau khi admin tạo lệnh chốt.</div></td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Người thắng đợt gần nhất</h2><p>Snapshot bất biến gắn với thư đã tạo</p></div></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Hạng</th><th>Nhân vật / ID đích</th><th>Điểm chốt</th><th>Thư</th></tr></thead>
            <tbody>
            <?php foreach ($latestWinners as $winner): ?>
                <tr><td><span class="badge badge-blue">Top <?= (int) $winner['rank_position'] ?></span></td><td><strong><?= admin_escape($winner['player_name']) ?></strong><br><small class="mono">player <?= (int) $winner['player_id'] ?> · account <?= (int) $winner['account_id'] ?></small></td><td><strong><?= $rankingKey === 'top_task' ? 'Nhiệm vụ ' . admin_number($winner['score']) : admin_number($winner['score']) ?></strong></td><td><span class="mono">#<?= (int) $winner['mailbox_id'] ?></span><br><span class="badge <?= $winner['mail_status'] === 'CLAIMED' ? 'badge-green' : 'badge-orange' ?>"><?= $winner['mail_status'] === 'CLAIMED' ? 'Đã nhận' : 'Chưa nhận' ?></span></td></tr>
            <?php endforeach; ?>
            <?php if (!$latestWinners): ?><tr><td colspan="4"><div class="empty"><strong>Chưa có kết quả</strong>Sau khi game server chốt xong, Top 1–10 của đúng kỳ sẽ hiện ở đây.</div></td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</div>

<div class="toolbar">
    <a class="btn btn-primary" href="<?= admin_escape(admin_url('mailboxes.php?create=1')) ?>">+ Trao quà ngay</a>
    <select class="filter-select" onchange="window.location.href=this.value">
        <option value="<?= admin_escape(admin_url('mailboxes.php')) ?>">Tất cả trạng thái</option>
        <?php foreach ($allowedStatuses as $itemStatus): ?><option value="<?= admin_escape(admin_url('mailboxes.php?status=' . $itemStatus)) ?>" <?= $status === $itemStatus ? 'selected' : '' ?>><?= admin_escape($itemStatus) ?></option><?php endforeach; ?>
    </select>
    <span class="count">Hiển thị <?= count($mails) ?> thư gần nhất</span>
</div>
<section class="panel"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Mã</th><th>Người nhận</th><th>Thư</th><th>Phần thưởng</th><th>Trạng thái</th><th>Thời gian</th><th class="text-right">Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($mails as $mail):
        $badge = match ($mail['status']) {
            'PENDING' => ['orange', 'Chưa nhận'], 'PROCESSING' => ['blue', 'Đang xử lý'],
            'CLAIMED' => ['green', 'Đã nhận'], default => ['red', 'Đã thu hồi'],
        };
    ?>
        <tr>
            <td class="mono">#<?= (int) $mail['id'] ?></td>
            <td><strong><?= admin_escape($mail['player_name']) ?></strong><br><small class="text-muted"><?= admin_escape($mail['username']) ?></small></td>
            <td><strong><?= $mail['rank_position'] ? '<span class="badge badge-blue">Top ' . (int) $mail['rank_position'] . '</span> ' : '' ?><?= admin_escape($mail['title']) ?></strong><br><small class="text-muted">Từ <?= admin_escape($mail['sender_name']) ?></small></td>
            <td class="gift-summary"><?= admin_escape(mb_strimwidth(mailbox_reward_summary($mail['rewards_json'], $itemNames), 0, 140, '…')) ?></td>
            <td><span class="badge badge-<?= $badge[0] ?>"><?= $badge[1] ?></span></td>
            <td class="nowrap"><?= admin_escape(admin_datetime($mail['created_at'])) ?><?php if ($mail['claimed_at']): ?><br><small class="text-muted">Nhận: <?= admin_escape(admin_datetime($mail['claimed_at'])) ?></small><?php endif; ?></td>
            <td class="text-right"><?php if ($mail['status'] === 'PENDING'): ?><form method="post" data-confirm="Thu hồi thư #<?= (int) $mail['id'] ?>?"><?= admin_csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="mail_id" value="<?= (int) $mail['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Thu hồi</button></form><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$mails): ?><tr><td colspan="7"><div class="empty"><strong>Chưa có thư</strong>Gửi phần thưởng đầu tiên cho người chơi.</div></td></tr><?php endif; ?>
    </tbody>
</table></div></section>
<?php admin_render_footer(); ?>
