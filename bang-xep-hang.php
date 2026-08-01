<?php
// bang-xep-hang.php
include_once 'set.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>B&#7843;ng X&#7871;p H&#7841;ng S&#7913;c M&#7841;nh - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <meta name="description" content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="keywords" content="ChĂº BĂ© Rá»“ng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="author" content="">
    <base href="/">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="twitter:description" content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="twitter:image" content="/image/logo.png">
    <meta name="twitter:image:width" content="200">
    <meta name="twitter:image:height" content="200">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/notify/notify.js"></script>
    <link href="assets/main.css" rel="stylesheet">
	<script src="/view/static/js/disable_devtools.js"></script>
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
        .bxh-topbar {
            max-width: 630px;
            margin: 14px auto 0;
            background: rgba(71, 30, 10, .9);
            border: 1px solid rgba(255, 221, 150, .35);
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(48, 18, 7, .24);
        }
        .bxh-topbar a { color: #fff3c4 !important; font-weight: 700; text-decoration: none; }
        .bxh-card {
            max-width: 630px;
            margin: 12px auto 18px;
            padding: 0;
            background: #fff6df;
            border: 3px solid #6b3016;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 34px rgba(49, 20, 8, .3);
        }
        .bxh-card > .row, .bxh-card > .row > .col { margin: 0; padding: 0; }
        .bxh-hero {
            padding: 18px 16px 14px;
            background: linear-gradient(135deg, #561d00 0%, #a14418 52%, #f0a33b 100%);
            color: #fff7d5;
            text-align: center;
        }
        .bxh-hero h1 { margin: 0; font-size: 16px; line-height: 1.25; font-weight: 800; }
        .bxh-hero p { margin: 6px 0 0; font-size: 9pt; opacity: .92; }
        .bxh-table-wrap { padding: 12px; overflow-x: hidden; }
        #leaderboard-table {
            width: 100%;
            min-width: 0;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0 7px;
            table-layout: fixed;
        }
        #leaderboard-table thead th {
            background: #5b250d;
            color: #fff1bd;
            border: 0 !important;
            padding: 9px 10px !important;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        #leaderboard-table tbody tr { background: #fffaf0; box-shadow: 0 1px 0 rgba(104, 48, 18, .14), 0 2px 8px rgba(104, 48, 18, .08); }
        #leaderboard-table tbody tr:nth-of-type(odd) { background: #fff1d3; }
        #leaderboard-table th, #leaderboard-table td {
            white-space: normal;
            border: 0 !important;
            padding: 8px 6px !important;
            font-size: 9pt;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        #leaderboard-table th:nth-child(1), #leaderboard-table td:nth-child(1) { width: 44px; }
        #leaderboard-table th:nth-child(2), #leaderboard-table td:nth-child(2) { width: 18%; }
        #leaderboard-table th:nth-child(3), #leaderboard-table td:nth-child(3) { width: 18%; }
        #leaderboard-table th:nth-child(4), #leaderboard-table td:nth-child(4) { width: 16%; }
        #leaderboard-table th:nth-child(5), #leaderboard-table td:nth-child(5) { width: 16%; }
        #leaderboard-table th:nth-child(6), #leaderboard-table td:nth-child(6) { width: 18%; }
        #leaderboard-table tbody td:first-child, #leaderboard-table thead th:first-child { border-radius: 7px 0 0 7px; }
        #leaderboard-table tbody td:last-child, #leaderboard-table thead th:last-child { border-radius: 0 7px 7px 0; }
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
        .bxh-updated { padding: 0 14px 14px; color: #6b3016; }
        .bxh-empty { padding: 18px !important; color: #7a3a1b; font-weight: 700; }
        @media (max-width: 576px) {
            .ranking-shell { margin-top: 8px; padding: 0; }
            .ranking-logo { height: 70px; }
            .ranking-logo img { height: 70px; }
            .ranking-nav a { font-size: 9pt; padding: 7px 2px; }
            .bxh-topbar, .bxh-card { margin-left: 8px; margin-right: 8px; }
            .bxh-hero h1 { font-size: 15px; }
            .bxh-table-wrap { padding: 8px; }
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
    <div class="container color-forum pt-2 bxh-card">
        <div class="row">
            <div class="col">
                <div class="bxh-hero"><h1>B&#7842;NG X&#7870;P H&#7840;NG &#272;UA TOP</h1><p>Top 100 nh&#226;n v&#7853;t c&#243; t&#7893;ng s&#7913;c m&#7841;nh cao nh&#7845;t m&#225;y ch&#7911;</p></div><div class="bxh-table-wrap">
                <table class="table text-center" id="leaderboard-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Nh&#226;n v&#7853;t</th>
                            <th>S&#7913;c M&#7841;nh</th>
                            <th>&#272;&#7879; T&#7917;</th>
                            <th>H&#224;nh Tinh</th>
                            <th>T&#7893;ng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $countTop = 1;
                        $query = "
                            SELECT
                                name,
                                gender,
                                CAST(JSON_UNQUOTE(JSON_EXTRACT(data_point, '$[1]')) AS SIGNED) AS player_sm,
                                COALESCE(
                                    CAST(
                                        SUBSTRING_INDEX(
                                            SUBSTRING_INDEX(
                                                JSON_UNQUOTE(JSON_EXTRACT(pet, '$[1]')),
                                                ',', 2
                                            ),
                                            ',', -1
                                        ) AS SIGNED
                                    ), 0
                                ) AS detu_sm,
                                CAST(JSON_UNQUOTE(JSON_EXTRACT(data_point, '$[1]')) AS SIGNED) +
                                COALESCE(
                                    CAST(
                                        SUBSTRING_INDEX(
                                            SUBSTRING_INDEX(
                                                JSON_UNQUOTE(JSON_EXTRACT(pet, '$[1]')),
                                                ',', 2
                                            ),
                                            ',', -1
                                        ) AS SIGNED
                                    ), 0
                                ) AS tongdiem
                            FROM player
                            ORDER BY tongdiem DESC
                            LIMIT 100;
                        ";

                        $data = mysqli_query($conn, $query);

                        if ($data) {
                            if (mysqli_num_rows($data) > 0) {
                                while ($row = mysqli_fetch_array($data)) {
                        ?>
                                        <tr class="top_<?php echo $countTop; ?>">
                                            <td>
                                                <span class="rank-badge"><?php echo $countTop++; ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $value = $row['player_sm'];
                                                if ($value != '') {
                                                    if ($value > 1000000000) {
                                                        echo number_format($value / 1000000000, 1, '.', '') . ' t&#7927;';
                                                    } elseif ($value > 1000000) {
                                                        echo number_format($value / 1000000, 1, '.', '') . ' Tri&#7879;u';
                                                    } elseif ($value >= 1000) {
                                                        echo number_format($value / 1000, 1, '.', '') . ' k';
                                                    } else {
                                                        echo number_format($value, 0, ',', '');
                                                    }
                                                } else {
                                                    echo 'Kh&#244;ng c&#243; ch&#7881; s&#7889; s&#7913;c m&#7841;nh';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $value = $row['detu_sm'];
                                                if ($value != '' && $value > 0) {
                                                    if ($value > 1000000000) {
                                                        echo number_format($value / 1000000000, 1, '.', '') . ' t&#7927;';
                                                    } elseif ($value > 1000000) {
                                                        echo number_format($value / 1000000, 1, '.', '') . ' Tri&#7879;u';
                                                    } elseif ($value >= 1000) {
                                                        echo number_format($value / 1000, 1, '.', '') . ' k';
                                                    } else {
                                                        echo number_format($value, 0, ',', '');
                                                    }
                                                } else {
                                                    echo 'Kh&#244;ng &#273;&#7879; t&#7917;';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($row['gender'] == 0) {
                                                    echo "Tr&#225;i &#272;&#7845;t";
                                                } elseif ($row['gender'] == 1) {
                                                    echo "Namec";
                                                } elseif ($row['gender'] == 2) {
                                                    echo "Xayda";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $total = $row['tongdiem'];
                                                if ($total > 1000000000) {
                                                    echo number_format($total / 1000000000, 1, '.', '') . ' t&#7927;';
                                                } elseif ($total > 1000000) {
                                                    echo number_format($total / 1000000, 1, '.', '') . ' Tri&#7879;u';
                                                } elseif ($total >= 1000) {
                                                    echo number_format($total / 1000, 1, '.', '') . ' k';
                                                } else {
                                                    echo number_format($total, 0, ',', '');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                        <?php
                                }
                            } else {
                                echo '<tr><td colspan="6">M&#225;y Ch&#7911; 1 ch&#432;a c&#243; th&#7889;ng k&#234; b&#7843;ng x&#7871;p h&#7841;ng!</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">L&#7895;i khi l&#7845;y d&#7919; li&#7879;u t&#7915; c&#417; s&#7903; d&#7919; li&#7879;u: ' . mysqli_error($conn) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                </div>
                <script>
                    setInterval(function() {
                        $.ajax({
                            url: location.href,
                            success: function(result) {
                                var leaderboardTableBody = $(result).find('#leaderboard-table tbody');
                                $('#leaderboard-table tbody').html(leaderboardTableBody.html());
                            }
                        });
                    }, 3000);
                </script>
                <div class="text-right bxh-updated">
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

