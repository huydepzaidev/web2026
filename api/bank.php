<?php

session_start();
include_once '../connect.php'; // Chứa $conn
include_once '../forum_data.php'; // Nếu cần các biến session khác

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request or data.'];

function log_activity($message, $type = 'info') {
    $log_file = __DIR__ . '/sepay_webhook_debug.log'; // Log riêng cho SePay webhook
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, $timestamp . " [" . strtoupper($type) . "] " . $message . "\n", FILE_APPEND);
}

// DEBUGGING BLOCK: Ghi log chi tiết về request nhận được
log_activity("DEBUG: =========== New Webhook Request Start ===========");
log_activity("DEBUG: Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));
log_activity("DEBUG: Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
log_activity("DEBUG: Remote IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));

$all_headers = [];
if (function_exists('getallheaders')) {
    $all_headers = getallheaders();
} else {
    // Fallback for environments where getallheaders() is not available (e.g., Nginx + PHP-FPM)
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $all_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
}
log_activity("DEBUG: All Headers: " . json_encode($all_headers));

$input_raw_debug = file_get_contents('php://input');
log_activity("DEBUG: Raw input from php://input (length " . strlen($input_raw_debug) . "): " . (empty($input_raw_debug) ? "[EMPTY]" : $input_raw_debug));
log_activity("DEBUG: ===================================================");

// KẾT THÚC DEBUGGING BLOCK

// Kiểm tra phương thức yêu cầu (GET hoặc POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $input_raw = $input_raw_debug; // Sử dụng dữ liệu đã lấy ở phần debug
    $data = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($content_type, 'application/json') !== false) {
            $data = json_decode($input_raw, true);
            log_activity("Received POST Webhook Data (JSON): " . $input_raw);
        } elseif (stripos($content_type, 'application/x-www-form-urlencoded') !== false || empty($content_type)) {
            parse_str($input_raw, $data);
            log_activity("Received POST Webhook Data (Form Encoded): " . $input_raw);
        } else {
            // Fallback: Thử cả JSON và form-urlencoded nếu Content-Type không rõ ràng
            $data = json_decode($input_raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                parse_str($input_raw, $data);
            }
            log_activity("Received POST Webhook Data (Unknown Content-Type, tried parsing): " . $input_raw);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $input_raw = http_build_query($_GET);
        $data = $_GET;
        log_activity("Received GET Webhook Data (from GET params): " . $input_raw);
    }

    if (empty($data)) {
        $response['message'] = 'Dữ liệu webhook không hợp lệ (không phải JSON/Form data hoặc trống).';
        log_activity("Dữ liệu nhận được không hợp lệ: " . $input_raw, 'ERROR');
        echo json_encode($response);
        exit();
    }

    $received_signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

    $calculated_signature = hash_hmac('sha256', $input_raw, $sepay_webhook_secret);

    // Xác minh chữ ký: Nếu có chữ ký được gửi và nó không khớp thì từ chối.
    // Nếu không có chữ ký được gửi (phổ biến với GET hoặc cấu hình SePay khác), thì bỏ qua bước này.
    if (!empty($received_signature) && !hash_equals($calculated_signature, $received_signature)) {
        $response['message'] = 'Chữ ký webhook không hợp lệ. Yêu cầu bị từ chối.';
        log_activity("Chữ ký webhook không hợp lệ. Dữ liệu: " . $input_raw . " - Chữ ký nhận được: " . $received_signature . " - Chữ ký tính toán: " . $calculated_signature, 'WARNING');
        echo json_encode($response);
        exit();
    }

    $transaction_id = $data['transaction_id'] ?? $data['refId'] ?? null;
    $amount = (int)($data['amount'] ?? 0);
    $description = $data['description'] ?? $data['content'] ?? '';
    $status = $data['status'] ?? '';
    $bank_account_number = $data['receiverAccount'] ?? '';
    $sender_bank_name = $data['senderBankName'] ?? '';
    $transfer_time = $data['transactionTime'] ?? date('Y-m-d H:i:s');

    if (empty($transaction_id) || $amount <= 0 || empty($status)) {
        $response['message'] = 'Dữ liệu giao dịch thiếu thông tin quan trọng từ SePay.';
        log_activity("Dữ liệu giao dịch thiếu thông tin quan trọng từ SePay: " . json_encode($data), 'ERROR');
        echo json_encode($response);
        exit();
    }

    // --- TRÍCH XUẤT USERNAME TỪ DESCRIPTION ---
    $username_from_description = '';
    preg_match('/\[(.*?)\]/', $description, $matches);
    if (isset($matches[1])) {
        $username_from_description = trim($matches[1]);
    }

    // Kiểm tra từ khóa "naptien" trong nội dung mô tả
    $is_top_up_transaction = (stripos($description, 'naptien') !== false);

    if (empty($username_from_description)) {
        $response['status'] = 'error';
        $response['message'] = 'Không tìm thấy tên người dùng trong nội dung chuyển khoản. Yêu cầu nội dung có định dạng [username].';
        log_activity("Không tìm thấy tên người dùng trong nội dung chuyển khoản: " . $description . " - Transaction ID: " . $transaction_id, 'WARNING');
        echo json_encode($response);
        exit();
    }

    // --- KIỂM TRA USERNAME CÓ TỒN TẠI TRONG BẢNG `account` KHÔNG ---
    // Đảm bảo tên bảng là `account` như bạn đã cung cấp
    $stmt_check_user = $conn->prepare("SELECT id FROM `account` WHERE username = ?");
    if ($stmt_check_user) {
        $stmt_check_user->bind_param("s", $username_from_description);
        $stmt_check_user->execute();
        $stmt_check_user->store_result();
        if ($stmt_check_user->num_rows === 0) {
            $response['status'] = 'error';
            $response['message'] = 'Tên tài khoản "' . htmlspecialchars($username_from_description) . '" không tồn tại trong hệ thống. Vui lòng kiểm tra lại nội dung chuyển khoản hoặc liên hệ hỗ trợ.';
            log_activity("Tên tài khoản không tồn tại: " . $username_from_description . " trong giao dịch " . $transaction_id, 'WARNING');
            echo json_encode($response);
            $stmt_check_user->close();
            exit();
        }
        $stmt_check_user->close();
    } else {
        log_activity("Lỗi prepare kiểm tra người dùng tồn tại: " . $conn->error, 'ERROR');
        $response['message'] = 'Lỗi hệ thống khi kiểm tra tài khoản người dùng.';
        echo json_encode($response);
        exit();
    }

    // --- KIỂM TRA GIAO DỊCH ĐÃ ĐƯỢC XỬ LÝ CHƯA (CHỐNG TRÙNG LẶP) ---
    $stmt_check_transfer = $conn->prepare("SELECT id FROM bank_transfers WHERE transaction_id = ?");
    if ($stmt_check_transfer) {
        $stmt_check_transfer->bind_param("s", $transaction_id);
        $stmt_check_transfer->execute();
        $stmt_check_transfer->store_result();
        if ($stmt_check_transfer->num_rows > 0) {
            $response['status'] = 'success'; // Trả về success để SePay không gửi lại webhook
            $response['message'] = 'Giao dịch đã được xử lý trước đó.';
            log_activity("Giao dịch đã được xử lý trước đó: " . $transaction_id, 'INFO');
            echo json_encode($response);
            $stmt_check_transfer->close();
            exit();
        }
        $stmt_check_transfer->close();
    } else {
        log_activity("Lỗi prepare kiểm tra giao dịch đã tồn tại: " . $conn->error, 'ERROR');
        $response['message'] = 'Lỗi hệ thống khi kiểm tra giao dịch. Vui lòng liên hệ hỗ trợ.';
        echo json_encode($response);
        exit();
    }

    // --- XỬ LÝ VÀ LƯU TRỮ GIAO DỊCH VÀO DATABASE ---
    $is_credited = 0;
    $final_status = 'pending';

    if ($status === 'success') {
        $final_status = 'success';
        $is_credited = 1;
    } elseif ($status === 'failed' || $status === 'error') {
        $final_status = 'failed';
    } else {
        $final_status = 'unknown';
    }

    $stmt_insert_transfer = $conn->prepare(
        "INSERT INTO bank_transfers (
            `transaction_id`, `username`, `amount`, `description`, `status`,
            `sender_bank_name`, `created_at`, `is_credited`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt_insert_transfer) {
        $stmt_insert_transfer->bind_param(
            "ssissssi",
            $transaction_id,
            $username_from_description,
            $amount,
            $description,
            $final_status,
            $sender_bank_name,
            $transfer_time,
            $is_credited
        );

        if (!$stmt_insert_transfer->execute()) {
            log_activity("Lỗi execute INSERT vào bảng bank_transfers: " . $stmt_insert_transfer->error . " - Data: " . json_encode($data), 'ERROR');
            $response['message'] = 'Lỗi hệ thống khi lưu lịch sử chuyển khoản.';
            echo json_encode($response);
            $stmt_insert_transfer->close();
            exit();
        }
        $stmt_insert_transfer->close();
    } else {
        log_activity("Lỗi prepare INSERT vào bảng bank_transfers: " . $conn->error, 'ERROR');
        $response['message'] = 'Lỗi hệ thống khi chuẩn bị lưu lịch sử chuyển khoản.';
        echo json_encode($response);
        exit();
    }

    // --- CỘNG TIỀN VÀ TỔNG NẠP VÀO TÀI KHOẢN NGƯỜI DÙNG ---
    if ($is_credited === 1 && $is_top_up_transaction) {
        // Cập nhật cả 'vnd' và 'tongnap' trong bảng `account`
        $stmt_update_user_balance = $conn->prepare("UPDATE `account` SET vnd = vnd + ?, tongnap = tongnap + ? WHERE username = ?");
        if ($stmt_update_user_balance) {
            $stmt_update_user_balance->bind_param("iis", $amount, $amount, $username_from_description); // amount cho vnd và tongnap
            if ($stmt_update_user_balance->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Nạp tiền thành công! Tiền đã được cộng vào tài khoản ' . $username_from_description . '.';
                log_activity("Nạp tiền thành công: " . $amount . " VNĐ đã được cộng vào tài khoản " . $username_from_description . " (Transaction ID: " . $transaction_id . ")", 'INFO');
            } else {
                $response['message'] = 'Lỗi khi cộng tiền vào tài khoản người dùng. Vui lòng liên hệ hỗ trợ.';
                log_activity("Lỗi execute UPDATE số dư người dùng (vnd, tongnap): " . $stmt_update_user_balance->error . " - Transaction ID: " . $transaction_id, 'ERROR');
            }
            $stmt_update_user_balance->close();
        } else {
            log_activity("Lỗi prepare UPDATE số dư người dùng (vnd, tongnap): " . $conn->error, 'ERROR');
            $response['message'] = 'Lỗi hệ thống khi chuẩn bị cập nhật số dư người dùng.';
        }
    } else {
        // Giao dịch không thành công hoặc không phải giao dịch nạp tiền, chỉ ghi nhận vào log
        $response['status'] = 'success'; // Trả về success để SePay không gửi lại webhook nếu không phải lỗi của bạn
        $message_log = "Giao dịch được ghi nhận với trạng thái: " . $final_status;
        if (!$is_top_up_transaction) {
            $message_log .= " (Không phải giao dịch nạp tiền)";
        }
        $message_log .= " (Transaction ID: " . $transaction_id . ")";
        log_activity($message_log, 'INFO');
        $response['message'] = 'Giao dịch được ghi nhận. ' . ($is_top_up_transaction ? '' : 'Đây không phải giao dịch nạp tiền hoặc không thành công.');
    }

} else {
    $response['message'] = 'Yêu cầu không hợp lệ. Chỉ chấp nhận phương thức POST hoặc GET.';
    log_activity("Phương thức yêu cầu không hợp lệ: " . $_SERVER['REQUEST_METHOD'] . " từ IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'), 'WARNING');
}

if (isset($conn) && $conn->ping()) {
    $conn->close();
}

echo json_encode($response);

?>
