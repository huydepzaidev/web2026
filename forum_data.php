<?php
// forum_data.php - Xá»­ lĂ½ dá»¯ liá»‡u diá»…n Ä‘Ă n vĂ  thĂ´ng tin ngÆ°á»i dĂ¹ng

// Báº­t hiá»ƒn thá»‹ lá»—i PHP Ä‘á»ƒ dá»… gá»¡ lá»—i (CHá»ˆ TRONG MĂ”I TRÆ¯á»œNG PHĂT TRIá»‚N!)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/connect.php'; // Äáº£m báº£o connect.php Ä‘Æ°á»£c include Ä‘á»ƒ cĂ³ $conn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Khá»Ÿi táº¡o cĂ¡c biáº¿n máº·c Ä‘á»‹nh
$is_logged_in = false;
$account_username = '';
$display_player_name = '';
$user_vnd = 0;
$user_avatar = '/images/avatar/0.png'; // Avatar máº·c Ä‘á»‹nh
$player_gender_for_avatar = 0;
$is_admin_for_avatar = 0;
$player_head_for_avatar = 0;
$player_name = $display_player_name;
$user_balance = $user_vnd;

function get_existing_avatar_url($avatar_file) {
    $avatar_file = basename((string)$avatar_file);
    $avatar_path = __DIR__ . '/images/avatar/' . $avatar_file;

    if ($avatar_file !== '' && is_file($avatar_path)) {
        return '/images/avatar/' . $avatar_file;
    }

    return '/images/avatar/0.png';
}

// Ghi log tráº¡ng thĂ¡i session khi forum_data.php Ä‘Æ°á»£c táº£i
error_log("DEBUG: forum_data.php - Session status: " . session_status());
error_log("DEBUG: forum_data.php - SESSION username: " . ($_SESSION['username'] ?? 'Not set'));
error_log("DEBUG: forum_data.php - SESSION user_id: " . ($_SESSION['user_id'] ?? 'Not set'));


