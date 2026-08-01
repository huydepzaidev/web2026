<?php
// head.php
// File nĂ y CHá»ˆ chá»©a ná»™i dung bĂªn trong tháº» <head> vĂ  logic PHP liĂªn quan
// Äáº£m báº£o session Ä‘Ă£ Ä‘Æ°á»£c báº¯t Ä‘áº§u á»Ÿ Ä‘áº§u file chĂ­nh (vd: index.php, bai-viet.php)
// vĂ  $conn Ä‘Ă£ Ä‘Æ°á»£c thiáº¿t láº­p (tá»« connect.php) trÆ°á»›c khi file nĂ y Ä‘Æ°á»£c include.

// Khá»Ÿi táº¡o cĂ¡c biáº¿n máº·c Ä‘á»‹nh
$_is_logged_in = false;
$_username = '';
$_user_avatar = '/images/avatar/default_avatar.png'; // ÄÆ°á»ng dáº«n avatar máº·c Ä‘á»‹nh
$_coin = 0; // Sá»‘ dÆ° máº·c Ä‘á»‹nh
$_danh_hieu_logged_in = "";
$_color_logged_in = "";
$_admin_logged_in = 0;
$_name_str_logged_in = ''; // Biáº¿n Ä‘á»ƒ chá»©a chuá»—i HTML tĂªn vĂ  danh hiá»‡u

$logged_in_player_name = null; // TĂªn nhĂ¢n váº­t (dĂ¹ng cho bĂ¬nh luáº­n, admin logic)
$logged_in_player_gender = 0; // Giá»›i tĂ­nh cá»§a nhĂ¢n váº­t
$is_admin = false; // Tráº¡ng thĂ¡i admin cá»§a tĂ i khoáº£n

