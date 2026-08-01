
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
    <title>N&#7841;p Momo - Ch&#250; B&#233; R&#7891;ng Online</title>
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
<?php
$query = "SELECT player.name FROM player LEFT JOIN account ON player.account_id = account.id";
$result = mysqli_query($conn, $query);

$count = 0; // Biáº¿n Ä‘áº¿m sá»‘ láº§n láº·p

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $count++; // TÄƒng biáº¿n Ä‘áº¿m
        
        if ($count === 1) { // Chá»‰ hiá»ƒn thá»‹ ná»™i dung trong láº§n Ä‘áº§u tiĂªn
            ?>
            <div class="container pt-5 pb-5">
                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <h4>CĂ¡ch 1: Náº¡p qua tin nháº¯n</h4>
                        <div>
                            <p>ThĂ´ng tin má»Ÿ thĂ nh viĂªn:</p>
                            <p><strong>- TĂªn TĂ i Khoáº£n:</strong> <?php echo $_taikhoanmm; ?></p>
                            <p><strong>- NgĂ¢n HĂ ng:</strong> <?php echo $_momo; ?></p>
                            <p><strong>- Sá»‘ TĂ i Khoáº£n:</strong> <?php echo $_phonemomo; ?></p>
                            <p><strong>- Ná»™i Dung:</strong> <?php echo $_username ?></p>
                            <br>
                            <p>- XĂ¢y dá»±ng, á»§ng há»™ / hoáº¡t Ä‘á»™ng.</p>
                        </div>
                        <br>
                        <a class="btn btn-main form-control" style="border-radius:10px" href="momo">XĂ¡c nháº­n Ä‘Ă£ chuyá»ƒn khoáº£n</a>
                        <br>
                        <p><i>Khi chuyá»ƒn tiá»n xong nháº¥n xĂ¡c nháº­n Ä‘Ă£ chuyá»ƒn khoáº£n Ä‘á»ƒ xĂ¡c thá»±c giao dá»‹ch nhĂ©!</i></p>
                        <p><i>Khi xĂ¡c thá»±c xong lĂ m má»›i trang sau 1 - 3 phĂºt Ä‘á»ƒ cáº­p nháº­t Vnd.</i></p>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    mysqli_free_result($result);
} else {
    echo "Lá»—i truy váº¥n: " . mysqli_error($conn);
}
?>
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
                        <small>2026Â© Ngá»c Rá»“ng Tuá»•i ThÆ¡</small>
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
