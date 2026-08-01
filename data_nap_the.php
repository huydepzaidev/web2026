<?php
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
$csrf_token = generate_csrf_token();

$history_card_payments = [];
$is_logged_in = isset($_SESSION['username']);
$account_username = $is_logged_in ? $_SESSION['username'] : '';
$user_avatar = $is_logged_in && isset($user_avatar) ? $user_avatar : '/images/default_avatar.png';
$display_player_name = $is_logged_in && isset($display_player_name) ? $display_player_name : '';
$user_vnd = $is_logged_in && isset($user_vnd) ? $user_vnd : 0;


if ($is_logged_in) {
    $current_page_card = isset($_GET['page_card']) ? (int)$_GET['page_card'] : 1;
    $payments_per_page = 5;
    $offset_card = ($current_page_card - 1) * $payments_per_page;
    if ($offset_card < 0) $offset_card = 0;
    $sql_count_card = "SELECT COUNT(*) AS total FROM payments WHERE name = ?";
    $stmt_count_card = $conn->prepare($sql_count_card);
    $total_card_payments = 0;
    if ($stmt_count_card) {
        $stmt_count_card->bind_param("s", $account_username);
        $stmt_count_card->execute();
        $result_count_card = $stmt_count_card->get_result();
        $row_count_card = $result_count_card->fetch_assoc();
        $total_card_payments = $row_count_card['total'];
        $stmt_count_card->close();
    } else {
        error_log("Lỗi prepare đếm lịch sử nạp thẻ (bảng payments): " . $conn->error);
    }
    $total_pages_card = ceil($total_card_payments / $payments_per_page);
    if ($total_pages_card == 0) {
        $current_page_card = 1;
    } elseif ($current_page_card > $total_pages_card) {
        $current_page_card = $total_pages_card;
        $offset_card = ($current_page_card - 1) * $payments_per_page;
        if ($offset_card < 0) $offset_card = 0;
    }
    $sql_card_history = "SELECT
                            card_telco,
                            declared_amount,
                            detected_value,
                            status_text,
                            date AS created_at
                           FROM payments
                           WHERE name = ?
                           ORDER BY date DESC
                           LIMIT ?, ?";
    $stmt_card_history = $conn->prepare($sql_card_history);

    if ($stmt_card_history) {
        $stmt_card_history->bind_param("sii", $account_username, $offset_card, $payments_per_page);
        $stmt_card_history->execute();
        $result_card_history = $stmt_card_history->get_result();
        while ($row = $result_card_history->fetch_assoc()) {
            $history_card_payments[] = $row;
        }
        $stmt_card_history->close();
    } else {
        error_log("Lỗi prepare truy vấn lịch sử thẻ: " . $conn->error);
    }
}
$history_bank_transfers = [];
if ($is_logged_in) {
    $current_page_transfer = isset($_GET['page_transfer']) ? (int)$_GET['page_transfer'] : 1;
    $transfers_per_page = 5;
    $offset_transfer = ($current_page_transfer - 1) * $transfers_per_page;
    if ($offset_transfer < 0) $offset_transfer = 0;

    $sql_count_transfer = "SELECT COUNT(*) AS total FROM bank_transfers WHERE username = ?";
    $stmt_count_transfer = $conn->prepare($sql_count_transfer);
    $total_bank_transfers = 0;
    if ($stmt_count_transfer) {
        $stmt_count_transfer->bind_param("s", $account_username);
        $stmt_count_transfer->execute();
        $result_count_transfer = $stmt_count_transfer->get_result();
        $row_count_transfer = $result_count_transfer->fetch_assoc();
        $total_bank_transfers = $row_count_transfer['total'];
        $stmt_count_transfer->close();
    } else {
        error_log("Lỗi prepare đếm lịch sử chuyển khoản: " . $conn->error);
    }

    $total_pages_transfer = ceil($total_bank_transfers / $transfers_per_page);
    if ($total_pages_transfer == 0) {
        $current_page_transfer = 1;
    } elseif ($current_page_transfer > $total_pages_transfer) {
        $current_page_transfer = $total_pages_transfer;
        $offset_transfer = ($current_page_transfer - 1) * $transfers_per_page;
        if ($offset_transfer < 0) $offset_transfer = 0;
    }

    $sql_transfer_history = "SELECT amount, status, created_at FROM bank_transfers WHERE username = ? ORDER BY created_at DESC LIMIT ?, ?";
    $stmt_transfer_history = $conn->prepare($sql_transfer_history);
    if ($stmt_transfer_history) {
        $stmt_transfer_history->bind_param("sii", $account_username, $offset_transfer, $transfers_per_page);
        $stmt_transfer_history->execute();
        $result_transfer_history = $stmt_transfer_history->get_result();
        while ($row = $result_transfer_history->fetch_assoc()) {
            $history_bank_transfers[] = $row;
        }
        $stmt_transfer_history->close();
    } else {
        error_log("Lỗi prepare lịch sử chuyển khoản: " . $conn->error);
    }
}
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>