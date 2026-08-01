<?php
ob_clean(); // clear output buffer
header("Location: nap-momo.php"); // redirect to "nap-momo.php"
error_reporting(0);

include_once 'connect.php';

//check lsgd
$curl = curl_init();
$dataPost = array(
    "type" => "history",
    "token" => "09b8c3bcc370e0d331ed95-5882-26e9-0635-64447055ff91",
);
curl_setopt_array($curl, array(

    CURLOPT_URL => 'https://api.web2m.com/historyapimomo/09b8c3bcc370e0d331ed95-5882-26e9-0635-64447055ff91',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $dataPost,
)
);

$response = curl_exec($curl);

curl_close($curl);
#print_r($response);
$result = json_decode($response, true);
$a = $result['momoMsg']['tranList'];
$count = count($result['momoMsg']['tranList']);
for ($x = 0; $x <= $count; $x++) {
    $tranId = $result['momoMsg']['tranList'][$x]['tranId'];
    $io = $result['momoMsg']['tranList'][$x]['io'];
    $partnerName = $result['momoMsg']['tranList'][$x]['partnerName'];
    $amount = $result['momoMsg']['tranList'][$x]['amount'];
    $comment = $result['momoMsg']['tranList'][$x]['comment'];
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `momo` WHERE `tranId`='" . $tranId . "'"));
    $show = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `momo`"));

    if ($check['tranId'] == $tranId) {
    } else {
        mysqli_query($conn, "UPDATE `user` SET vnd=vnd+{$amount}, tongnap=tongnap+{$amount} WHERE username='$comment'");

        mysqli_query($conn, "INSERT INTO `momo` SET 
    `tranId`='$tranId',
    `io`='$io',
    `partnerName`='$partnerName',
    `amount`='$amount',
    `comment`='$comment'
    ");

    }

    /*mysqli_query($conn, "INSERT INTO `regexchat` SET 
    `id`='11',
    `text`='11'
    ");*/

}

?>
<script>
    window.alert("Load lại trang sau khi đã chuyển khoản đúng nội dung !");
    window.alert("Hệ thống xử lý tự động từ 1-10',sau 10' nếu không thấy cộng vnd vui lòng liên hệ admin để xử lý !");
</script>
<?php
exit(); // exit script
?>
