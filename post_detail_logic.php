<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'settings.php';
require_once __DIR__ . '/connect.php';

$post_detail = null;
$_alert = '';
$logged_in_user_id = $_SESSION['id'] ?? null;
$logged_in_username = $_SESSION['username'] ?? null;

$logged_in_player_gender = 0;
$logged_in_player_head = 0;
$is_admin = 0;
if ($logged_in_username !== null && isset($conn)) {
    $stmt_user_info = $conn->prepare("
        SELECT
            p.gender,
            p.head
        FROM account a
        LEFT JOIN player p ON a.id = p.account_id
        WHERE a.username = ?
    ");
    if ($stmt_user_info) {
        $stmt_user_info->bind_param("s", $logged_in_username);
        $stmt_user_info->execute();
        $result_user_info = $stmt_user_info->get_result();
        if ($result_user_info->num_rows > 0) {
            $user_info = $result_user_info->fetch_assoc();
            $logged_in_player_gender = $user_info['gender'] ?? 0;
            $logged_in_player_head = $user_info['head'] ?? 0;
        }
        $stmt_user_info->close();
    }
}

$_is_logged_in = ($logged_in_username !== null);

$post_id = null;
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
        $post_id = intval($_GET['id']);
    } else {
        $_alert = "<div class='alert alert-danger'>ID bài viết không hợp lệ.</div>";
    }
}
if ($post_id !== null && isset($conn)) {
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.tieude,
            p.noidung,
            p.username,
            p.created_at,
            p.image,
            p.ghimbai,     -- Lấy thêm cột ghimbai
            pl.gender AS author_gender,
            pl.head AS author_head
        FROM
            posts p
        LEFT JOIN
            account a ON p.username = a.username
        LEFT JOIN
            player pl ON a.id = pl.account_id
        WHERE p.id = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $post_detail = $result->fetch_assoc();
                        $views_column_result = $conn->query("SHOW COLUMNS FROM posts LIKE 'views'");
            if ($views_column_result && $views_column_result->num_rows > 0) {
                $conn->query("UPDATE posts SET views = views + 1 WHERE id = " . $post_id);
            }
            if ($views_column_result) {
                $views_column_result->free();
            }

            $author_avatar_src = '/images/avatar/default_avatar.png';
            $author_gender = $post_detail['author_gender'] ?? 0;
            $author_head = $post_detail['author_head'] ?? 0;
            $is_ghimbai = ($post_detail['ghimbai'] ?? 0) == 1;
            if ($is_ghimbai) {
                $author_avatar_src = "/images/avatar/6101.gif";
            } else {
                if ($author_head > 0) {
                    $author_avatar_src = "/images/avatar/" . htmlspecialchars($author_head) . ".png";
                } else {
                    if ($author_gender == 0) {
                        $author_avatar_src = "/images/avatar/0.png";
                    } elseif ($author_gender == 1) {
                        $author_avatar_src = "/images/avatar/1.png";
                    } elseif ($author_gender == 2) {
                        $author_avatar_src = "/images/avatar/2.png";
                    } else {
                        $author_avatar_src = "/images/avatar/default_avatar.png";
                    }
                }
            }
            $post_detail['author_avatar_path'] = $author_avatar_src;
            $post_image_raw = $post_detail['image'] ?? null;
            $post_image_path = '';

            if ($post_image_raw) {
                $decoded_images = json_decode($post_image_raw);
                $image_source = '';
                if (is_array($decoded_images) && !empty($decoded_images)) {
                    $image_source = $decoded_images[0];
                } else {
                    $image_source = $post_image_raw;
                }
                if (filter_var($image_source, FILTER_VALIDATE_URL)) {
                    $post_image_path = htmlspecialchars($image_source);
                } else {
                    $post_image_path = '/images/forum/' . htmlspecialchars($image_source);
                }
            }
            $post_detail['display_image_path'] = $post_image_path;
        }
        $stmt->close();
    } else {
        $_alert = "<div class='alert alert-danger'>Đã xảy ra lỗi khi tải bài viết. Vui lòng thử lại sau.</div>";
    }
} else if ($post_id === null && !isset($_GET['delete_comment_id'])) {
}

$comments = [];
?>





