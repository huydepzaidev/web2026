<?php
include_once('../connect.php'); 

function log_thesieure_callback($message, $type = 'INFO') {
    $log_file = __DIR__ . '/card_api_debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, $timestamp . " [" . strtoupper($type) . "] " . $message . "\n", FILE_APPEND);
}

log_thesieure_callback("Raw POST Data: " . print_r($_POST, true));

$status = isset($_POST['status']) ? (int)$_POST['status'] : -1;
$message = isset($_POST['message']) ? $_POST['message'] : 'Không có thông báo';
$value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
$code = isset($_POST['code']) ? $_POST['code'] : null;
$serial = isset($_POST['serial']) ? $_POST['serial'] : null;
$request_id = isset($_POST['request_id']) ? $_POST['request_id'] : null;
$telco = isset($_POST['telco']) ? $_POST['telco'] : null;
$callback_sign = isset($_POST['callback_sign']) ? $_POST['callback_sign'] : null;

$string_to_sign = $thesieure_partner_key . $request_id . $status . $value . $amount . $serial . $code;
$expected_sign = md5($string_to_sign);

if ($callback_sign !== $expected_sign) {
    log_thesieure_callback("ERROR: Invalid callback_sign for request_id: " . $request_id . 
                         " - Expected: " . $expected_sign . " | Received: " . $callback_sign, 'ERROR');
    http_response_code(200);
    exit('Invalid Sign'); 
}

if ($request_id) {
    $stmt = $conn->prepare("SELECT name, is_credited, status_text FROM payments WHERE request_id = ?");
    $stmt->bind_param("s", $request_id);
    $stmt->execute();
    $result_db = $stmt->get_result();
    $transaction = $result_db->fetch_assoc();
    $stmt->close();

    if ($transaction) {
        $username = $transaction['name'];
        $is_credited = $transaction['is_credited'];

        $status_text = '';
        switch ($status) {
            case 1:
                $status_text = 'Thành công';
                break;
            case 2:
                $status_text = 'Sai mệnh giá';
                break;
            case 3:
                $status_text = 'Thẻ sai/đã dùng';
                break;
            case 4:
                $status_text = 'Bảo trì';
                break;
            case 99:
                $status_text = 'Đang xử lý';
                break;
            default:
                $status_text = 'Không xác định (' . $status . ')';
                break;
        }

        $update_stmt = $conn->prepare("UPDATE payments SET status_code = ?, api_response_msg = ?, detected_value = ?, status_text = ?, updated_at = NOW() WHERE request_id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("issss", $status, $message, $value, $status_text, $request_id);
            if ($update_stmt->execute()) {
                log_thesieure_callback("SUCCESS: Updated transaction ID " . $request_id . " to status " . $status_text);

                if ($status == 1 && $is_credited == 0) {
                    $conn->begin_transaction();
                    try {
                        $update_user_balance_stmt = $conn->prepare("UPDATE account SET vnd = vnd + ? WHERE username = ?");
                        if ($update_user_balance_stmt) {
                            $update_user_balance_stmt->bind_param("is", $amount, $username);
                            if ($update_user_balance_stmt->execute()) {
                                $update_credited_flag_stmt = $conn->prepare("UPDATE payments SET is_credited = 1 WHERE request_id = ?");
                                $update_credited_flag_stmt->bind_param("s", $request_id);
                                if ($update_credited_flag_stmt->execute()) {
                                    $conn->commit();
                                    log_thesieure_callback("Credited " . $amount . " VND to user " . $username . " for request_id: " . $request_id);
                                } else {
                                    $conn->rollback();
                                    log_thesieure_callback("ERROR: Failed to update is_credited flag for request_id: " . $request_id . ": " . $update_credited_flag_stmt->error, 'ERROR');
                                }
                                $update_credited_flag_stmt->close();
                            } else {
                                $conn->rollback();
                                log_thesieure_callback("ERROR: Failed to update user balance for " . $username . " with request_id: " . $request_id . ": " . $update_user_balance_stmt->error, 'ERROR');
                            }
                            $update_user_balance_stmt->close();
                        } else {
                            $conn->rollback();
                            log_thesieure_callback("ERROR: Failed to prepare user balance update statement: " . $conn->error, 'ERROR');
                        }
                    } catch (Exception $e) {
                        $conn->rollback();
                        log_thesieure_callback("EXCEPTION: Transaction failed for request_id: " . $request_id . ": " . $e->getMessage(), 'ERROR');
                    }
                } elseif ($status == 1 && $is_credited == 1) {
                    log_thesieure_callback("WARNING: Request ID " . $request_id . " already credited. Skipping duplicate credit operation.", 'WARNING');
                }

            } else {
                log_thesieure_callback("ERROR: Failed to update transaction ID " . $request_id . " in payments table: " . $update_stmt->error, 'ERROR');
            }
            $update_stmt->close();
        } else {
            log_thesieure_callback("ERROR: Failed to prepare update statement for payments table: " . $conn->error, 'ERROR');
        }

    } else {
        log_thesieure_callback("WARNING: Request ID " . $request_id . " not found in payments table. Possible mismatch or initial insert failed.", 'WARNING');
    }
} else {
    log_thesieure_callback("ERROR: No request_id received in callback.", 'ERROR');
}

http_response_code(200);
echo "OK"; 
?>