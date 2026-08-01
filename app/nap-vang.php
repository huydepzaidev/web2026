<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__ . '/../forum_data.php'; 
include_once 'account_info.php';

$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['message_type'])) {
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message_type']);
}

function handle_gold_exchange($conn, $account_id, &$user_balance, $current_player_id, $items_bag_json_from_db) {
    global $message, $message_type;

    $gold_amount_to_exchange = intval($_POST['gold_amount'] ?? 0);
    $exchange_rate = 250;
    $item_id_gold = 457;

    error_log("DEBUG_EXCHANGE: Bắt đầu xử lý đổi vàng cho Account ID: " . $account_id . ", Player ID: " . $current_player_id . ", Lượng vàng: " . $gold_amount_to_exchange);

    if ($gold_amount_to_exchange <= 0) {
        $message = 'Số lượng vàng cần đổi phải lớn hơn 0.';
        $message_type = 'error';
        return;
    }
    
    $cost_in_vnd = $gold_amount_to_exchange * $exchange_rate;

    if ($user_balance < $cost_in_vnd) {
        $message = 'Số dư VND không đủ để đổi ' . $gold_amount_to_exchange . ' thỏi vàng. Bạn cần ' . number_format($cost_in_vnd) . ' VND.';
        $message_type = 'error';
        return;
    }

    if ($current_player_id === null || $current_player_id == 0) {
        $message = 'Bạn chưa có nhân vật trong game. Vui lòng tạo nhân vật trước khi đổi vàng.';
        $message_type = 'error';
        return;
    }

    if (!($conn instanceof mysqli)) {
        $message = 'Lỗi kết nối cơ sở dữ liệu.';
        $message_type = 'error';
        return;
    }

    $conn->begin_transaction();

    try {
        $update_vnd_stmt = $conn->prepare("UPDATE account SET vnd = vnd - ? WHERE id = ? AND vnd >= ?");
        if (!$update_vnd_stmt) {
            throw new Exception("Lỗi prepare update VND: " . $conn->error);
        }
        $update_vnd_stmt->bind_param("iii", $cost_in_vnd, $account_id, $cost_in_vnd);
        $update_vnd_stmt->execute();
        if ($update_vnd_stmt->affected_rows === 0) {
            throw new Exception("Không thể trừ VND. Có thể tài khoản không tồn tại hoặc số dư không đủ (đã kiểm tra lại).");
        }
        $update_vnd_stmt->close();
        $current_timestamp = round(microtime(true) * 1000);
        $options_php_array = [[73, 0]];
        $options_json_string_inner = json_encode($options_php_array, JSON_UNESCAPED_UNICODE);

        $new_gold_item_array = [
            $item_id_gold,
            $gold_amount_to_exchange,
            $options_json_string_inner,
            $current_timestamp
        ];

        $player_items_array_for_update = [];
        
        $outer_array_of_item_strings = json_decode($items_bag_json_from_db, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($outer_array_of_item_strings)) {
            foreach ($outer_array_of_item_strings as $item_str) {
                if (is_string($item_str)) {
                    $decoded_inner_item = json_decode($item_str, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_inner_item) && count($decoded_inner_item) >= 4) {
                        $player_items_array_for_update[] = $decoded_inner_item;
                    } else {
                        $player_items_array_for_update[] = [-1, 0, '[]', 0];
                        error_log("DEBUG_EXCHANGE: Item trong items_bag không hợp lệ, thêm slot trống. Original: " . $item_str);
                    }
                } else {
                    $player_items_array_for_update[] = [-1, 0, '[]', 0];
                    error_log("DEBUG_EXCHANGE: Phần tử trong items_bag không phải chuỗi, thêm slot trống.");
                }
            }
        } else {
            error_log("DEBUG_EXCHANGE: Lỗi giải mã lớp ngoài items_bag hoặc không phải mảng: " . json_last_error_msg() . " - " . $items_bag_json_from_db);
            $player_items_array_for_update = [];
        }

        $item_found_and_updated = false;
        $empty_slot_index = -1;
        foreach ($player_items_array_for_update as $index => &$item) {
            if (is_array($item) && isset($item[0]) && $item[0] == $item_id_gold) {
                $item[1] = ($item[1] ?? 0) + $gold_amount_to_exchange;
                $item_found_and_updated = true;
                error_log("DEBUG_EXCHANGE: Đã cập nhật vàng hiện có tại index " . $index . ". New amount: " . $item[1]);
                break;
            }
            if (is_array($item) && isset($item[0]) && $item[0] == -1 && isset($item[1]) && $item[1] == 0 && isset($item[2]) && $item[2] === '[]') {
                if ($empty_slot_index === -1) {
                    $empty_slot_index = $index;
                    error_log("DEBUG_EXCHANGE: Đã tìm thấy slot trống tại index " . $index);
                }
            }
        }
        unset($item);
        if (!$item_found_and_updated) {
            if ($empty_slot_index !== -1) {
                $player_items_array_for_update[$empty_slot_index] = $new_gold_item_array;
                error_log("DEBUG_EXCHANGE: Thêm vàng mới vào slot trống tại index " . $empty_slot_index);
            } else {
                $player_items_array_for_update[] = $new_gold_item_array;
                error_log("DEBUG_EXCHANGE: Thêm vàng mới vào cuối hành trang (không có slot trống hoặc vàng hiện có).");
            }
        }
        $final_items_for_db = [];
        foreach ($player_items_array_for_update as $item_php_array) {
            $final_items_for_db[] = json_encode($item_php_array, JSON_UNESCAPED_UNICODE);
        }
        $new_items_bag_json = json_encode($final_items_for_db, JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("DEBUG_EXCHANGE: LỖI mã hóa JSON items_bag (cuối cùng). Error: " . json_last_error_msg());
            throw new Exception("Lỗi mã hóa JSON items_bag: " . json_last_error_msg());
        }
        error_log("DEBUG_EXCHANGE: Chuỗi JSON cuối cùng để lưu vào DB: " . $new_items_bag_json);
        $update_items_bag_stmt = $conn->prepare("UPDATE player SET items_bag = ? WHERE id = ?");
        if (!$update_items_bag_stmt) {
            throw new Exception("Lỗi prepare cập nhật items_bag: " . $conn->error);
        }
        $update_items_bag_stmt->bind_param("si", $new_items_bag_json, $current_player_id);
        $update_items_bag_stmt->execute();
        if ($update_items_bag_stmt->affected_rows === 0) {
            throw new Exception("Không thể cập nhật túi đồ của nhân vật. Có thể nhân vật không tồn tại hoặc dữ liệu không thay đổi.");
        }
        $update_items_bag_stmt->close();

        $conn->commit();
        $message = 'Chúc mừng bạn đã đổi thành công ' . $gold_amount_to_exchange . ' thỏi vàng! Chúc bạn chơi game vui vẻ.';
        $message_type = 'success';
        $user_balance -= $cost_in_vnd;
        $_SESSION['vnd'] = $user_balance;
        error_log("DEBUG_EXCHANGE: Giao dịch thành công.");

    } catch (Exception $e) {
        $conn->rollback();
        $message = 'Giao dịch thất bại: ' . $e->getMessage();
        $message_type = 'error';
        error_log("DEBUG_EXCHANGE: Giao dịch thất bại: " . $e->getMessage());
    } finally {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
        header("Location: nap-vang.php");
        exit();
    }
}
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'exchange_gold') {
    handle_gold_exchange($conn, $account_id, $user_balance, $current_player_id, $items_bag_json_from_db);
}

