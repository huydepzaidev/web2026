<?php
include_once 'set.php';
include_once 'connect.php';

if ($_login == null) {
    header("location:dang-nhap");
}
$_alert = null;
include('head.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>N&#7841;p S&#7889; D&#432; - Ch&#250; B&#233; R&#7891;ng Online</title>
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
    <link href="assets/main.css" rel="stylesheet">
    <script src="http://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.rawgit.com/daneden/animate.css/v3.1.0/animate.min.css">
    <link rel="stylesheet" href="https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css">
</head>

<body>
    <div class="container color-forum pt-1 pb-1">
        <div class="row">
            <div class="col">
                <a href="dien-dan" style="color: white">Quay láº¡i diá»…n Ä‘Ă n</a>
            </div>
        </div>
    </div>
    <div class="container pt-3 pb-5">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div class="text-center pb-3"> <a href="history" class="text-dark"><i
                            class="fas fa-hand-point-right"></i> Xem tĂ¬nh tráº¡ng náº¡p tháº» <i
                            class="fas fa-hand-point-left"></i></a> </div>
                <h4>Náº P Sá» DÆ¯</h4>
                <form method="POST" action="#" id="myform">
                    <tbody>
                        <label>TĂ i Khoáº£n: </label><br>
                        <input type="text" class="form-control form-control-alternative"
                            style="background-color: #DCDCDC; font-weight: bold; color: #696969" name="username" value="<?php echo $_username; ?>
" readonly required>

                        <label>Loáº¡i tháº»:</label>
                        <select class="form-control form-control-alternative" name="card_type" required>
                            <option value="">Chá»n loáº¡i tháº»</option>
                            <?php
                            $cdurl = curl_init("https://thesieutoc.net/card_info.php");
                            curl_setopt($cdurl, CURLOPT_FAILONERROR, true);
                            curl_setopt($cdurl, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($cdurl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($cdurl, CURLOPT_CAINFO, __DIR__ . '/api/curl-ca-bundle.crt');
                            curl_setopt($cdurl, CURLOPT_CAPATH, __DIR__ . '/api/curl-ca-bundle.crt');
                            $obj = json_decode(curl_exec($cdurl), true);
                            curl_close($cdurl);
                            $length = count($obj);
                            for ($i = 0; $i < $length; $i++) {
                                if ($obj[$i]['status'] == 1) {
                                    echo '<option value="' . $obj[$i]['name'] . '">' . $obj[$i]['name'] . ' (' . $obj[$i]['chietkhau'] . '%)</option> ';
                                }
                            }
                            ?>
                        </select>
                        <label>Má»‡nh giĂ¡:</label>
                        <select class="form-control form-control-alternative" name="card_amount" required>
                            <option value="">Chá»n má»‡nh giĂ¡</option>
                            <option value="10000">10.000</option>
                            <option value="20000">20.000</option>
                            <option value="30000">30.000 </option>
                            <option value="50000">50.000</option>
                            <option value="100000">100.000</option>
                            <option value="200000">200.000</option>
                            <option value="300000">300.000</option>
                            <option value="500000">500.000</option>
                            <option value="1000000">1.000.000</option>
                        </select>
                        <label>Sá»‘ seri:</label>
                        <input type="text" class="form-control form-control-alternative" name="serial" required />
                        <label>MĂ£ tháº»:</label>
                        <input type="text" class="form-control form-control-alternative" name="pin" required /><br>
                        <button type="submit" class="btn btn-main form-control" name="submit">Náº P NGAY</button>

                    </tbody>
                </form>
                <script type="text/javascript">

                    $(document).ready(function () {
                        var lastSubmitTime = 0;
                        $("#myform").submit(function (e) {
                            $("#status").html("<img src='./img/load.gif' width='0%' />");
                            e.preventDefault();

                            var now = new Date().getTime();
                            if (now - lastSubmitTime < 5000) { // 10000 milliseconds = 10 seconds
                                swal('ThĂ´ng bĂ¡o', 'Vui lĂ²ng Ä‘á»£i Ă­t nháº¥t 5 giĂ¢y trÆ°á»›c khi náº¡p tiáº¿p', 'error');
                                return false;
                            }
                            lastSubmitTime = now;

                            $.ajax({
                                url: "./ajax/card.php",
                                type: 'post',
                                data: $("#myform").serialize(),
                                success: function (data) {
                                    $("#status").html(data);
                                    document.getElementById("myform").reset();
                                    $("#load_hs").load(".history.php");
                                }
                            });
                        });

                    });
                </script>
                <div>
                </div>
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
    <div id="status"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <!-- Code made in tui 127.0.0.1 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
        integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T"
        crossorigin="anonymous"></script>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</body><!-- Bootstrap core JavaScript -->

</html>
