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
<?php

session_start();
include_once '../connect.php';
include_once '../forum_data.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình xử lý.'];

function log_activity($message, $type = 'info') {
    $log_file = __DIR__ . '/card_api_debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, $timestamp . " [" . strtoupper($type) . "] " . $message . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!function_exists('verify_csrf_token')) {
        function verify_csrf_token($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $response['message'] = 'Lỗi bảo mật: CSRF token không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang và thử lại.';
        log_activity("CSRF Token Mismatch for IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'), 'WARNING');
        echo json_encode($response);
        exit();
    }

    $cardType = strtoupper(trim($_POST['cardType'] ?? ''));
    $declared_amount = (int)($_POST['amount'] ?? 0);
    $cardSerial = trim($_POST['cardSerial'] ?? '');
    $cardPin = trim($_POST['cardPin'] ?? '');
    $account_username = $_SESSION['username'] ?? '';

    if (empty($account_username)) {
        $response['message'] = 'Bạn chưa đăng nhập. Vui lòng đăng nhập để nạp thẻ.';
        echo json_encode($response);
        exit();
    }

    if (empty($cardType) || $declared_amount <= 0 || empty($cardSerial) || empty($cardPin)) {
        $response['message'] = 'Vui lòng nhập đầy đủ và chính xác các thông tin thẻ.';
        echo json_encode($response);
        exit();
    }

    $refNo = uniqid('nrc_');

    $api_params = [
        'telco' => $cardType,
        'code' => $cardPin,
        'serial' => $cardSerial,
        'amount' => $declared_amount,
        'partner_id' => $thesieure_partner_id,
        'request_id' => $refNo,
        'command' => 'charging'
    ];

    $sign_string = $thesieure_partner_key . $api_params['code'] . $api_params['command'] . $api_params['partner_id'] . $api_params['request_id'] . $api_params['serial'] . $api_params['telco'];
    $api_params['sign'] = md5($sign_string);

    log_activity("Dữ liệu gửi đến Thesieure.com: " . json_encode($api_params));

    $payment_status_text = 'Đang chờ Thesieure.com xử lý';
    $api_status_code_initial = 'PENDING_LOCAL';
    $current_date = date('Y-m-d H:i:s');

    $stmt_insert_payment = $conn->prepare(
        "INSERT INTO payments (`name`, `refNo`, `date`, `card_serial`, `card_pin`, `declared_amount`, `status_text`, `api_status_code`, `api_message`, `card_telco`, `is_credited`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt_insert_payment) {
        $initial_is_credited = 0;
        $initial_api_message = 'Đang chờ phản hồi từ Thesieure.com';

        $stmt_insert_payment->bind_param(
            "sssssiisssi",
            $account_username,
            $refNo,
            $current_date,
            $cardSerial,
            $cardPin,
            $declared_amount,
            $payment_status_text,
            $api_status_code_initial,
            $initial_api_message,
            $cardType,
            $initial_is_credited
        );

        if (!$stmt_insert_payment->execute()) {
            log_activity("Lỗi execute INSERT vào bảng payments: " . $stmt_insert_payment->error, 'ERROR');
            $response['message'] = 'Lỗi hệ thống khi lưu lịch sử giao dịch. Vui lòng liên hệ hỗ trợ.';
            echo json_encode($response);
            $stmt_insert_payment->close();
            exit();
        }
        $stmt_insert_payment->close();
    } else {
        log_activity("Lỗi prepare INSERT vào bảng payments: " . $conn->error, 'ERROR');
        $response['message'] = 'Lỗi hệ thống khi chuẩn bị lưu lịch sử giao dịch. Vui lòng liên hệ hỗ trợ.';
        echo json_encode($response);
        exit();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $thesieure_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $api_result_raw = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    log_activity("RequestID: " . $refNo . " - HTTP Code: " . $http_code . " - Raw Response: " . $api_result_raw . " - cURL Error: " . $curl_error);

    if ($api_result_raw === false || $http_code !== 200) {
        $response['message'] = 'Không thể kết nối đến cổng nạp thẻ Thesieure.com. Vui lòng thử lại sau ít phút hoặc liên hệ hỗ trợ.';
        log_activity("Lỗi cURL khi gọi Thesieure.com API cho RequestID: " . $refNo . " - Error: " . $curl_error . " - HTTP Code: " . $http_code, 'ERROR');

        $stmt_update_payment_error = $conn->prepare("UPDATE payments SET status_text = ?, api_status_code = ?, api_message = ? WHERE refNo = ?");
        if ($stmt_update_payment_error) {
            $error_status_text = 'Lỗi kết nối API Thesieure.com';
            $error_api_code = 'CURL_ERROR_' . $http_code;
            $error_api_message = 'HTTP Code: ' . $http_code . ' - ' . ($curl_error ?: 'Lỗi cURL không xác định');
            $stmt_update_payment_error->bind_param("ssss", $error_status_text, $error_api_code, $error_api_message, $refNo);
            $stmt_update_payment_error->execute();
            $stmt_update_payment_error->close();
        } else {
            log_activity("Lỗi prepare UPDATE khi lỗi cURL: " . $conn->error, 'ERROR');
        }
    } else {
        $decoded_response = json_decode($api_result_raw, true);

        if ($decoded_response && isset($decoded_response['status'])) {
            $api_status_code = (string)$decoded_response['status'];
            $api_message = $decoded_response['message'] ?? 'Không có thông báo';

            $api_declared_value = $decoded_response['declared_value'] ?? null;
            $detected_value = $decoded_response['value'] ?? null;

            $stmt_update_payment_api_response = $conn->prepare(
                "UPDATE payments SET status_text = ?, api_status_code = ?, api_message = ?, api_declared_value = ?, detected_value = ? WHERE refNo = ?"
            );

            if ($stmt_update_payment_api_response) {
                $status_text_for_db = 'Thesieure.com: ' . $api_message;

                switch ($api_status_code) {
                    case '99':
                        $response['success'] = true;
                        $response['status'] = 99;
                        $response['message'] = 'Yêu cầu nạp thẻ của bạn đã được ghi nhận. Kết quả sẽ được cập nhật trong ít phút!';
                        $status_text_for_db = 'Chờ xử lý';
                        break;
                    case '1':
                        $response['success'] = true;
                        $response['status'] = 1;
                        $response['message'] = 'Thẻ đúng! Số tiền thực nhận sẽ được cập nhật sau khi hệ thống xử lý hoàn tất.';
                        $status_text_for_db = 'Thành công (Chờ Callback)';
                        break;
                    case '2':
                        $response['message'] = 'Thẻ sai mệnh giá. Vui lòng kiểm tra lại. Mệnh giá thực tế: ' . number_format($detected_value ?? 0, 0, ',', '.') . ' VNĐ.';
                        $status_text_for_db = 'Sai mệnh giá';
                        break;
                    case '3':
                        $response['message'] = 'Thẻ lỗi: Mã thẻ sai hoặc đã được sử dụng. Vui lòng kiểm tra lại.';
                        $status_text_for_db = 'Thẻ lỗi';
                        break;
                    case '4':
                        $response['message'] = 'Hệ thống nạp thẻ đang bảo trì. Vui lòng thử lại sau.';
                        $status_text_for_db = 'Bảo trì';
                        break;
                    case '102':
                        $response['message'] = 'Lỗi cấu hình API: Tài khoản Partner ID không tồn tại hoặc chưa được kích hoạt trên Thesieure.com. Vui lòng kiểm tra lại thông tin Partner ID/Key hoặc liên hệ Thesieure.com.';
                        $status_text_for_db = 'Lỗi cấu hình API';
                        break;
                    default:
                        $response['message'] = 'Thesieure.com phản hồi lỗi: ' . $api_message . ' (Mã: ' . $api_status_code . ').';
                        $status_text_for_db = 'Lỗi Thesieure.com: ' . $api_status_code;
                        break;
                }

                $stmt_update_payment_api_response->bind_param(
                    "sssiis",
                    $status_text_for_db,
                    $api_status_code,
                    $api_message,
                    $api_declared_value,
                    $detected_value,
                    $refNo
                );
                $stmt_update_payment_api_response->execute();
                $stmt_update_payment_api_response->close();
            } else {
                log_activity("Lỗi prepare UPDATE sau phản hồi API: " . $conn->error, 'ERROR');
                $response['message'] = 'Có lỗi xảy ra khi cập nhật trạng thái giao dịch sau phản hồi API. Vui lòng liên hệ hỗ trợ.';
            }

        } else {
            $response['message'] = 'Phản hồi không hợp lệ từ Thesieure.com. Vui lòng liên hệ hỗ trợ.';
            log_activity("Phản hồi không hợp lệ từ Thesieure.com cho RequestID: " . $refNo . " - Response: " . $api_result_raw, 'ERROR');

            $stmt_update_payment_error = $conn->prepare("UPDATE payments SET status_text = ?, api_status_code = ?, api_message = ? WHERE refNo = ?");
            if ($stmt_update_payment_error) {
                $error_status_text = 'Phản hồi API không hợp lệ';
                $error_api_code = 'INVALID_RESPONSE';
                $error_api_message = substr($api_result_raw, 0, 255);
                $stmt_update_payment_error->bind_param("ssss", $error_status_text, $error_api_code, $error_api_message, $refNo);
                $stmt_update_payment_error->execute();
                $stmt_update_payment_error->close();
            } else {
                log_activity("Lỗi prepare UPDATE khi phản hồi không hợp lệ: " . $conn->error, 'ERROR');
            }
        }
    }
} else {
    $response['message'] = 'Yêu cầu không hợp lệ. Chỉ chấp nhận phương thức POST.';
    log_activity("Invalid request method: " . $_SERVER['REQUEST_METHOD'] . " for IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'), 'WARNING');
}

if (isset($conn) && $conn->ping()) {
    $conn->close();
}

echo json_encode($response);

?>