if (isset($_SESSION['username'])) {
    $is_logged_in = true;
    $account_username = $_SESSION['username'];

    // Láº¥y user_id tá»« session (ráº¥t quan trá»ng cho cĂ¡c truy váº¥n sau nĂ y)
    // Äáº£m báº£o ráº±ng user_id Ä‘Æ°á»£c Ä‘áº·t trong session khi Ä‘Äƒng nháº­p
    if (!isset($_SESSION['user_id'])) {
        // Náº¿u user_id chÆ°a cĂ³ trong session, cá»‘ gáº¯ng láº¥y tá»« DB dá»±a trĂªn username
        $stmt_get_user_id = $conn->prepare("SELECT id FROM account WHERE username = ? LIMIT 1");
        if ($stmt_get_user_id) {
            $stmt_get_user_id->bind_param("s", $account_username);
            $stmt_get_user_id->execute();
            $result_get_user_id = $stmt_get_user_id->get_result();
            if ($row_user_id = $result_get_user_id->fetch_assoc()) {
                $_SESSION['user_id'] = $row_user_id['id'];
                error_log("DEBUG: forum_data.php - user_id Ä‘Æ°á»£c láº¥y tá»« DB vĂ  Ä‘áº·t vĂ o session: " . $_SESSION['user_id']);
            } else {
                error_log("DEBUG: forum_data.php - KhĂ´ng tĂ¬m tháº¥y user_id trong DB cho username: " . $account_username);
                $is_logged_in = false; // Náº¿u khĂ´ng tĂ¬m tháº¥y ID, coi nhÆ° chÆ°a Ä‘Äƒng nháº­p
            }
            $stmt_get_user_id->close();
        } else {
            error_log("Lá»—i prepare láº¥y user_id trong forum_data.php: " . $conn->error);
            $is_logged_in = false;
        }
    }


    $stmt_user_data = $conn->prepare("
        SELECT
            a.id, a.vnd, a.is_admin,
            p.name AS player_name, p.gender, p.head
        FROM
            account a
        LEFT JOIN
            player p ON a.id = p.account_id
        WHERE
            a.username = ?
        LIMIT 1
    ");

    if ($stmt_user_data) {
        $stmt_user_data->bind_param("s", $account_username);
        $stmt_user_data->execute();
        $result_user_data = $stmt_user_data->get_result();
        if ($user_row = $result_user_data->fetch_assoc()) {
            $user_vnd = $user_row['vnd'] ?? 0;
            $is_admin_for_avatar = (int) ($user_row['is_admin'] ?? 0);
            $_SESSION['is_admin'] = $is_admin_for_avatar;
            $display_player_name = $user_row['player_name'] ?? $account_username;
            $player_gender_for_avatar = $user_row['gender'] ?? 0;
            $player_head_for_avatar = $user_row['head'] ?? 0;

            // Äáº£m báº£o user_id Ä‘Æ°á»£c Ä‘áº·t trong session tá»« Ä‘Ă¢y náº¿u nĂ³ chÆ°a cĂ³
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['user_id'] = $user_row['id'];
                error_log("DEBUG: forum_data.php - user_id Ä‘Æ°á»£c Ä‘áº·t vĂ o session tá»« truy váº¥n chĂ­nh: " . $_SESSION['user_id']);
            }

            error_log("DEBUG: forum_data.php - Dá»¯ liá»‡u ngÆ°á»i dĂ¹ng: Admin=" . $is_admin_for_avatar . ", Gender=" . $player_gender_for_avatar . ", Head=" . $player_head_for_avatar);

            if ($is_admin_for_avatar == 1) {
                if ($player_gender_for_avatar == 0) {
                    $user_avatar = get_existing_avatar_url("10.png");
                } elseif ($player_gender_for_avatar == 1) {
                    $user_avatar = get_existing_avatar_url("11.png");
                } elseif ($player_gender_for_avatar == 2) {
                    $user_avatar = get_existing_avatar_url("12.png");
                } else {
                    $user_avatar = get_existing_avatar_url("12.png"); // Default for admin if gender is unexpected
                }
            } else {
                if ($player_head_for_avatar > 0) {
                    $user_avatar = get_existing_avatar_url((int)$player_head_for_avatar . ".png");
                } else {
                    if ($player_gender_for_avatar == 0) {
                        $user_avatar = get_existing_avatar_url("0.png");
                    } elseif ($player_gender_for_avatar == 1) {
                        $user_avatar = get_existing_avatar_url("1.png");
                    } elseif ($player_gender_for_avatar == 2) {
                        $user_avatar = get_existing_avatar_url("2.png");
                    } else {
                        $user_avatar = get_existing_avatar_url("0.png"); // Default for non-admin if head/gender is unexpected
                    }
                }
            }
            error_log("DEBUG: forum_data.php - ÄÆ°á»ng dáº«n avatar cuá»‘i cĂ¹ng: " . $user_avatar);
        } else {
            error_log("DEBUG: forum_data.php - KhĂ´ng tĂ¬m tháº¥y dá»¯ liá»‡u ngÆ°á»i dĂ¹ng cho username: " . $account_username);
            $is_logged_in = false; // Náº¿u khĂ´ng tĂ¬m tháº¥y dá»¯ liá»‡u, coi nhÆ° chÆ°a Ä‘Äƒng nháº­p
        }
        $stmt_user_data->close();
    } else {
        error_log("Lá»—i prepare láº¥y thĂ´ng tin ngÆ°á»i dĂ¹ng trong forum_data.php: " . $conn->error);
        $is_logged_in = false;
    }
} else {
    error_log("DEBUG: forum_data.php - SESSION username khĂ´ng Ä‘Æ°á»£c Ä‘áº·t. NgÆ°á»i dĂ¹ng chÆ°a Ä‘Äƒng nháº­p.");
}

