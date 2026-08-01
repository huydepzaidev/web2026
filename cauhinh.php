<?php
$_domain = (isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/') : '/'); // điền domain của sự kiện giới thiệu của bạn
$_IP = '127.0.0.1'; // IP hiển thị ở phần cuối trang

// MySQL
require_once __DIR__ . '/connect.php';

// API
$w_cuphap_momo = 'Mr Blue'; // cú pháp
$_qrmomo = 'img/qrmomo.png'; // link ảnh qr code
$_phonemomo = '0392920228'; // số điện thoại momo
$_momo = 'Momo'; // ngân hàng momo
$_nganhang = 'Mbbank - Ngân Hàng Quân Đội'; // ngân hàng quân đội mbbank
$_taikhoanmm = 'Mr Blue'; // tên tài khoản

?>
