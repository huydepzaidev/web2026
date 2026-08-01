<?php
include_once 'set.php';
include_once 'connect.php';
include_once 'head.php';
if ($_login == null) {
    header("location:dang-nhap");
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>M&#7853;t Kh&#7849;u C&#7845;p 2 - Ch&#250; B&#233; R&#7891;ng Online</title>
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
    <link href="assets//main.css" rel="stylesheet">
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
                    <h4>Báº¢O Máº¬T Cáº¤P 2 - Báº¢O Vá»† TĂ€I KHOáº¢N</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT password, mkc2 FROM account WHERE username=?");
                    $stmt->bind_param("s", $_username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $primaryPassword = $row['password'];
                    $mkc2 = $row['mkc2'];

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $password = $_POST['password'] ?? '';
                        $new_passwordcap2 = $_POST['new_passwordcap2'] ?? '';
                        $new_passwordcap2xacnhan = $_POST['new_passwordcap2xacnhan'] ?? '';

                        if (!empty($mkc2)) {
                            $old_passwordcap2 = isset($_POST['old_passwordcap2']) ? $_POST['old_passwordcap2'] : '';

                            if (!empty($password) && !empty($new_passwordcap2) && !empty($new_passwordcap2xacnhan) && !empty($old_passwordcap2)) {
                                // Kiá»ƒm tra xem máº­t kháº©u hiá»‡n táº¡i nháº­p vĂ o cĂ³ giá»‘ng vá»›i máº­t kháº©u trong database khĂ´ng.
                                // Náº¿u sai, in ra thĂ´ng bĂ¡o lá»—i.
                                if ($password !== $primaryPassword) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Sai máº­t kháº©u hiá»‡n táº¡i</div>";
                                } elseif ($old_passwordcap2 !== $mkc2) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Sai máº­t kháº©u cáº¥p 2 hiá»‡n táº¡i</div>";
                                } elseif ($new_passwordcap2 === $password) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Máº­t kháº©u cáº¥p 2 khĂ´ng Ä‘Æ°á»£c giá»‘ng máº­t kháº©u hiá»‡n táº¡i</div>";
                                } elseif ($new_passwordcap2 !== $new_passwordcap2xacnhan) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Máº­t kháº©u cáº¥p 2 khĂ´ng giá»‘ng nhau</div>";
                                } elseif ($password === $new_passwordcap2) { // Kiá»ƒm tra máº­t kháº©u hiá»‡n táº¡i cĂ³ trĂ¹ng vá»›i máº­t kháº©u má»›i hay khĂ´ng.
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Máº­t kháº©u cáº¥p 2 pháº£i khĂ¡c vá»›i máº­t kháº©u hiá»‡n táº¡i</div>";
                                } else {
                                    // Cáº­p nháº­t máº­t kháº©u cáº¥p 2 lĂªn database
                                    $stmt = $conn->prepare("UPDATE account SET mkc2=? WHERE username=?");
                                    $stmt->bind_param("ss", $new_passwordcap2, $_username);

                                    if ($stmt->execute()) {
                                        echo "<div class='text-danger pb-2 font-weight-bold'>Cáº­p nháº­t máº­t kháº©u cáº¥p 2 thĂ nh cĂ´ng</div>";
                                    } else {
                                        echo "<div class='text-danger pb-2 font-weight-bold'>Lá»—i khi cáº­p nháº­t máº­t kháº©u cáº¥p 2</div>";
                                    }
                                }
                            } else {
                                echo "<div class='text-danger pb-2 font-weight-bold'>Vui lĂ²ng Ä‘iá»n Ä‘áº§y Ä‘á»§ thĂ´ng tin trong form</div>";
                            }
                        } else {
                            if (!empty($password) && !empty($new_passwordcap2) && !empty($new_passwordcap2xacnhan)) {
                                if ($password !== $primaryPassword) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Sai máº­t kháº©u hiá»‡n táº¡i</div>";
                                } elseif ($new_passwordcap2 === $password) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Máº­t kháº©u cáº¥p 2 khĂ´ng Ä‘Æ°á»£c giá»‘ng máº­t kháº©u hiá»‡n táº¡i</div>";
                                } elseif ($new_passwordcap2 !== $new_passwordcap2xacnhan) {
                                    echo "<div class='text-danger pb-2 font-weight-bold'>Máº­t kháº©u cáº¥p 2 khĂ´ng giá»‘ng nhau</div>";
                                } else {
                                    $stmt = $conn->prepare("UPDATE account SET mkc2=? WHERE username=?");
                                    $stmt->bind_param("ss", $new_passwordcap2, $_username);

                                    if ($stmt->execute()) {
                                        echo "<div class='text-danger pb-2 font-weight-bold'>Cáº­p nháº­t máº­t kháº©u cáº¥p 2 thĂ nh cĂ´ng</div>";
                                    } else {
                                        echo "<div class='text-danger pb-2 font-weight-bold'>Lá»—i khi cáº­p nháº­t máº­t kháº©u cáº¥p 2</div>";
                                    }
                                }
                            } else {
                                echo "<div class='text-danger pb-2 font-weight-bold'>Vui lĂ²ng Ä‘iá»n Ä‘áº§y Ä‘á»§ thĂ´ng tin trong form</div>";
                            }
                        }
                    }

                    if (!empty($mkc2)) {
                        ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="font-weight-bold">Máº­t Kháº©u hiá»‡n táº¡i:</label>
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="Máº­t kháº©u hiá»‡n táº¡i" required autocomplete="password">
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold">Máº­t Kháº©u Cáº¥p 2 Hiá»‡n Táº¡i:</label>
                                <input type="password" class="form-control" name="old_passwordcap2" id="old_passwordcap2"
                                    placeholder="Máº­t kháº©u cáº¥p 2 hiá»‡n táº¡i" required autocomplete="old-passwordcap2">
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold">Máº­t Kháº©u Cáº¥p 2 Má»›i:</label>
                                <input type="password" class="form-control" name="new_passwordcap2" id="new_passwordcap2"
                                    placeholder="Máº­t kháº©u cáº¥p 2 má»›i" required autocomplete="new-passwordcap2">
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold">XĂ¡c Nháº­n Máº­t Kháº©u Cáº¥p 2:</label>
                                <input type="password" class="form-control" name="new_passwordcap2xacnhan"
                                    id="new_passwordcap2xacnhan" placeholder="XĂ¡c nháº­n máº­t kháº©u cáº¥p 2 má»›i" required
                                    autocomplete="new-passwordcap2xacnhan">
                            </div>
                            <button class="btn btn-main form-control" type="submit">Thá»±c hiá»‡n</button>
                        </form>
                    <?php } else { ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="font-weight-bold">Máº­t Kháº©u Hiá»‡n Táº¡i:</label>
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="Máº­t kháº©u hiá»‡n táº¡i" required autocomplete="password">
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold">Máº­t Kháº©u Cáº¥p 2:</label>
                                <input type="password" class="form-control" name="new_passwordcap2" id="new_passwordcap2"
                                    placeholder="Máº­t kháº©u cáº¥p 2" required autocomplete="new-passwordcap2">
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold">Nháº­p Láº¡i Máº­t Kháº©u Cáº¥p 2:</label>
                                <input type="password" class="form-control" name="new_passwordcap2xacnhan"
                                    id="new_passwordcap2xacnhan" placeholder="XĂ¡c nháº­n máº­t kháº©u cáº¥p 2" required
                                    autocomplete="new-passwordcap2xacnhan">
                            </div>
                            <button class="btn btn-main form-control" type="submit">Thá»±c hiá»‡n</button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="border-secondary border-top"></div>
        <div class="container pt-4 pb-4 text-white">
            <div class="row">
                <div class="col">
                    <div class="text-center">
                        <div style="font-size: 13px" class="text-dark"> <small>Desgin By Nguyá»…n Äá»©c KiĂªn</small><br>
                            <small>2023Â©NGá»ŒC Rá»’NG LIGHT</small>
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
