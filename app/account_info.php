<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../connect.php';

$is_logged_in = isset($_SESSION['username']);
$account_username = $_SESSION['username'] ?? 'Khách';
$account_id = null;
$user_balance = 0;

$player_name = "Chưa có nhân vật";
$current_player_id = null;
$items_bag_json_from_db = '[]';

function get_account_id_from_username($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM account WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return (int)$row['id'];
        }
        $stmt->close();
    } else {
        error_log("Lỗi prepare lấy account_id từ username: " . $conn->error);
    }
    return null;
}

function get_player_info_for_account($conn, $account_id) {
    $player_data = [
        'id' => null,
        'name' => "Chưa có nhân vật",
        'items_bag' => '[]'
    ];

    if ($conn && $account_id !== null) {
        $stmt = $conn->prepare("SELECT id, name, items_bag FROM player WHERE account_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($data = $result->fetch_assoc()) {
                $player_data['id'] = $data['id'];
                $player_data['name'] = htmlspecialchars($data['name']);
                $player_data['items_bag'] = $data['items_bag'];
            }
            $stmt->close();
        } else {
            error_log("Lỗi prepare lấy thông tin nhân vật: " . $conn->error);
        }
    }
    return $player_data;
}

if ($is_logged_in && isset($conn)) {
    $account_id = get_account_id_from_username($conn, $account_username);

    if ($account_id !== null) {
        $stmt_balance = $conn->prepare("SELECT vnd FROM account WHERE id = ? LIMIT 1");
        if ($stmt_balance) {
            $stmt_balance->bind_param("i", $account_id);
            $stmt_balance->execute();
            $result_balance = $stmt_balance->get_result();
            if ($balance_data = $result_balance->fetch_assoc()) {
                $user_balance = $balance_data['vnd'];
                $_SESSION['vnd'] = $user_balance;
            }
            $stmt_balance->close();
        } else {
            error_log("Lỗi prepare lấy số dư VND: " . $conn->error);
        }

        $player_info = get_player_info_for_account($conn, $account_id);
        $current_player_id = $player_info['id'];
        $player_name = $player_info['name'];
        $items_bag_json_from_db = $player_info['items_bag'];
    }
}
?>
