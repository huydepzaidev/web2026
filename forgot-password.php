<?php
require_once 'connect.php';
require_once 'set.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Qu&#234;n M&#7853;t Kh&#7849;u - Ch&#250; B&#233; R&#7891;ng Online</title>
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
    <script src="assets/jquery/jquery.min.js"></>
            <script src="assets/notify/notify.js"></script>
    <link href="assets/main.css" rel="stylesheet">
</head>

<body>
    <div class="container" style="border-radius: 15px; background: #ffaf4c; padding: 0px">
        <div class="container" style="background-color: #e67e22; border-radius: 15px 15px 0px 0px">
            <div class="row bg pb-3 pt-2">
                <div class="col">
                    <div class="text-center mb-2"> <a href="dien-dan"><img class="rounded" src="images/logo_sk_he.png"
                                id="logo"></a> </div>
                    <div class="text-center pt-2">
                        <div style="display: inline-block;"> <a href="tai-game/android"> <img class="icon-download"
                            src="images/android.png"></a> <br>
                            <small class="text-dark">0.0.1</small>
                        </div>
                        <div style="display: inline-block;"> <a href="tai-game/windows"><img class="icon-download"
                            src="images/pc.png"></a> <br> <small class="text-dark">0.0.1</small> </div>
                        <div style="display: inline-block;"> <a href="tai-game/iphone"><img class="icon-download"
                            src="images/ip.png"></a> <br> <small class="text-dark">0.0.1</small> </div>
                    <div> <img height="12" src="images/12.png" style="vertical-align: middle;"> <small
                                style="font-size: 10px" id="hour3">DĂ nh cho
                                ngÆ°á»i chÆ¡i trĂªn 12 tuá»•i. ChÆ¡i quĂ¡ 180 phĂºt má»—i ngĂ y sáº½ háº¡i sá»©c khá»e.</small> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container color-main2 pb-2">
            <div class="text-center">
                <div class="row">
                    <div class="col pr-0"> <a href="dang-nhap" class="btn p-1 btn-header">ÄÄƒng
                            nháº­p</a> </div>
                    <div class="col"> <a href="dang-ky" class="btn p-1 btn-header-active">ÄÄƒng
                            kĂ½</a> </div>
                </div>
            </div>
        </div>
        <div class="container color-forum pt-1 pb-1">
            <div class="row">
                <div class="col"> <a href="dien-dan" style="color: white">Quay láº¡i diá»…n Ä‘Ă n</a> </div>
            </div>
        </div>
        <div class="container pt-5 pb-5">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <h4>QUĂN Máº¬T KHáº¨U</h4>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $username = mysqli_real_escape_string($conn, $_POST['username']);
                        $mkc2 = mysqli_real_escape_string($conn, $_POST['mkc2']);

                        // Check if the username and mkc2 combination exists in the account table
                        $query = "SELECT * FROM account WHERE username='$username' AND mkc2='$mkc2'";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);

                        if ($row) {
                            // Display new password update form
                            ?>
                            <form id="update-form" method="POST">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                <div class="form-group">
                                    <label for="newpassword">Máº­t kháº©u má»›i:</label>
                                    <input class="form-control" type="password" name="newpassword" id="newpassword"
                                        placeholder="Nháº­p máº­t kháº©u má»›i" required>
                                </div>
                                <button class="btn btn-main form-control" type="submit" name="submit">Cáº¬P NHáº¬T Máº¬T KHáº¨U
                                    Má»I</button>
                            </form>
                            <?php
                        } else {
                            echo "<div class='text-danger pb-2 font-weight-bold'>TĂ i khoáº£n hoáº·c máº­t kháº©u cáº¥p 2 khĂ´ng há»£p lá»‡.</div>";
                        }

                        if (isset($_POST['newpassword'])) {
                            $newpassword = password_hash($_POST['newpassword'], PASSWORD_DEFAULT); // Hash new password before storing
                    
                            // Update new password in the database
                            $updateQuery = "UPDATE account SET password='$newpassword' WHERE username='$username'";
                            mysqli_query($conn, $updateQuery);

                            echo "<div class='text-success pb-2 font-weight-bold'>Cáº­p nháº­t máº­t kháº©u thĂ nh cĂ´ng!</div>";
                        }
                    }
                    ?>
                    <form id="form" method="POST">
                        <div class="form-group">
                            <label for="username">TĂ i khoáº£n:</label>
                            <input class="form-control" type="text" name="username" id="username"
                                placeholder="Nháº­p tĂ i khoáº£n" required>
                        </div>
                        <div class="form-group">
                            <label for="mkc2">Máº­t kháº©u cáº¥p 2:</label>
                            <input class="form-control" type="password" name="mkc2" id="mkc2"
                                placeholder="Nháº­p máº­t kháº©u cáº¥p 2" required>
                        </div>

                        <?php
                        if (!empty($_alert)) {
                            echo $_alert;
                        }
                        ?>

                        <div id="notify" class="text-danger pb-2 font-weight-bold"></div>
                        <button class="btn btn-main form-control" type="submit" name="submit">XĂC NHáº¬N</button>
                    </form>
                    <br>
                </div>
            </div>
        </div>
        <div class="border-secondary border-top"></div>
        <div class="container pt-4 pb-4 text-white">
            <div class="row">
                <div class="col">
                    <div class="text-center">
                        <div style="font-size: 13px" class="text-dark">
                                    <small>IP:
                                        <?php echo $_IP; ?>
                                    </small><br>
                                    <small>Desgin By Mr Blue</small><br>
                                    <small>2023Â© Ngá»c Rá»“ng Online</small>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