// HĂ m láº¥y URL avatar cho bĂ i viáº¿t (khĂ´ng liĂªn quan trá»±c tiáº¿p Ä‘áº¿n avatar ngÆ°á»i dĂ¹ng hiá»‡n táº¡i)
function get_post_avatar_url($is_admin, $gender, $head, $is_pinned) {
    if ($is_pinned == 1) {
        return get_existing_avatar_url("6101.gif");
    }

    if ($is_admin == 1) {
        if ($gender == 0) {
            return get_existing_avatar_url("10.png");
        } elseif ($gender == 1) {
            return get_existing_avatar_url("11.png");
        } elseif ($gender == 2) {
            return get_existing_avatar_url("12.png");
        } else {
            return get_existing_avatar_url("12.png");
        }
    } else {
        if ($head > 0) {
            return get_existing_avatar_url((int)$head . ".png");
        } else {
            if ($gender == 0) {
                return get_existing_avatar_url("0.png");
            } elseif ($gender == 1) {
                return get_existing_avatar_url("1.png");
            } elseif ($gender == 2) {
                return get_existing_avatar_url("2.png");
            } else {
                return get_existing_avatar_url("0.png");
            }
        }
    }
}

// Pháº§n code dÆ°á»›i Ä‘Ă¢y khĂ´ng liĂªn quan trá»±c tiáº¿p Ä‘áº¿n avatar ngÆ°á»i dĂ¹ng mĂ  lĂ  cĂ¡c bĂ i viáº¿t
// NhÆ°ng tĂ´i sáº½ giá»¯ nguyĂªn Ä‘á»ƒ Ä‘áº£m báº£o tĂ­nh toĂ n váº¹n cá»§a file forum_data.php cá»§a báº¡n.
$sql_pinned = "
    SELECT
        p.id,
        p.tieude,
        p.username,
        p.created_at,
        p.ghimbai,
        pl.gender,
        pl.head
    FROM
        posts p
    LEFT JOIN
        account a ON p.username = a.username
    LEFT JOIN
        player pl ON a.id = pl.account_id
    WHERE
        p.ghimbai = 1
    ORDER BY
        p.created_at DESC";
$result_pinned = $conn->query($sql_pinned);

$pinned_posts = [];
if ($result_pinned) {
    while ($row = $result_pinned->fetch_assoc()) {
        $row['avatar_url'] = get_post_avatar_url($row['admin'] ?? 0, $row['gender'] ?? 0, $row['head'] ?? 0, $row['ghimbai'] ?? 0);
        $pinned_posts[] = $row;
    }
    $result_pinned->free();
} else {
    error_log("Lá»—i truy váº¥n bĂ i viáº¿t Ä‘Ă£ ghim: " . $conn->error);
}

$posts_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$sql_count_unpinned = "SELECT COUNT(*) AS total_posts FROM posts WHERE ghimbai = 0";
$result_count = $conn->query($sql_count_unpinned);
$total_unpinned_posts = 0;
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_unpinned_posts = $row_count['total_posts'];
    $result_count->free();
} else {
    error_log("Lá»—i truy váº¥n tá»•ng sá»‘ bĂ i viáº¿t chÆ°a ghim: " . $conn->error);
}

$total_pages = ceil($total_unpinned_posts / $posts_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
} elseif ($total_pages == 0) {
    $current_page = 1;
}

$offset = ($current_page - 1) * $posts_per_page;
if ($offset < 0) {
    $offset = 0;
}

$sql_unpinned = "
    SELECT
        p.id,
        p.tieude,
        p.username,
        p.created_at,
        p.ghimbai,
        pl.gender,
        pl.head
    FROM
        posts p
    LEFT JOIN
        account a ON p.username = a.username
    LEFT JOIN
        player pl ON a.id = pl.account_id
    WHERE
        p.ghimbai = 0
    ORDER BY
        p.created_at DESC
    LIMIT ?, ?";

$stmt_unpinned = $conn->prepare($sql_unpinned);
$unpinned_posts = [];
if ($stmt_unpinned) {
    $stmt_unpinned->bind_param("ii", $offset, $posts_per_page);
    $stmt_unpinned->execute();
    $result_unpinned = $stmt_unpinned->get_result();
    while ($row = $result_unpinned->fetch_assoc()) {
        $row['avatar_url'] = get_post_avatar_url($row['admin'] ?? 0, $row['gender'] ?? 0, $row['head'] ?? 0, $row['ghimbai'] ?? 0);
        $unpinned_posts[] = $row;
    }
    $result_unpinned->free();
    $stmt_unpinned->close();
} else {
    error_log("Lá»—i prepare truy váº¥n bĂ i viáº¿t chÆ°a ghim: " . $conn->error);
}
?>

