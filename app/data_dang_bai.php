<?php
session_start();
include_once __DIR__ . '/../set.php';
include_once __DIR__ . '/../connect.php';

if ($_login == null) {
    header("location:login");
    exit();
}
$_alert = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tieude = htmlspecialchars($_POST["tieude"]);
    $noidung = htmlspecialchars($_POST["noidung"]);

    if (strlen($tieude) < 5 || strlen(trim(strip_tags($noidung))) < 5) {
        $_SESSION['alert_message'] = "<div class='alert alert-danger'>Tiêu đề và nội dung phải có ít nhất 5 ký tự!</div>";
    } else {
        if (!isset($_username)) {
            $_SESSION['alert_message'] = "<div class='alert alert-danger'>Lỗi: Không thể xác định tên người dùng. Vui lòng đăng nhập lại.</div>";
        } else {
            $stmt_player_name = $conn->prepare("SELECT p.name FROM player p JOIN account a ON a.id = p.account_id WHERE a.username = ?");
            $stmt_player_name->bind_param("s", $_username);
            $stmt_player_name->execute();
            $result_player_name = $stmt_player_name->get_result();
            $row_player_name = $result_player_name->fetch_assoc();
            $_name = $row_player_name['name'] ?? 'Guest';
            $stmt_player_name->close();

            $stmt_insert_post = $conn->prepare("INSERT INTO posts (tieude, noidung, username) VALUES (?, ?, ?)");
            $stmt_insert_post->bind_param("sss", $tieude, $noidung, $_name);

            if ($stmt_insert_post->execute()) {
                $stmt_select_tichdiem = $conn->prepare("SELECT a.tichdiem FROM account a JOIN player p ON a.id = p.account_id WHERE p.name = ?");
                $stmt_select_tichdiem->bind_param("s", $_name);
                $stmt_select_tichdiem->execute();
                $result_select_tichdiem = $stmt_select_tichdiem->get_result();
                $row_select_tichdiem = $result_select_tichdiem->fetch_assoc();
                $tichdiem = $row_select_tichdiem['tichdiem'] ?? 0;
                $stmt_select_tichdiem->close();

                $stmt_update_tichdiem = $conn->prepare("UPDATE account SET tichdiem = (? + 1) WHERE id = (SELECT account_id FROM player WHERE name = ?)");
                $new_tichdiem = $tichdiem + 1;
                $stmt_update_tichdiem->bind_param("is", $new_tichdiem, $_name);
                $stmt_update_tichdiem->execute();
                $stmt_update_tichdiem->close();
                $_SESSION['redirect_to_forum'] = true;
                $_SESSION['alert_message'] = "<div class='alert alert-success'>Bài viết đã được đăng thành công.</div>";
                header('Location: ' . webgoc_url('forum'));
                exit();
            } else {
                $_SESSION['alert_message'] = "<div class='alert alert-danger'>Lỗi khi đăng bài viết: " . $stmt_insert_post->error . "</div>";
            }
            $stmt_insert_post->close();
        }
    }
}
mysqli_close($conn); 
?>
