<?php
require_once 'connect.php';
require_once 'task_data_config.php';

$players_task_data = [];
$error_message = '';

if (!isset($conn) || $conn->connect_error) {
    $error_message = 'Kh&#244;ng th&#7875; k&#7871;t n&#7889;i &#273;&#7871;n c&#417; s&#7903; d&#7919; li&#7879;u. Vui l&#242;ng th&#7917; l&#7841;i sau!';
} else {
    $query = "SELECT name, data_task FROM player ORDER BY id ASC LIMIT 200";
    $result = $conn->query($query);

    if ($result === false) {
        $error_message = 'L&#7895;i truy v&#7845;n SQL: ' . htmlspecialchars($conn->error);
    } else if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $task_data = json_decode($row['data_task'], true);
            $current_task_id_for_sort = 0;
            $task_display_text = "Kh&#244;ng c&#243; d&#7919; li&#7879;u nhi&#7879;m v&#7909;";

            if (is_array($task_data) && json_last_error() === JSON_ERROR_NONE) {
                if (isset($task_data[0]) && is_numeric($task_data[0])) {
                    $current_task_id_for_sort = (int)$task_data[0];
                    if (isset($game_tasks[$current_task_id_for_sort])) {
                        $task_display_text = htmlspecialchars($game_tasks[$current_task_id_for_sort]['name']);
                    } else {
                        $task_display_text = "Nhi&#7879;m v&#7909; ID: " . $current_task_id_for_sort . " (Ch&#432;a c&#243; t&#234;n trong c&#7845;u h&#236;nh)";
                    }

                    if (isset($task_data[1]) && is_numeric($task_data[1]) && $task_data[1] > 0) {
                       $task_display_text .= " (Ti&#7871;n &#273;&#7897;: " . $task_data[1] . "%)";
                    }

                } else {
                    $task_id_found = false;
                    if (isset($task_data['current_task_id'])) {
                        $current_task_id_for_sort = (int)$task_data['current_task_id'];
                        $task_id_found = true;
                    } else if (isset($task_data['task_id'])) {
                        $current_task_id_for_sort = (int)$task_data['task_id'];
                        $task_id_found = true;
                    } else if (isset($task_data['currentTask']) && is_array($task_data['currentTask']) && isset($task_data['currentTask']['id'])) {
                        $current_task_id_for_sort = (int)$task_data['currentTask']['id'];
                        $task_id_found = true;
                    }
                    
                    if ($task_id_found && isset($game_tasks[$current_task_id_for_sort])) {
                        $task_info = $game_tasks[$current_task_id_for_sort];
                        $task_display_text = htmlspecialchars($task_info['name']);
                    } else if ($task_id_found) {
                        $task_display_text = "Nhi&#7879;m v&#7909; ID: " . $current_task_id_for_sort . " (Ch&#432;a c&#243; t&#234;n trong c&#7845;u h&#236;nh)";
                    }

                    if (isset($task_data['name']) && !empty($task_data['name'])) {
                        $task_display_text = htmlspecialchars($task_data['name']);
                    } else if (isset($task_data['description']) && !empty($task_data['description'])) {
                        $task_display_text = htmlspecialchars($task_data['description']);
                    } else if (isset($task_data['message']) && !empty($task_data['message'])) {
                        $task_display_text = htmlspecialchars($task_data['message']);
                    }

                    if (isset($task_data['progress']) && is_numeric($task_data['progress'])) {
                        $task_display_text .= " (Ti&#7871;n &#273;&#7897;: " . $task_data['progress'] . "%)";
                    }
                }
            } 
            
            $players_task_data[] = [
                'name' => htmlspecialchars($row['name']),
                'task_id_for_sort' => $current_task_id_for_sort,
                'task_display' => $task_display_text
            ];
        }

        usort($players_task_data, function($a, $b) {
            return $b['task_id_for_sort'] <=> $a['task_id_for_sort'];
        });

        $players_task_data = array_slice($players_task_data, 0, 50);

    } else {
        $error_message = 'Hi&#7879;n t&#7841;i ch&#432;a c&#243; ng&#432;&#7901;i ch&#417;i n&#224;o &#273;&#7875; hi&#7875;n th&#7883; b&#7843;ng x&#7871;p h&#7841;ng nhi&#7879;m v&#7909;.';
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>B&#7843;ng X&#7871;p H&#7841;ng Nhi&#7879;m V&#7909; - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <meta name="description" content="Xem báº£ng xáº¿p háº¡ng nhiá»‡m vá»¥ cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta name="keywords" content="top nhiá»‡m vá»¥, chĂº bĂ© rá»“ng online, ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="author" content="Mr Blue">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Báº£ng Xáº¿p Háº¡ng Nhiá»‡m Vá»¥ - ChĂº BĂ© Rá»“ng Online">
    <meta property="og:description" content="Xem báº£ng xáº¿p háº¡ng nhiá»‡m vá»¥ cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta property="og:image" content="/image/logo.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:title" content="Báº£ng Xáº¿p Háº¡ng Nhiá»‡m Vá»¥ - ChĂº BĂ© Rá»“ng Online">
    <meta name="twitter:description" content="Xem báº£ng xáº¿p háº¡ng nhiá»‡m vá»¥ cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta name="twitter:image" content="/image/logo.png">


    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/main.css" rel="stylesheet">

    <script src="/view/static/js/disable_devtools.js"></script>
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/notify/notify.js"></script>

    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(255, 213, 119, .45), transparent 28rem), linear-gradient(180deg, #7a341b 0%, #d98237 42%, #f1c06c 100%);
            color: #3b1b0c;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
        }
        html { overflow-y: scroll; }
        .ranking-shell {
            max-width: 630px;
            margin: 14px auto 18px;
            padding: 0 8px;
        }
        .ranking-logo {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ranking-logo img {
            max-width: 100%;
            height: 70px;
            object-fit: contain;
        }
        .ranking-nav {
            background: #ff5601;
            border-radius: 12px 12px 0 0;
            padding: 6px 6px 4px;
            box-shadow: 0 8px 20px rgba(48, 18, 7, .18);
        }
        .ranking-nav table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin: 0;
        }
        .ranking-nav td {
            width: 33.333%;
            text-align: center;
            background: linear-gradient(180deg, #f8d56a 0%, #d98615 100%);
            border: 1px solid #8d3a08;
            border-radius: 5px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .35);
        }
        .ranking-nav a {
            display: block;
            padding: 7px 4px;
            color: #7b1d00 !important;
            font-size: 9pt;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }
        .mission-topbar {
            max-width: 630px;
            margin: 14px auto 0;
            background: rgba(71, 30, 10, .9);
            border: 1px solid rgba(255, 221, 150, .35);
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(48, 18, 7, .24);
        }
        .mission-topbar a { color: #fff3c4 !important; font-weight: 700; text-decoration: none; }
        .mission-card {
            max-width: 630px;
            margin: 12px auto 18px;
            padding: 0;
            background: #fff6df;
            border: 3px solid #6b3016;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 34px rgba(49, 20, 8, .3);
        }
        .mission-card > .row, .mission-card > .row > .col { margin: 0; padding: 0; }
        .mission-hero {
            padding: 18px 16px 14px;
            background: linear-gradient(135deg, #561d00 0%, #a14418 52%, #f0a33b 100%);
            color: #fff7d5;
            text-align: center;
        }
        .mission-hero h1 { margin: 0; font-size: 16px; line-height: 1.25; font-weight: 800; }
        .mission-hero p { margin: 6px 0 0; font-size: 9pt; opacity: .92; }
        .mission-table-wrap { padding: 12px; overflow-x: hidden; }
        #mission-table {
            width: 100%;
            min-width: 0;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0 7px;
            table-layout: fixed;
        }
        #mission-table thead th {
            background: #5b250d;
            color: #fff1bd;
            border: 0 !important;
            padding: 9px 10px !important;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        #mission-table tbody tr { background: #fffaf0; box-shadow: 0 1px 0 rgba(104, 48, 18, .14), 0 2px 8px rgba(104, 48, 18, .08); }
        #mission-table tbody tr:nth-of-type(odd) { background: #fff1d3; }
        #mission-table th, #mission-table td {
            border: 0 !important;
            padding: 8px 6px !important;
            font-size: 9pt;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        #mission-table th:first-child, #mission-table td:first-child { width: 44px; text-align: center; white-space: normal; }
        #mission-table th:nth-child(2), #mission-table td:nth-child(2) { width: 24%; white-space: normal; font-weight: 700; }
        #mission-table td:nth-child(3) { text-align: left; min-width: 0; }
        #mission-table tbody td:first-child, #mission-table thead th:first-child { border-radius: 7px 0 0 7px; }
        #mission-table tbody td:last-child, #mission-table thead th:last-child { border-radius: 0 7px 7px 0; }
        .rank-badge {
            display: inline-flex;
            width: 28px;
            height: 28px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #7a3a1b;
            color: #fff;
            font-weight: 800;
            box-shadow: inset 0 -2px 0 rgba(0,0,0,.18);
        }
        .top_1 .rank-badge { background: linear-gradient(180deg, #ffe88c, #d69516); color: #4a2600; }
        .top_2 .rank-badge { background: linear-gradient(180deg, #f5f7ff, #9ca8b8); color: #243044; }
        .top_3 .rank-badge { background: linear-gradient(180deg, #ffc58a, #b96521); color: #3f1e09; }
        .top_1 td, .top_2 td, .top_3 td { font-weight: 700; }
        .mission-updated { padding: 0 14px 14px; color: #6b3016; }
        .mission-empty { padding: 18px !important; color: #7a3a1b; font-weight: 700; text-align: center !important; }
        @media (max-width: 576px) {
            .ranking-shell { margin-top: 8px; padding: 0; }
            .ranking-logo { height: 70px; }
            .ranking-logo img { height: 70px; }
            .ranking-nav a { font-size: 9pt; padding: 7px 2px; }
            .mission-topbar, .mission-card { margin-left: 8px; margin-right: 8px; }
            .mission-hero h1 { font-size: 15px; }
            .mission-table-wrap { padding: 8px; }
        }
    </style>
</head>

<body>
    <div class="ranking-shell">
        <div class="ranking-logo">
            <a href="/trang-chu"><img src="/images/logo_sk_he.png" alt="Ch&#250; B&#233; R&#7891;ng Online"></a>
        </div>
        <div class="ranking-nav">
            <table>
                <tr>
                    <td><a href="/trang-chu">Trang Ch&#7911;</a></td>
                    <td><a href="/gioi-thieu">Gi&#7899;i Thi&#7879;u</a></td>
                    <td><a href="/forum">Di&#7877;n &#272;&#224;n</a></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="container color-forum pt-2 mission-card">
        <div class="row">
            <div class="col">
                <div class="mission-hero"><h1>B&#7842;NG X&#7870;P H&#7840;NG NHI&#7878;M V&#7908;</h1><p>Top 50 nh&#226;n v&#7853;t c&#243; ti&#7871;n &#273;&#7897; nhi&#7879;m v&#7909; cao nh&#7845;t</p></div>
                <div class="mission-table-wrap">
                    <table class="table text-center" id="mission-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Nh&#226;n V&#7853;t</th>
                                <th>Nhi&#7879;m V&#7909; Hi&#7879;n T&#7841;i</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($error_message)): ?>
                                <tr><td colspan="3" class="mission-empty"><?php echo $error_message; ?></td></tr>
                            <?php elseif (empty($players_task_data)): ?>
                                <tr><td colspan="3" class="mission-empty">Hi&#7879;n t&#7841;i ch&#432;a c&#243; d&#7919; li&#7879;u nhi&#7879;m v&#7909; h&#7907;p l&#7879; &#273;&#7875; x&#7871;p h&#7841;ng.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; ?>
                                <?php foreach ($players_task_data as $player): ?>
                                    <tr class="top_<?php echo $stt; ?>">
                                        <td><span class="rank-badge"><?php echo $stt++; ?></span></td>
                                        <td><?php echo $player['name']; ?></td>
                                        <td><?php echo $player['task_display']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-right w-100 mt-3 mission-updated">
                    <small>C&#7853;p nh&#7853;t l&#250;c:
                        <?php echo date('H:i d/m/Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</body>
</html>

