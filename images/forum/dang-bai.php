<?php
include_once 'set.php';
include_once 'connect.php';
include('head.php');

if ($_login == null) {
    header("location:dang-nhap");
}

$_alert = '';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Trang Chủ Chính - Ngọc Rồng Online</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="description"
        content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngoc Rong Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!">
    <meta name="keywords"
        content="Chú Bé Rồng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title"
        content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngoc Rong Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!">
    <meta name="twitter:description"
        content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngoc Rong Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!">
    <meta name="twitter:image" content="image/logo.png">
    <meta name="twitter:image:width" content="200">
    <meta name="twitter:image:height" content="200">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/notify/notify.js"></script>
    <link rel="icon" href="image/icon.png?v=99">
    <link href="assets/main.css" rel="stylesheet">
</head>

<body>
        <div class="container color-forum pt-1 pb-1">
            <div class="row">
                <div class="col"> <a href="dien-dan" style="color: white">Quay lại diễn đàn</a> </div>
            </div>
        </div>
<div class="container pt-5 pb-5">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <form method="POST">
                        <div class="form-group">
                            <label><span class="text-danger">*</span> Tiêu đề:</label>
                            <input class="form-control" type="text" name="tieude" id="tieude"
                                placeholder="Nhập tiêu đề bài viết" required>

                            <label><span class="text-danger">*</span> Nội dung:</label>
                            <textarea class="form-control" type="text" name="noidung" id="noidung"
                                placeholder="Nhập nội dung bài viết"
                                onkeydown="if(event.shiftKey&&event.keyCode==13){document.getElementById('form-submit').click();return false;}"
                                required></textarea>

                            <div id="submit-error" class="alert alert-danger mt-2" style="display: none;"></div>
                        </div>

                        <button class="btn btn-main form-control" type="submit">ĐĂNG BÀI</button>
                    </form>
                    <script>
                        const form = document.querySelector('form');
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const submitError = form.querySelector('#submit-error');

                        form.addEventListener('submit', (event) => {
                            const titleLength = document.getElementById('tieude').value.trim().length;
                            const contentLength = document.getElementById('noidung').value.trim().length;

                            if (titleLength < 1 || contentLength < 1) {
                                event.preventDefault();

                                submitError.innerHTML = '<strong>Lỗi:</strong> Tiêu đề và nội dung phải có ít nhất 5 ký tự!';
                                submitError.style.display = 'block';
                                submitBtn.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
        <script src=" assets/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="asset/main.js"></script>
</body><!-- Bootstrap core JavaScript -->

</html>
<div class="py-3">
    <div class="table-responsive">
        <?php
        include_once 'set.php';
        // Lấy dữ liệu từ form sử dụng phương thức POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // Lấy giá trị của tiêu đề và nội dung bài viết
            $tieude = htmlspecialchars($_POST["tieude"]);
            $noidung = htmlspecialchars($_POST["noidung"]);

            if (isset($_POST['username'])) {
                $_username = $_POST['username'];
            }
            $sql = "SELECT player.name FROM player INNER JOIN account ON account.id = player.account_id WHERE account.username='$_username'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $_name = $row['name'];
            // Lưu dữ liệu (bao gồm username) vào cơ sở dữ liệu bằng câu lệnh INSERT INTO
            $sql = "INSERT INTO posts (tieude, noidung, username) VALUES ('$tieude', '$noidung', '$_name')";

            if (mysqli_query($conn, $sql)) {
                // Lấy số điểm tích lũy hiện tại của người dùng
                $sql_select = "SELECT a.tichdiem FROM account a INNER JOIN player p ON a.id = p.account_id WHERE p.name = '$_name'";
                $result_select = mysqli_query($conn, $sql_select);
                $row_select = mysqli_fetch_assoc($result_select);
                $tichdiem = $row_select['tichdiem'];

                // Cập nhật giá trị tichdiem trong bảng account
                $sql_update = "UPDATE account SET tichdiem = ($tichdiem + 1) WHERE id = (SELECT account_id FROM player WHERE name = '$_name')";
                mysqli_query($conn, $sql_update);

                echo "Bài viết đã được đăng thành công.";
                // header("Location: baiviet.php");
                // exit;
            } else {
                echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
            }
        }

        // Đóng kết nối với cơ sở dữ liệu
        mysqli_close($conn);
        ?>
    </div>
    <div class="border-secondary border-top"></div>
    <div class="container pt-4 pb-4 text-white">
        <div class="row">
            <div class="col">
                <div class="text-center">
                    <div style="font-size: 13px" class="text-dark"> <small>Desgin By Mr Blue</small><br>
                        <small>2023© Ngọc Rồng Online.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
</body>