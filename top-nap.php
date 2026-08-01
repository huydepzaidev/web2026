<?php
// top-nap.php
require_once 'connect.php';
?>
<!DOCTYPE html>
<html lang="vi"> <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>B&#7843;ng X&#7871;p H&#7841;ng Top N&#7841;p - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <meta name="description"
        content="Xem báº£ng xáº¿p háº¡ng Top Náº¡p cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta name="keywords"
        content="top náº¡p, chĂº bĂ© rá»“ng online, ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="author" content="Mr Blue"> <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Báº£ng Xáº¿p Háº¡ng Top Náº¡p - ChĂº BĂ© Rá»“ng Online">
    <meta property="og:description" content="Xem báº£ng xáº¿p háº¡ng Top Náº¡p cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta property="og:image" content="/image/logo.png">
    <meta name="twitter:card" content="summary_large_image"> <meta name="twitter:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:title" content="Báº£ng Xáº¿p Háº¡ng Top Náº¡p - ChĂº BĂ© Rá»“ng Online">
    <meta name="twitter:description" content="Xem báº£ng xáº¿p háº¡ng Top Náº¡p cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile háº¥p dáº«n nháº¥t hiá»‡n nay.">
    <meta name="twitter:image" content="/image/logo.png">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/main.css" rel="stylesheet">
	<script src="/view/static/js/disable_devtools.js"></script>
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/notify/notify.js"></script>

    <style>
        html { overflow-y: scroll; }
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(255, 213, 119, .45), transparent 28rem), linear-gradient(180deg, #7a341b 0%, #d98237 42%, #f1c06c 100%);
            color: #3b1b0c;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
        }
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
        .topnap-card {
            max-width: 630px;
            margin: 12px auto 18px;
            padding: 0;
            background: #fff6df;
            border: 3px solid #6b3016;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 34px rgba(49, 20, 8, .3);
        }
        .topnap-card > .row, .topnap-card > .row > .col { margin: 0; padding: 0; }
        .topnap-hero {
            padding: 18px 16px 14px;
            background: linear-gradient(135deg, #561d00 0%, #a14418 52%, #f0a33b 100%);
            color: #fff7d5;
            text-align: center;
        }
        .topnap-hero h1 { margin: 0; font-size: 16px; line-height: 1.25; font-weight: 800; }
        .topnap-hero p { margin: 6px 0 0; font-size: 9pt; opacity: .92; }
        .topnap-table-wrap { padding: 12px; overflow-x: hidden; }
        #topnap-table {
            width: 100%;
            min-width: 0;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0 7px;
            table-layout: fixed;
        }
        #topnap-table thead th {
            background: #5b250d;
            color: #fff1bd;
            border: 0 !important;
            padding: 9px 10px !important;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        #topnap-table tbody tr { background: #fffaf0; box-shadow: 0 1px 0 rgba(104, 48, 18, .14), 0 2px 8px rgba(104, 48, 18, .08); }
        #topnap-table tbody tr:nth-of-type(odd) { background: #fff1d3; }
        #topnap-table th, #topnap-table td {
            white-space: normal;
            border: 0 !important;
            padding: 8px 6px !important;
            font-size: 9pt;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        #topnap-table th:nth-child(1), #topnap-table td:nth-child(1) { width: 44px; }
        #topnap-table th:nth-child(2), #topnap-table td:nth-child(2) { width: 42%; }
        #topnap-table th:nth-child(3), #topnap-table td:nth-child(3) { width: 42%; }
        #topnap-table tbody td:first-child, #topnap-table thead th:first-child { border-radius: 7px 0 0 7px; }
        #topnap-table tbody td:last-child, #topnap-table thead th:last-child { border-radius: 0 7px 7px 0; }
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
        .topnap-updated { padding: 0 14px 14px; color: #6b3016; }
        .topnap-empty { padding: 18px !important; color: #7a3a1b; font-weight: 700; text-align: center !important; }
        @media (max-width: 576px) {
            .ranking-shell { margin-top: 8px; padding: 0; }
            .ranking-logo { height: 70px; }
            .ranking-logo img { height: 70px; }
            .ranking-nav a { font-size: 9pt; padding: 7px 2px; }
            .topnap-card { margin-left: 8px; margin-right: 8px; }
            .topnap-hero h1 { font-size: 15px; }
            .topnap-table-wrap { padding: 8px; }
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

    <div class="container color-forum pt-2 topnap-card">
        <div class="row">
            <div class="col">
                <div class="topnap-hero"><h1>B&#7842;NG X&#7870;P H&#7840;NG &#272;UA TOP N&#7840;P</h1><p>Top 50 nh&#226;n v&#7853;t c&#243; t&#7893;ng n&#7841;p cao nh&#7845;t</p></div>
                <div class="topnap-table-wrap">
                <table class="table text-center" id="topnap-table"> <thead> <tr>
                            <th>STT</th>
                            <th>Nh&#226;n V&#7853;t</th>
                            <th>T&#7893;ng N&#7841;p</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'connect.php'; 
                        if (!$conn) {
                            echo '<tr><td colspan="3" class="alert alert-danger">Kh&#244;ng th&#7875; k&#7871;t n&#7889;i &#273;&#7871;n c&#417; s&#7903; d&#7919; li&#7879;u. Vui l&#242;ng th&#7917; l&#7841;i sau!</td></tr>';
                        } else {
                            $query = "SELECT p.name, SUM(a.tongnap) AS tongnap 
                                      FROM account a 
                                      JOIN player p ON a.id = p.account_id 
                                      GROUP BY p.name 
                                      ORDER BY tongnap DESC 
                                      LIMIT 50";
                            
                            $result = $conn->query($query);
                            
                            if ($result === false) {
                                echo '<tr><td colspan="3" class="alert alert-danger">L&#7895;i truy v&#7845;n SQL: ' . htmlspecialchars($conn->error) . '</td></tr>';
                            } else if ($result->num_rows > 0) {
                                $stt = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo '
                                    <tr class="top_' . $stt . '">
                                        <td><span class="rank-badge">' . $stt . '</span></td>
                                        <td>' . htmlspecialchars($row['name']) . '</td>
                                        <td>' . number_format($row['tongnap'], 0, ',', '.') . '&#273;</td>
                                    </tr>
                                    ';
                                    $stt++;
                                }
                            } else {
                                echo '<tr><td colspan="3" class="topnap-empty">M&#225;y Ch&#7911; 1 ch&#432;a c&#243; th&#7889;ng k&#234; b&#7843;ng x&#7871;p h&#7841;ng!</td></tr>';
                            }
                            $conn->close();
                        }
                        ?>
                    </tbody>
                </table>
                </div>
                <div class="text-right w-100 mt-3 topnap-updated"> <small>C&#7853;p nh&#7853;t l&#250;c:
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



