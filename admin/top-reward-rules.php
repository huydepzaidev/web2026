<?php
declare(strict_types=1);

function mailbox_naruto_top_reward_presets(): array
{
    return [
        2019 => [
            ['id' => 50, 'param' => 15],
            ['id' => 77, 'param' => 15],
            ['id' => 103, 'param' => 15],
            ['id' => 204, 'param' => 10],
            ['id' => 14, 'param' => 7],
            ['id' => 30, 'param' => 0],
        ],
        2026 => [
            ['id' => 77, 'param' => 25],
            ['id' => 22, 'param' => 35],
            ['id' => 94, 'param' => 15],
            ['id' => 30, 'param' => 0],
        ],
        2027 => [
            ['id' => 50, 'param' => 25],
            ['id' => 0, 'param' => 12000],
            ['id' => 14, 'param' => 15],
            ['id' => 5, 'param' => 20],
            ['id' => 30, 'param' => 0],
        ],
        2030 => [
            ['id' => 50, 'param' => 20],
            ['id' => 77, 'param' => 20],
            ['id' => 103, 'param' => 20],
            ['id' => 95, 'param' => 10],
            ['id' => 96, 'param' => 10],
            ['id' => 14, 'param' => 15],
            ['id' => 30, 'param' => 0],
        ],
        2039 => [
            ['id' => 50, 'param' => 22],
            ['id' => 77, 'param' => 22],
            ['id' => 103, 'param' => 22],
            ['id' => 101, 'param' => 55],
            ['id' => 14, 'param' => 10],
            ['id' => 30, 'param' => 0],
        ],
    ];
}

function mailbox_apply_naruto_top_reward_rules(array $rewards, array $targetRanks): array
{
    $presets = mailbox_naruto_top_reward_presets();
    $topOneOnly = count($targetRanks) === 1 && (int) $targetRanks[0] === 1;

    foreach ($rewards as &$reward) {
        $itemId = (int) ($reward['id'] ?? 0);
        if (!isset($presets[$itemId])) {
            continue;
        }
        if (!$topOneOnly) {
            throw new RuntimeException('Vật phẩm trong Rương hợp tác Naruto chỉ được cấu hình cho Top 1.');
        }
        $reward['options'] = $presets[$itemId];
    }
    unset($reward);

    return $rewards;
}