function get_sticky_posts($conn) {
    $sticky_posts = [];
    if ($conn instanceof mysqli) {
        $sql = "SELECT id, tieude, image FROM posts WHERE ghimbai = 1 ORDER BY created_at DESC, id DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_array = json_decode($row['image']);
                    $image_file = (!empty($image_array) && is_array($image_array) && isset($image_array[0])) ? $image_array[0] : '6101.gif';
                    $row['full_image_path'] = 'images/avatar/' . htmlspecialchars($image_file);
                    $sticky_posts[] = $row;
                }
            }
            $result->free();
        } else {
            error_log("Lỗi truy vấn bài viết ghim: " . $conn->error);
        }
    } else {
        error_log("Không có kết nối CSDL khi cố gắng lấy bài viết ghim.");
    }
    return $sticky_posts;
}
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Nạp vàng - Chú Bé Rồng Online</title>
    <link rel="stylesheet" href="/app/wiew/css/StyleSheet.css" type="text/css" />
    <link rel="stylesheet" href="/app/wiew/css/template.css" type="text/css" />
    <script src="/view/static/js/disable_devtools.js"></script>
    <link rel="shortcut icon" href='https://forum.ngocrongonline.com/app/view/images/favicon.png' type="image/x-icon" />
    <script>
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-22738816-4']);
        _gaq.push(['_setDomainName', '.teamobi.com']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>
    <link rel="stylesheet" href="/app/wiew/css/w3.css">
</head>
<style>
    .snowEffect {
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 99;
        overflow: hidden;
        pointer-events: none;
    }
    #snowcanvas {
        position: fixed;
        z-index: 0;
    }
    /* CSS cho thông báo */
    .message {
        margin-top: 10px;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
    }
    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .exchange-form-table td {
        padding: 5px;
    }
    .exchange-form-table label {
        display: block;
        text-align: right;
        margin-right: 10px;
        color: #561d00;
        font-weight: bold;
    }
    .exchange-form-table input[type="number"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .exchange-rate-info {
        font-size: 11px;
        color: #666;
        margin-top: 5px;
        text-align: left;
    }
</style>
<body>
<div class="snowEffect">
    <canvas id="snowcanvas" height="100%" width="100%"></canvas>
</div>
<div class="body_body">
    <div class="left_top"></div>
    <div class="bg_top">
        <div class="right_top"></div>
    </div>
    <div class="body-content">
        <div class="a" align="center"><img src="/images/logo_sk_he.png" height="90"/></div>
        <div id="top">
            <div class="link-more">
                <div class="h" align="center">
                    <div class="bg_tree"></div>
                    <div class="bg_noel"></div>
                    <div class="menu2" style="background: #561d00;">
                        <table width="100%" border="0" cellspacing="4">
                            <tr class="menu">
                                <td><a href="">Trang Chủ</a></td>
                                <td id="selected"><a href="/forum">Diễn Đàn</a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="body" style="text-align:center">
                        <div style="font-size:10px;">Đổi Thỏi Vàng.</div>
                        <center>
                            <?php if (!$is_logged_in): ?>
                                <p class="message error">Bạn cần đăng nhập để sử dụng chức năng này. <a href="login">Đăng nhập</a></p>
                            <?php else: ?>
                                <div style="margin-bottom: 10px; font-weight: bold; color: #561d00;">
                                    Số dư VND của bạn: <span style="color: red;"><?php echo number_format($user_balance ?? 0); ?></span> VND
                                </div>
                                <form id="exchangeGoldForm" method="POST" action="">
                                    <input type="hidden" name="action" value="exchange_gold" />
                                    <table class="exchange-form-table" style="margin: 0 auto;">
                                        <tr>
                                            <td><label for="gold_amount">Số thỏi vàng muốn đổi:</label></td>
                                            <td><input name="gold_amount" type="number" min="1" value="0" required /></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div class="exchange-rate-info">
                                                    Thỏi vàng sẽ được cộng vào túi đồ nhân vật (lưu ý: thoát game trước khi giao dịch) <br>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php if (!empty($message)): ?>
                                        <div class="message <?php echo htmlspecialchars($message_type); ?>" style="display:block;">
                                            <?php echo htmlspecialchars($message); ?>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="w3-button w3-red" value="Đổi Vàng" id="button1" name="submit">Đổi Vàng</button><br />
                                    <div style="font-size:10px; margin-top: 10px;">
                                        <a href="">Về trang chủ</a>
                                    </div>
                                </form><br>
                            <?php endif; ?>
                        </center>
                    </div>
                </div>
                <br>
            </div><br>
        </div>
    </div>
    <div class="left_b_bottom">
        <div class="right_b_bottom">
            <div class="footer">
                <div class="left_bottom"></div>
                <div class="right_bottom"></div>
            </div>
        </div>
    </div>
    <div class="copyright"><br><b>Bản quyền thuộc về Chú Bé Rồng Online - 2013</b></div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    var initialMessageDiv = $('.message');
    if (initialMessageDiv.length && initialMessageDiv.text().trim() === '') {
        initialMessageDiv.hide();
    }
    $('#exchangeGoldForm').submit(function(e) {
    });
});
</script>

<script src="https://ngocrongonline.com/view/static/js/ThreeCanvas.js"></script>
<script src="https://ngocrongonline.com/view/static/js/Snow3d.js"></script>
<script src="https://ngocrongonline.com/view/static/js/animation.js?v4"></script>
</body>
</html>
