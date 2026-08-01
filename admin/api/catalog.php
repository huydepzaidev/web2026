<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$catalogType = (string) ($_GET['type'] ?? 'items');
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = $catalogType === 'options' ? 60 : 24;
$offset = ($page - 1) * $perPage;
$term = '%' . $search . '%';

try {
    if ($catalogType === 'items') {
        $specialItems = [
            ['id' => -1, 'name' => 'Vàng', 'type' => -1, 'gender' => 3, 'description' => 'Cộng thẳng vào túi vàng', 'icon_id' => 0, 'special' => true],
            ['id' => -2, 'name' => 'Ngọc', 'type' => -1, 'gender' => 3, 'description' => 'Cộng thẳng vào ngọc', 'icon_id' => 0, 'special' => true],
            ['id' => -3, 'name' => 'Ngọc khóa', 'type' => -1, 'gender' => 3, 'description' => 'Cộng thẳng vào ngọc khóa', 'icon_id' => 0, 'special' => true],
        ];
        $matchingSpecials = array_values(array_filter(
            $specialItems,
            static fn(array $item): bool => $search === ''
                || str_contains(mb_strtolower($item['name']), mb_strtolower($search))
                || (string) $item['id'] === $search
        ));

        $where = "WHERE NAME <> ''";
        $types = '';
        $params = [];
        if ($search !== '') {
            $where .= ' AND (NAME LIKE ? OR CAST(id AS CHAR) = ? OR description LIKE ?)';
            $types = 'sss';
            $params = [$term, $search, $term];
        }

        $databaseTotal = (int) admin_scalar("SELECT COUNT(*) FROM item_template $where", $types, $params);
        $rows = admin_all(
            "SELECT id, NAME AS name, TYPE AS type, gender, description, icon_id
             FROM item_template $where ORDER BY id ASC LIMIT ? OFFSET ?",
            $types . 'ii',
            array_merge($params, [$perPage, $offset])
        );
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['type'] = (int) $row['type'];
            $row['gender'] = (int) $row['gender'];
            $row['icon_id'] = (int) $row['icon_id'];
            $row['special'] = false;
        }
        unset($row);

        if ($page === 1) {
            $rows = array_merge($matchingSpecials, $rows);
        }
        $total = $databaseTotal + count($matchingSpecials);
    } elseif ($catalogType === 'options') {
        $where = '';
        $types = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE NAME LIKE ? OR CAST(id AS CHAR) = ?';
            $types = 'ss';
            $params = [$term, $search];
        }
        $total = (int) admin_scalar("SELECT COUNT(*) FROM item_option_template $where", $types, $params);
        $rows = admin_all(
            "SELECT id, NAME AS name FROM item_option_template $where ORDER BY id ASC LIMIT ? OFFSET ?",
            $types . 'ii',
            array_merge($params, [$perPage, $offset])
        );
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
        }
        unset($row);
    } else {
        throw new RuntimeException('Loại danh mục không hợp lệ.');
    }

    echo json_encode([
        'status' => 'success',
        'items' => $rows,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / $perPage)),
        'total' => $total,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Không thể tải danh mục từ database.',
    ], JSON_UNESCAPED_UNICODE);
}

