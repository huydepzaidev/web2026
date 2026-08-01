<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../connect.php';

$_alert = '';
$post_id = null;
$post_detail = null;

$logged_in_username = $_SESSION['username'] ?? null;
$is_admin = false;

if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
        $post_id = intval($_GET['id']);
    } else {
        $_alert = "<div class='alert alert-danger'>ID bài viết không hợp lệ.</div>";
    }
}

if ($post_id !== null && isset($conn)) {
    $stmt = $conn->prepare("SELECT id, tieude, noidung, username, image FROM posts WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $post_detail = $result->fetch_assoc();

            if ($post_detail['username'] !== $logged_in_username && !$is_admin) {
                $_alert = "<div class='alert alert-danger'>Bạn không có quyền chỉnh sửa bài viết này.</div>";
                $post_detail = null;
            }
        } else {
            $_alert = "<div class='alert alert-danger'>Bài viết không tồn tại.</div>";
        }
        $stmt->close();
    } else {
        $_alert = "<div class='alert alert-danger'>Đã xảy ra lỗi khi tải bài viết. Vui lòng thử lại sau.</div>";
    }
} else {
    if ($logged_in_username === null) {
        $_alert = "<div class='alert alert-warning'>Vui lòng đăng nhập để chỉnh sửa bài viết.</div>";
    } else {
        $_alert = "<div class='alert alert-info'>Vui lòng cung cấp ID bài viết để chỉnh sửa.</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit'])) {
    if ($logged_in_username === null) {
        $_alert = "<div class='alert alert-danger'>Bạn cần đăng nhập để thực hiện hành động này.</div>";
    } elseif ($post_detail === null) {
        $_alert = "<div class='alert alert-danger'>Không thể chỉnh sửa bài viết không tồn tại hoặc không có quyền.</div>";
    } else {
        if ($post_detail['username'] !== $logged_in_username && !$is_admin) {
            $_alert = "<div class='alert alert-danger'>Bạn không có quyền chỉnh sửa bài viết này.</div>";
        } else {
            $new_tieude = trim($_POST['tieude']);
            $new_noidung = trim($_POST['noidung']);
            $post_id_from_form = intval($_POST['post_id']);

            if (empty($new_tieude) || strlen($new_tieude) < 5) {
                $_alert = "<div class='alert alert-danger'>Tiêu đề bài viết phải có ít nhất 5 ký tự.</div>";
            } elseif (empty($new_noidung) || strlen($new_noidung) < 10) {
                $_alert = "<div class='alert alert-danger'>Nội dung bài viết phải có ít nhất 10 ký tự.</div>";
            } elseif ($post_id_from_form !== $post_id) {
                $_alert = "<div class='alert alert-danger'>ID bài viết không khớp.</div>";
            } else {
                $stmt_update = $conn->prepare("UPDATE posts SET tieude = ?, noidung = ? WHERE id = ?");
                if ($stmt_update) {
                    $stmt_update->bind_param("ssi", $new_tieude, $new_noidung, $post_id);
                    if ($stmt_update->execute()) {
                        header('Location: ' . webgoc_url('forum'));
                        exit();
                    } else {
                        $_alert = "<div class='alert alert-danger'>Lỗi khi cập nhật bài viết: " . htmlspecialchars($stmt_update->error) . "</div>";
                    }
                    $stmt_update->close();
                } else {
                    $_alert = "<div class='alert alert-danger'>Lỗi chuẩn bị câu lệnh SQL để cập nhật: " . htmlspecialchars($conn->error) . "</div>";
                }
            }
        }
    }
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>