if (isset($_SESSION['username']) && isset($conn)) { // Äáº£m báº£o $conn Ä‘Ă£ cĂ³
    $_username = $_SESSION['username']; // ÄĂ¢y lĂ  username tĂ i khoáº£n (vd: "admin", "user123")

    // Truy váº¥n Ä‘á»ƒ láº¥y tĂªn nhĂ¢n váº­t (player.name), giá»›i tĂ­nh (player.gender)
    // Giáº£ Ä‘á»‹nh má»—i tĂ i khoáº£n cĂ³ má»™t nhĂ¢n váº­t chĂ­nh hoáº·c báº¡n muá»‘n láº¥y báº¥t ká»³ nhĂ¢n váº­t nĂ o liĂªn káº¿t.
    // Náº¿u posts.username lĂ  tĂªn nhĂ¢n váº­t, báº¡n cáº§n Ä‘iá»u chá»‰nh truy váº¥n JOIN trong post_detail_logic.php
    // Current query assumes posts.username is account.username
    $sql_user_data = "
        SELECT
            p.name AS player_name,
            p.gender,
            p.head, -- Láº¥y cá»™t head tá»« báº£ng player
            a.tichdiem,
            a.vnd AS coin,
            a.is_admin
        FROM
            account a
        LEFT JOIN
            player p ON a.id = p.account_id
        WHERE
            a.username = ?
        LIMIT 1 -- Láº¥y 1 nhĂ¢n váº­t náº¿u cĂ³ nhiá»u
    ";
    $stmt_user_data = $conn->prepare($sql_user_data);

    if ($stmt_user_data) {
        $stmt_user_data->bind_param("s", $_username);
        $stmt_user_data->execute();
        $result_user_data = $stmt_user_data->get_result();
        $user_row = $result_user_data->fetch_assoc();

        if ($user_row) {
            $_is_logged_in = true;
            $logged_in_player_gender = $user_row['gender'] ?? 0; // Giá»›i tĂ­nh cá»§a nhĂ¢n váº­t
            $tichdiem_logged_in = $user_row['tichdiem'] ?? 0;
            $_coin = $user_row['coin'] ?? 0;
            $_admin_logged_in = (int) ($user_row['is_admin'] ?? 0);
            $logged_in_player_name = $user_row['player_name'] ?? $_username; // Sá»­ dá»¥ng tĂªn nhĂ¢n váº­t náº¿u cĂ³, khĂ´ng thĂ¬ dĂ¹ng username

            // XĂ¡c Ä‘á»‹nh avatar dá»±a trĂªn head vĂ  admin
            $player_head = $user_row['head'] ?? 0; // Láº¥y giĂ¡ trá»‹ head
            if ($_admin_logged_in == 1) {
                // Admin avatar (cĂ³ thá»ƒ tĂ¹y chá»‰nh theo head, hoáº·c dĂ¹ng avatar cá»‘ Ä‘á»‹nh cho admin)
                // Náº¿u báº¡n muá»‘n admin cĂ³ avatar riĂªng khĂ´ng phá»¥ thuá»™c head, giá»¯ nguyĂªn cĂ¡c dĂ²ng dÆ°á»›i.
                // Náº¿u admin avatar phá»¥ thuá»™c head, báº¡n cáº§n logic phá»©c táº¡p hÆ¡n á»Ÿ Ä‘Ă¢y.
                if ($logged_in_player_gender == 1) {
                    $_user_avatar = "/images/avatar/avatar10.png"; // Admin nam
                } elseif ($logged_in_player_gender == 2) {
                    $_user_avatar = "/images/avatar/avatar11.png"; // Admin ná»¯
                } else {
                    $_user_avatar = "/images/avatar/avatar12.png"; // Admin khĂ¡c
                }
            } else {
                // Avatar ngÆ°á»i dĂ¹ng bĂ¬nh thÆ°á»ng dá»±a trĂªn head
                if ($player_head > 0) { // Äáº£m báº£o head cĂ³ giĂ¡ trá»‹ há»£p lá»‡
                    $_user_avatar = "/images/avatar/" . htmlspecialchars($player_head) . ".png"; // Hoáº·c .gif, tĂ¹y Ä‘á»‹nh dáº¡ng file avatar cá»§a báº¡n
                } else {
                    // Avatar máº·c Ä‘á»‹nh náº¿u head khĂ´ng há»£p lá»‡ hoáº·c khĂ´ng cĂ³
                    if ($logged_in_player_gender == 1) {
                        $_user_avatar = "/images/avatar/avatar1.png"; // User nam máº·c Ä‘á»‹nh
                    } elseif ($logged_in_player_gender == 2) {
                        $_user_avatar = "/images/avatar/avatar2.png"; // User ná»¯ máº·c Ä‘á»‹nh
                    } else {
                        $_user_avatar = "/images/avatar/avatar0.png"; // User khĂ¡c máº·c Ä‘á»‹nh
                    }
                }
            }

            // LÆ°u cĂ¡c biáº¿n quan trá»ng vĂ o SESSION Ä‘á»ƒ sá»­ dá»¥ng á»Ÿ cĂ¡c file khĂ¡c
            $_SESSION['player_name'] = $logged_in_player_name;
            $_SESSION['player_gender'] = $logged_in_player_gender;
            $_SESSION['is_admin'] = $_admin_logged_in;
            $_SESSION['user_avatar'] = $_user_avatar; // LÆ°u Ä‘Æ°á»ng dáº«n avatar Ä‘áº§y Ä‘á»§ vĂ o session
            $_SESSION['coin'] = $_coin;
            // ... (cĂ¡c biáº¿n session khĂ¡c báº¡n muá»‘n lÆ°u)

            // XĂ¡c Ä‘á»‹nh danh hiá»‡u vĂ  mĂ u sáº¯c
            if ($tichdiem_logged_in >= 500) { $_danh_hieu_logged_in = "(ChuyĂªn Gia)"; $_color_logged_in = "#800000"; }
            // ... (ThĂªm cĂ¡c cáº¥p danh hiá»‡u khĂ¡c cá»§a báº¡n á»Ÿ Ä‘Ă¢y) ...

            // Táº¡o chuá»—i HTML cho tĂªn ngÆ°á»i dĂ¹ng vĂ  danh hiá»‡u
            if ($_admin_logged_in == 1) {
                $_name_str_logged_in = '<span class="text-danger font-weight-bold">' . htmlspecialchars($logged_in_player_name) . '</span><br>';
                $_name_str_logged_in .= '<span class="text-danger pt-1 mb-0">(Admin)</span>';
            } else {
                $_name_str_logged_in = '<p class="text-main font-weight-bold pt-1 mb-0">' . htmlspecialchars($logged_in_player_name) . '</p>';
                if ($_danh_hieu_logged_in !== "") {
                    $_name_str_logged_in .= '<div style="font-size: 9px; padding-top: 5px"><span style="color:' . $_color_logged_in . ' !important">' . $_danh_hieu_logged_in . '</span></div>';
                }
            }

            $is_admin = ($_admin_logged_in == 1); // Biáº¿n boolean cho logic
        }
        $stmt_user_data->close();
    } else {
        error_log("Lá»—i chuáº©n bá»‹ truy váº¥n SQL trong head.php: " . $conn->error);
    }
} else {
    // Náº¿u khĂ´ng Ä‘Äƒng nháº­p hoáº·c $conn khĂ´ng cĂ³, cĂ¡c biáº¿n sáº½ giá»¯ giĂ¡ trá»‹ máº·c Ä‘á»‹nh.
    // Äáº£m báº£o logged_in_player_name vĂ  is_admin Ä‘Æ°á»£c set Ä‘á»ƒ trĂ¡nh lá»—i undefined
    $logged_in_player_name = null;
    $is_admin = false;
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="ChĂº BĂ© Rá»“ng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong" />
    <meta name="description" content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngá»c Rá»“ng Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!" />
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/view/static/css/template.css?v=1.10">
    <link rel="stylesheet" href="/view/static/css/eff.css?v=1.00">
    <link rel="stylesheet" href="/view/static/css/w3.css?v=1.01">
    <link rel="stylesheet" href="/view/static/css/styleSheet.css?v=1.1">
    <script src="https://www.google.com/recaptcha/api.js?render="></script>

