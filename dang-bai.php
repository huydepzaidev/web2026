<?php
session_start();
require_once __DIR__ . '/base_path.php';
header('Location: ' . webgoc_url('forum'));
exit();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'settings.php';
require_once 'set.php';
require_once 'connect.php';

if ($_login == null) {
    header("location:login");
    exit();
}

$_alert = '';
if (isset($_SESSION['alert_message'])) {
    $_alert = $_SESSION['alert_message'];
    unset($_SESSION['alert_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tieude = htmlspecialchars($_POST["tieude"]);
    $noidung = htmlspecialchars($_POST["noidung"]);

    if (strlen($tieude) < 5 || strlen(trim(strip_tags($noidung))) < 5) {
        $_alert = "<div class='alert alert-danger'>TiĂªu Ä‘á» vĂ  ná»™i dung pháº£i cĂ³ Ă­t nháº¥t 5 kĂ½ tá»±!</div>";
    } else {
        if (!isset($_username)) {
            $_alert = "<div class='alert alert-danger'>Lá»—i: KhĂ´ng thá»ƒ xĂ¡c Ä‘á»‹nh tĂªn ngÆ°á»i dĂ¹ng. Vui lĂ²ng Ä‘Äƒng nháº­p láº¡i.</div>";
        } else {
            $stmt_player_name = $conn->prepare("SELECT p.name FROM player p JOIN account a ON a.id = p.account_id WHERE a.username = ?");
            if (!$stmt_player_name) {
                $_alert = "<div class='alert alert-danger'>Lá»—i chuáº©n bá»‹ cĂ¢u lá»‡nh SQL láº¥y tĂªn ngÆ°á»i chÆ¡i: " . $conn->error . "</div>";
            } else {
                $stmt_player_name->bind_param("s", $_username);
                $stmt_player_name->execute();
                $result_player_name = $stmt_player_name->get_result();
                $row_player_name = $result_player_name->fetch_assoc();
                $_name = $row_player_name['name'] ?? 'Guest';

                $stmt_player_name->close();

                $stmt_insert_post = $conn->prepare("INSERT INTO posts (tieude, noidung, username) VALUES (?, ?, ?)");
                if (!$stmt_insert_post) {
                    $_alert = "<div class='alert alert-danger'>Lá»—i chuáº©n bá»‹ cĂ¢u lá»‡nh SQL Ä‘Äƒng bĂ i: " . $conn->error . "</div>";
                } else {
                    $stmt_insert_post->bind_param("sss", $tieude, $noidung, $_name);

                    if ($stmt_insert_post->execute()) {
                        $stmt_update_tichdiem = $conn->prepare("UPDATE account SET tichdiem = tichdiem + 1 WHERE username = ?");
                        if (!$stmt_update_tichdiem) {
                            $_alert = "<div class='alert alert-danger'>Lá»—i chuáº©n bá»‹ cĂ¢u lá»‡nh SQL cáº­p nháº­t tĂ­ch Ä‘iá»ƒm: " . $conn->error . "</div>";
                        } else {
                            $stmt_update_tichdiem->bind_param("s", $_username);
                            $stmt_update_tichdiem->execute();
                            $stmt_update_tichdiem->close();
                        }
                        
                        $_SESSION['alert_message'] = "<div class='alert alert-success'>BĂ i viáº¿t Ä‘Ă£ Ä‘Æ°á»£c Ä‘Äƒng thĂ nh cĂ´ng.</div>";
        header('Location: ' . webgoc_url('forum'));
                        exit();
                    } else {
                        $_alert = "<div class='alert alert-danger'>Lá»—i khi Ä‘Äƒng bĂ i viáº¿t: " . $stmt_insert_post->error . "</div>";
                    }
                    $stmt_insert_post->close();
                }
            }
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width,maximum-scale=1,user-scalable=no"/>
		<meta http-equiv="content-language" content="vi" />
        <title>&#272;&#259;ng B&#224;i Vi&#7871;t - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
		<meta name="keywords" content="ChĂº BĂ© Rá»“ng Online, Ngoc Rong Online, Ngá»c Rá»“ng Mobile" />
		<meta name="description" content="ÄÄƒng bĂ i viáº¿t má»›i" />
		<meta name="robots" content="NOINDEX,FOLLOW" />
        <script src="/view/static/js/disable_devtools.js"></script>
        <link rel="stylesheet" type="text/css" href="/app/wiew/css/StyleSheet.css" />
		<link rel="stylesheet" href="/app/wiew/css/template.css" />
        <link rel="stylesheet" href="/app/wiew/css/w3.css">
        <link rel="stylesheet" type="text/css" href="/view/static/css/eff.css" />
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
                                        <td><a href="">Trang Chá»§</a></td>
                                        <td id="selected"><a href="/forum">Diá»…n ÄĂ n</a></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="body" style="text-align:center">
                                <div style="font-size:10px;">Äiá»n thĂ´ng tin bĂ i viáº¿t cá»§a báº¡n.</div>
                                <center>
                                    <form id="postForm" method="POST" action="">
                                        <table>
                                            <tr>
                                                <td colspan=2><label for="tieude">TiĂªu Ä‘á»:</label></td>
                                                <td colspan=2><input name="tieude" type="text" value="" required style="width: 100%; padding: 5px; box-sizing: border-box;" /></td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><label for="noidung">Ná»™i dung:</label></td>
                                                <td colspan=2>
                                                    <textarea name="noidung" rows="8" required style="width: 100%; padding: 5px; box-sizing: border-box;"></textarea>
                                                </td>
                                            </tr>
                                        </table>
                                        <div id="postMessage" class="message" style="display:none;"></div>
                                        <?php if (!empty($_alert)): ?>
                                            <div class="message <?php echo strpos($_alert, 'alert-success') !== false ? 'success' : 'error'; ?>" style="display:block;">
                                                <?php echo strip_tags($_alert); ?>
                                            </div>
                                        <?php endif; ?>
                                        <button type="submit" class="w3-button w3-red" value="ÄÄƒng bĂ i" id="button1" name="submit">ÄÄƒng BĂ i</button><br />
                                        <div style="font-size:10px;">
                                            <a href="/Forum">Quay láº¡i Diá»…n ÄĂ n</a>
                                        </div>
                                    </form><br>
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
            <div class="copyright"><br><b>Báº£n quyá»n thuá»™c vá» ChĂº BĂ© Rá»“ng Online - 2013</b></div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                var initialAlert = $('#postMessage');
                if (initialAlert.text().trim() !== '') {
                    initialAlert.css('display', 'block');
                }

                $('#postForm').submit(function(e) {
                });
            });
        </script>
        <script src="https://ngocrongonline.com/view/static/js/ThreeCanvas.js"></script>
        <script src="https://ngocrongonline.com/view/static/js/Snow3d.js"></script>
        <script src="https://ngocrongonline.com/view/static/js/animation.js?v4"></script>
        </body>
</html>

