<?php
// doi-mat-khau.php - Xử lý đổi mật khẩu và hiển thị form
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../settings.php';
require_once '../forum_data.php';
require_once '../connect.php';
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = null;
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($is_logged_in)) {
    $is_logged_in = isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null && $_SESSION['user_id'] > 0;
}


// Xử lý yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changepass') {
    header('Content-Type: application/json');
    error_log("DEBUG: Session data: " . print_r($_SESSION, true));

    // Kiểm tra đăng nhập
    if (!$is_logged_in || !isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        error_log("DEBUG: Người dùng chưa đăng nhập hoặc user_id không có trong session.");
        echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập để đổi mật khẩu.']);
        exit();
    }

    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $userId = $_SESSION['user_id'];

    error_log("DEBUG: currentPassword (nhập): '" . $currentPassword . "'");
    error_log("DEBUG: newPassword (nhập): '" . $newPassword . "'");
    error_log("DEBUG: confirmPassword (nhập): '" . $confirmPassword . "'");
    error_log("DEBUG: User ID từ session: " . $userId);

    // Kiểm tra các trường không được rỗng
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường mật khẩu.']);
        exit();
    }

    // Kiểm tra độ dài mật khẩu mới
    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']);
        exit();
    }

    // Kiểm tra xác nhận mật khẩu
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.']);
        exit();
    }

    // Lấy mật khẩu từ cơ sở dữ liệu (GIẢ ĐỊNH LÀ PLAINTEXT)
    $stmt = $conn->prepare("SELECT password FROM account WHERE id = ?");
    if (!$stmt) {
        error_log("DEBUG: Lỗi chuẩn bị câu lệnh SQL SELECT password: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống (SQL Prepare Select). Vui lòng thử lại sau.']);
        exit();
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("DEBUG: Không tìm thấy tài khoản với ID: " . $userId);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại.']);
        $stmt->close();
        exit();
    }

    $user = $result->fetch_assoc();
    $passwordFromDb = $user['password']; // Lấy mật khẩu dạng PLAINTEXT từ DB
    $stmt->close();

    error_log("DEBUG: Mật khẩu từ DB: '" . $passwordFromDb . "'");
    error_log("DEBUG: Mật khẩu hiện tại nhập vào (plain): '" . $currentPassword . "'");

    // So sánh mật khẩu hiện tại trực tiếp (PLAINTEXT)
    if ($currentPassword !== $passwordFromDb) { // So sánh trực tiếp
        error_log("DEBUG: Mật khẩu hiện tại không khớp.");
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
        exit();
    }
    $newPasswordToStore = $newPassword;
    $stmt = $conn->prepare("UPDATE account SET password = ? WHERE id = ?");
    if (!$stmt) {
        error_log("DEBUG: Lỗi chuẩn bị câu lệnh SQL UPDATE password: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống (SQL Prepare Update). Vui lòng thử lại sau.']);
        exit();
    }
    $stmt->bind_param("si", $newPasswordToStore, $userId);

    if ($stmt->execute()) {
        error_log("DEBUG: Đổi mật khẩu thành công cho User ID: " . $userId);
        echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
    } else {
        error_log("DEBUG: Lỗi khi cập nhật mật khẩu vào CSDL: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.']);
    }
    $stmt->close();
    exit();
}
if (!function_exists('get_csrf_token')) {
    function get_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        return true;
    }
}

// Khởi tạo các biến cho phần hiển thị HTML nếu người dùng chưa đăng nhập hoặc không có dữ liệu
$user_avatar = '/images/avatar/0.png';
$display_player_name = 'Khách';
$user_vnd = 0;

if ($is_logged_in) {
    $userId = $_SESSION['user_id'];
    $stmt_user_info = $conn->prepare("SELECT username FROM account WHERE id = ?");
    if ($stmt_user_info) {
        $stmt_user_info->bind_param("i", $userId);
        $stmt_user_info->execute();
        $result_user_info = $stmt_user_info->get_result();
        if ($result_user_info->num_rows > 0) {
            $user_info = $result_user_info->fetch_assoc();
            $display_player_name = htmlspecialchars($user_info['username']);
        }
        $stmt_user_info->close();
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu - Chú Bé Rồng Online - Ngọc Rồng Online</title>
    <meta name="keywords" content="Chú Bé Rồng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong" />
    <meta name="description" content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngoc Rong Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!" />
    <meta http-equiv="refresh" content="600" />
    <meta name="robots" content="INDEX,FOLLOW" />

    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="shortcut icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/view/static/css/template.css?v=1.10">
    <link rel="stylesheet" href="/view/static/css/eff.css?v=1.00">
    <link rel="stylesheet" href="/view/static/css/w3.css?v=1.01">
    <link rel="stylesheet" href="/view/static/css/forum.css?v=1.01">
    <link rel="stylesheet" href="/view/static/css/styleSheet.css?v=1.1">
    <link rel="stylesheet" href="/view/static/css/napvang.css">
    <script src="/view/static/js/disable_devtools.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        html, body {
            min-height: 100%;
        }

        body {
            background:
                radial-gradient(circle at top, rgba(255, 244, 183, .42), transparent 34rem),
                linear-gradient(180deg, #f3c16b 0%, #c86f31 42%, #7d3b24 100%);
            font-family: Arial, Helvetica, sans-serif;
        }

        .body_body {
            max-width: 560px;
            margin: 18px auto;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .div-12,
        .bg_top,
        .left_top,
        .bg_tree,
        .bg_noel,
        .foot_bg,
        .left_b_bottom,
        .right_b_bottom,
        .footer,
        #backTop {
            display: none !important;
        }

        .body-content {
            background: #ff7a1f;
            border: 3px solid #713418;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 16px 34px rgba(55, 21, 8, .35);
        }

        .bg-content2 {
            background: transparent !important;
        }

        .body-content .a {
            margin: 0;
            padding: 12px 0 6px;
            text-align: center;
            background: linear-gradient(180deg, #ff8c2b 0%, #ff6717 100%);
        }

        .body-content .a img {
            max-width: 88%;
            height: auto;
            max-height: 92px;
        }

        .link-more .h {
            background: #ffd078 !important;
        }

        .menu2 {
            background: #5f260b !important;
            padding: 4px;
        }

        .menu2 table {
            table-layout: fixed;
            margin: 0;
        }

        .menu td {
            height: 28px;
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 4px;
            overflow: hidden;
            background: rgba(110, 45, 12, .75);
            text-align: center;
        }

        .menu td a {
            display: block;
            padding: 6px 0;
            color: #fff !important;
            font-weight: 700;
            text-decoration: none;
        }

        .menu td#selected {
            background: #ffcf6b;
        }

        .menu td#selected a {
            color: #4b210c !important;
        }

        .body {
            background: linear-gradient(180deg, #ffd487 0%, #ffb55d 100%) !important;
            padding: 16px 12px 20px;
            color: #49210e;
            text-align: center;
        }

        .box_inputboxx {
            display: flex;
            justify-content: center;
        }

        #user-info {
            display: inline-block;
            width: min(100%, 360px);
            background: rgba(255,255,255,.42);
            border: 1px solid rgba(104,49,17,.28);
            border-radius: 8px;
            padding: 12px;
            box-sizing: border-box;
            color: #49210e !important;
        }

        #user-info .user-avatar {
            width: 54px;
            height: 54px;
            border-radius: 6px;
            border: 1px solid rgba(104,49,17,.35);
            background: #fffaf1;
            object-fit: cover;
        }

        #user-info .user-details {
            color: #49210e;
            font-weight: 700;
            line-height: 1.6;
        }

        #user-info .user-details span {
            display: block;
            color: #49210e !important;
        }

        #user-info .user-details a,
        #changepass a {
            color: #8a1d12 !important;
            font-weight: 700;
        }

        .menu1 td {
            border-radius: 0;
            color: #fff;
            font-weight: 700;
            padding: 5px 0;
        }

        #box_forums,
        .box_list_chuyenmuc,
        .box_midss,
        .box_detai {
            background: transparent !important;
            border: 0 !important;
        }

        .change-pass-wrap {
            padding: 12px 8px 18px;
            text-align: center;
        }

        .change-pass-note {
            color: #49210e;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        #changepass {
            display: inline-block;
            width: min(100%, 360px);
            background: rgba(255,255,255,.42);
            border: 1px solid rgba(104,49,17,.28);
            border-radius: 8px;
            padding: 12px;
            box-sizing: border-box;
        }

        #changepass table {
            width: 100%;
            margin: 8px 0;
        }

        #changepass td {
            padding: 5px 3px;
            text-align: left;
        }

        #changepass label,
        #changepass b {
            color: #49210e;
            font-weight: 700;
            white-space: nowrap;
        }

        #changepass input[type="password"] {
            width: 100%;
            min-height: 30px;
            border: 1px solid #a75e22;
            border-radius: 5px;
            padding: 5px 7px;
            box-sizing: border-box;
            background: #fffaf1;
        }

        #button1 {
            width: 100%;
            margin-top: 8px;
            border-radius: 5px;
            border: 1px solid #8a1d12;
            background: linear-gradient(180deg, #e84a35 0%, #b7281d 100%) !important;
            font-weight: 700;
        }

        #comment_error.message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-weight: 700;
            line-height: 1.4;
            text-align: left;
        }

        #comment_error.message:empty {
            display: none;
        }

        #comment_error.message:not(:empty) {
            background: rgba(255,255,255,.45);
            border: 1px solid currentColor;
        }

        #comment_error.message.success {
            display: block;
            color: #155724 !important;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        #comment_error.message.error {
            display: block;
            color: #721c24 !important;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 420px) {
            #changepass td {
                display: block;
                width: 100%;
            }

            #changepass tr {
                display: block;
                margin-bottom: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
        <a href="#" id="backTop"><img id='backTopimg' src='/images/favicon-32x32.png' alt='top' /> </a>

        <div class="div-12">
            <img height=12 src="/images/12.png" style="vertical-align: middle;" />
            <span style="vertical-align: middle;">Dành cho người chơi trên 12 tuổi. Chơi quá 180 phút mỗi ngày sẽ hại sức khỏe.</span>
        </div>
        <div class="left_top"></div>
        <div class="bg_top">
            <div class="right_top"></div>
        </div>
        <div class="body-content">
            <div class="bg-content2">
                <h1 class="a">
                    <a href="/" title="game bảy viên Chú Bé Rồng Online">
                        <img height=90 src="/images/logo_sk_he.png" alt="game bảy viên Chú Bé Rồng Online" />
                    </a>
                </h1>
                <div id="top">
                    <div class="link-more">
                        <div class="h">
                            <div class="bg_noel"></div>
                            <div class="h">
                                <div class="menu2">
                                    <table width="100%" cellspacing="4">
                                        <tr class="menu">
                                            <td><a href="/Trang-Chu">Trang Chủ</a></td>
                                            <td><a href="/Gioi-Thieu">Giới Thiệu</a></td>
                                            <td><a href="/forum" title="Diễn Đàn">Diễn Đàn</a></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var currentUrl = window.location.pathname;
                                    document.querySelectorAll(".menu a").forEach(function(link) {
                                        if (link.getAttribute("href") === currentUrl) {
                                            document.querySelector("#selected")?.removeAttribute("id");
                                            link.parentElement.id = "selected";
                                        }
                                    });
                                });
                            </script>

                            <div class="body">
                                <div class="box_inputboxx" style="width:100%">
                                    <?php if ($is_logged_in): ?>
                                        <div id="user-info" style="color:white; text-align:center;">
                                            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
                                            <div class="user-details"> <br>
                                                <span>Xin chào: <?php echo htmlspecialchars($display_player_name); ?></span>
                                                <a href="/app/doi-mat-khau">Đổi mật khẩu</a> <br>
                                                <a href="/app/logout">Đăng xuất</a> <br>

                                                <div class="center-buttons">
                                                    <div style="display: flex; justify-content: center; align-items: center; margin-top: 5px;">
                                                        <!-- <a href="/app/nap-vang" style="color: cyan; transform: translateX(-21px); display: inline-block;">Nạp Vàng</a>
                                                        <a href="/app/nap-ngoc" style="color: cyan; margin-left: 0px;">Nạp Tiền</a> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="box_button_login" style="width:100%; position: relative; text-align:center;">
                                            <a id="tab-login-btn" href="/app/login.php">
                                                <button class="w3-button w3-red w3-small w3-hover-green">Đăng nhập</button>
                                            </a>
                                            <a id="tab-register-btn" href="/app/register.php">
                                                <button class="w3-button w3-blue w3-small w3-hover-green">Đăng ký</button>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table width="100%" border="0" cellspacing="0">
                        <tbody>
                            <tr class="menu1">
                                <td id="selected" style="width:50%; background-color: #ff5601;">Đổi mật khẩu</td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="box_forums">
                        <div class="box_list_chuyenmuc">
                            <div class="box_midss">
                                <div class="box_detai">
                                    <div class="change-pass-wrap">
                                        <div class="change-pass-note">Sử dụng mật khẩu hiện tại để đổi sang mật khẩu mới.</div>
                                        <form method="post" name="changepass" id="changepass">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td><b>Mật khẩu hiện tại</b></td>
                                                        <td colspan="2">
                                                            <input type="password" name="currentPassword" id="currentPassword" value="" required autocomplete="off">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Mật khẩu mới</b></td>
                                                        <td colspan="2">
                                                            <input type="password" name="newPassword" id="newPassword" value="" required autocomplete="off">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Nhập lại mật khẩu mới</b></td>
                                                        <td colspan="2">
                                                            <input type="password" name="confirmPassword" id="confirmPassword" value="" required autocomplete="off">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div id="comment_error" class="message"></div>
                                            <button type="submit" class="w3-button w3-red" name="button1" id="button1">Đổi Mật Khẩu</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <br>
        <div class="bg_tree"></div>
        <div class="foot_bg"></div>
        <div class="left_b_bottom">
            <div class="right_b_bottom">
                <div class="footer">
                    <div class="left_bottom"></div>
                    <div class="right_bottom"></div>
                </div>
            </div>
        </div>
        <div class="copyright" style="line-height: 7px">
            <p>Giấy phép thiết lập Mạng Xã Hội trên mạng số: 374/GP-BTTTT <br><br>do Bộ Thông Tin và Truyền Thông cấp ngày: 07/08/2015</p>
            Bản Quyền thuộc về Gomobi
        </div>
    </div>
    <script src="/view/static/js/ThreeCanvas.js" type="text/javascript"></script>
    <script src="/view/static/js/Snow3d.js" type="text/javascript"></script>
    <script src="/view/static/js/animation.js?v5" type="text/javascript"></script>
    <script src="/view/static/js/rocket-loader.min.js" data-cf-settings="3248e74b3f0d3f240922716b-|49" defer></script>

    <script>
        $(document).ready(function() {
            var lastPostTime = 0;
            $("form[name='changepass']").submit(function(event) {
                event.preventDefault();
                console.log("Form đổi mật khẩu đã được gửi.");

                var now = Date.now();
                if (now - lastPostTime < 10000) {
                    var secondsLeft = Math.ceil((10000 - (now - lastPostTime)) / 1000);
                    $("#comment_error").css("color", "red").text("Bạn chỉ có thể gửi mỗi 10 giây. Vui lòng chờ " + secondsLeft + " giây.");
                    console.log("Đạt giới hạn thời gian gửi. Số giây còn lại:", secondsLeft);
                    return;
                }

                var form = $(this);
                var action = form.attr("id"); // Đây sẽ là 'changepass'
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();

                if (newPassword.length < 6) {
                    $("#comment_error").css("color", "red").text("Mật khẩu mới phải có ít nhất 6 ký tự.");
                    return;
                }
                if (newPassword !== confirmPassword) {
                    $("#comment_error").css("color", "red").text("Mật khẩu mới và xác nhận mật khẩu không khớp.");
                    return;
                }

                var formData = form.serialize();
                formData += '&action=' + action;

                console.log("Đang gửi AJAX đến:", "/app/doi-mat-khau.php");
                console.log("Dữ liệu FormData đang được gửi:", formData);

                $.post("/app/doi-mat-khau.php", formData)
                    .done(function(response) {
                        console.log("AJAX thành công. Phản hồi thô:", response);
                        try {
                            // jQuery .post() với header Content-Type: application/json tự động parse JSON.
                            // Nếu response là string, hãy thử parse. Nếu không, sử dụng trực tiếp.
                            var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                            console.log("AJAX thành công. Phản hồi đã phân tích:", parsedResponse);

                            if (parsedResponse.success === true) { // Chỉ kiểm tra `success`
                                $("#comment_error").css("color", "green").text(parsedResponse.message || "Đổi mật khẩu thành công!");
                                lastPostTime = Date.now();
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                $("#comment_error").css("color", "red").text(parsedResponse.message || "Có lỗi xảy ra khi đổi mật khẩu. Vui lòng thử lại.");
                            }
                        } catch (e) {
                            console.error("Lỗi phân tích phản hồi JSON:", e, "Phản hồi thô:", response);
                            $("#comment_error").css("color", "red").text("Lỗi xử lý phản hồi từ máy chủ. Vui lòng thử lại.");
                        }
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX thất bại. Trạng thái:", textStatus, "Lỗi:", errorThrown, "jqXHR:", jqXHR);
                        console.error("Văn bản phản hồi từ máy chủ:", jqXHR.responseText);
                        var errorMessage = (jqXHR.responseJSON && jqXHR.responseJSON.message) || "Có lỗi xảy ra. Vui lòng thử lại trong ít phút nữa.";
                        $("#comment_error").css("color", "red").text(errorMessage);
                    });
            });
        });
    </script>
    <script>
        function show_pass(id) {
            var inputId = '';
            var iconSpanId = '';

            // Gán giá trị cho inputId và iconSpanId dựa trên id
            if (id === 1) {
                inputId = '#currentPassword';
                iconSpanId = '#show-pass';
            } else if (id === 2) {
                inputId = '#newPassword';
                iconSpanId = '#show-npass';
            } else if (id === 3) {
                inputId = '#confirmPassword';
                iconSpanId = '#show-repass';
            } else {
                return; // Thoát nếu id không hợp lệ
            }

            var input = $(inputId);
            input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');

            // Cập nhật icon
            var icon = $(iconSpanId).find('i');
            if (input.attr('type') === 'password') {
                icon.removeClass('bx-show').addClass('bx-hide');
            } else {
                icon.removeClass('bx-hide').addClass('bx-show');
            }
        }
    </script>
</body>
</html>
