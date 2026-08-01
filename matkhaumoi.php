<?php
include('set.php');
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $matkhau = $_POST['matkhau'];
    $xacnhan_matkhau = $_POST['xacnhan_matkhau'];

    if (empty($matkhau)) {
        echo '<div class="text-danger pb-2 font-weight-bold">Vui lĂ²ng nháº­p máº­t kháº©u má»›i.</div>';
        exit;
    }

    if ($matkhau !== $xacnhan_matkhau) {
        echo '<div class="text-danger pb-2 font-weight-bold">XĂ¡c nháº­n máº­t kháº©u má»›i khĂ´ng Ä‘Ăºng.</div>';
        exit;
    }

    // Escape cĂ¡c giĂ¡ trá»‹ Ä‘á»ƒ trĂ¡nh SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $matkhau = mysqli_real_escape_string($conn, $matkhau);

    // Cáº­p nháº­t máº­t kháº©u má»›i vĂ o cÆ¡ sá»Ÿ dá»¯ liá»‡u
    $sql = "UPDATE account SET password = '$matkhau' WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $_SESSION['alert'] = '<div class="text-danger pb-2 font-weight-bold">Äá»•i máº­t kháº©u thĂ nh cĂ´ng.</div>';
    } else {
        $_SESSION['alert'] = '<div class="text-danger pb-2 font-weight-bold">Äá»•i máº­t kháº©u tháº¥t báº¡i.</div>';
    }

    mysqli_close($conn);
}
?>
