<?php
include_once 'set.php';
include_once 'connect.php';

if ($_login == null) {
    header("location:dang-nhap");
}
include('head.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>M&#7903; Th&#224;nh Vi&#234;n - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <meta name="description" content="">
    <meta name="author" content="">
    <base href="">
    <meta name="description"
        content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="keywords"
        content="ChĂº BĂ© Rá»“ng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title"
        content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="twitter:description"
        content="Website chĂ­nh thá»©c cá»§a ChĂº BĂ© Rá»“ng Online â€“ Game Bay Vien Ngoc Rong Mobile nháº­p vai trá»±c tuyáº¿n trĂªn mĂ¡y tĂ­nh vĂ  Ä‘iá»‡n thoáº¡i vá» Game 7 ViĂªn Ngá»c Rá»“ng háº¥p dáº«n nháº¥t hiá»‡n nay!">
    <meta name="twitter:image" content="image/logo.png">
    <meta name="twitter:image:width" content="200">
    <meta name="twitter:image:height" content="200">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/notify/notify.js"></script>
    <link href="assets/main.css" rel="stylesheet">
</head>

<body>
    <div class="container color-forum pt-1 pb-1">
        <div class="row">
            <div class="col"> <a href="dien-dan" style="color: white">Quay láº¡i diá»…n Ä‘Ă n</a> </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <h4>Má» THĂ€NH VIĂN</h4>
                <?php
                // Check if account is already activated
                if ($_status == '1') {
                    $_alert = '<div class="text-danger pb-2 font-weight-bold">TĂ i khoáº£n cá»§a báº¡n Ä‘Ă£ Ä‘Æ°á»£c kĂ­ch hoáº¡t!</div>';
                }
                // Check if account is not activated and balance is insufficient
                elseif (($_status == '0' || $_status == '-1') && $_coin < 20000) {
                    $_alert = '<div class="text-danger pb-2 font-weight-bold">Báº¡n khĂ´ng Ä‘á»§ 20.000 KCoin. Vui lĂ²ng náº¡p thĂªm tiá»n vĂ o tĂ i khoáº£n Ä‘á»ƒ ' . ($_status == '0' ? 'kĂ­ch hoáº¡t nhĂ©!' : 'má»Ÿ láº¡i tĂ i khoáº£n!</div>');
                }
                // Activate or unlock account
                elseif (($_status == '0' || $_status == '-1') && $_coin >= 20000) {
                    $coin = $_coin - 20000;
                    $stmt = $mysqli->prepare('UPDATE account SET active = 1, coin = ? WHERE username = ?');
                    $stmt->bind_param('is', $coin, $_username);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $_alert = '<div class="text-danger pb-2 font-weight-bold">KĂ­ch hoáº¡t tĂ i khoáº£n thĂ nh cĂ´ng. BĂ¢y giá» báº¡n Ä‘Ă£ cĂ³ thá»ƒ Ä‘Äƒng nháº­p vĂ o game!</div>';
                        if ($_status == '-1') {
                            $_alert = '<div class="text-danger pb-2 font-weight-bold">Má»Ÿ khĂ³a tĂ i khoáº£n thĂ nh cĂ´ng. BĂ¢y giá» báº¡n Ä‘Ă£ cĂ³ thá»ƒ Ä‘Äƒng nháº­p vĂ o game!</div>';
                        }
                    } else {
                        $_alert = '<div class="text-danger pb-2 font-weight-bold">CĂ³ lá»—i gĂ¬ Ä‘Ă³ xáº£y ra. Vui lĂ²ng liĂªn há»‡ Admin!</div>';
                    }
                }
                ?>
                <form id="form" method="POST">
                    <div> ThĂ´ng tin má»Ÿ thĂ nh viĂªn:<br>- Má»Ÿ thĂ nh viĂªn vá»›i chá»‰ <strong>0 VNÄ</strong>. <img
                            src="image/hot.gif"><br>- ÄÆ°á»£c miá»…n phĂ­ <strong>GiftCode ThĂ nh viĂªn</strong>. <img
                            src="image/hot.gif"><br>- Táº­n hÆ°á»Ÿng trá»n váº¹n cĂ¡c tĂ­nh nÄƒng. <img src="image/hot.gif"><br>-
                        XĂ¢y dá»±ng, á»§ng há»™ / hoáº¡t Ä‘á»™ng. </div>
                    <div id="notify" class="text-danger pb-2 font-weight-bold"></div>
                    <?php if (isset($_POST['submit']))
                        echo $_alert; ?>
                    <button class="btn btn-main form-control" id="btn" type="submit" name="submit">Má» NGAY</button>
                </form>

            </div>
        </div>
    </div>
    <div class=" border-secondary border-top">
    </div>
    <div class="container pt-4 pb-4 text-white">
        <div class="row">
            <div class="col">
                <div class="text-center">
                    <div style="font-size: 13px" class="text-dark">
                                    <small>IP:
                                        <?php echo $_IP; ?>
                                    </small><br>
                                    <small>Desgin By Mr Blue</small><br>
                                    <small>2023Â© Ngá»c Rá»“ng Tuá»•i ThÆ¡</small>
                                </div>
                            </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</body><!-- Bootstrap core JavaScript -->

</html>
