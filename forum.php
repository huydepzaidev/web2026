<?php
require_once 'settings.php';
require_once 'forum_data.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Di&#7877;n &#272;&#224;n - Ch&#250; B&#233; R&#7891;ng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <meta name="keywords" content="Chú Bé Rồng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong" />
    <meta name="description" content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngọc Rồng Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!" />
    <meta http-equiv="refresh" content="600" />
    <meta name="robots" content="INDEX,FOLLOW" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/view/static/css/template.css?v=1.10">
    <link rel="stylesheet" href="/view/static/css/eff.css?v=1.00">
    <link rel="stylesheet" href="/view/static/css/w3.css?v=1.01">
    <link rel="stylesheet" href="/view/static/css/styleSheet.css?v=1.1">
    <link rel="stylesheet" href="/view/static/css/forum.css?v=1.4">
    <script src="https://www.google.com/recaptcha/api.js?render="></script>
	<script src="/view/static/js/disable_devtools.js"></script>
    <style>
        #user-info {
            display: inline-block;
            min-width: 220px;
            max-width: 360px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 8px;
            box-sizing: border-box;
            text-align: center;
        }

        #user-info .user-avatar {
            display: block;
            width: 58px;
            height: 58px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, .75);
            background: #fff;
            object-fit: cover;
            margin: 0 auto 6px;
        }

        #user-info .user-details {
            color: #fff;
            font-weight: 700;
            line-height: 1.6;
            text-align: center;
        }

        #user-info .user-details span {
            display: block;
            text-align: center;
        }

        #user-info .account-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        #user-info .secondary-actions {
            margin-top: 6px;
        }

        #user-info .account-actions a {
            display: inline-block;
            min-width: 104px;
            padding: 5px 9px;
            border: 1px solid #8a1d12;
            border-radius: 5px;
            background: linear-gradient(180deg, #e84a35 0%, #b7281d 100%);
            color: #fff !important;
            font-weight: 700;
            text-decoration: none;
            box-sizing: border-box;
        }

        #user-info .account-actions a:hover {
            filter: brightness(1.08);
        }

        .box_inputboxx {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
        <a href="#" id="backTop"><img id='backTopimg' src='/images/favicon-32x32.png' alt='top' /> </a>

        <div class="div-12">
            <img height=12 src="/images/18-1.png" style="vertical-align: middle;" />
            <span style="vertical-align: middle;">Dành cho người chơi trên 18 tuổi. Chơi quá 180 phút mỗi ngày sẽ hại sức khỏe.
            </span>
        </div>
        <div class="left_top"></div>
        <div class="bg_top">
            <div class="right_top"></div>
        </div>
        <div class="body-content">
            <div class="bg-content2">
                <h1 class="a">
                    <a href="/" title="game bảy viên Chú Bé Rồng Online">
                        <img height=90 src="/images/logo_sk_he.png" alt="game bảy viên Chú Bé Rồng Online" /></a>
                </h1>
                <div id="top">
                    <div class="link-more">
                        <div class="h">
                            <div class="bg_noel"></div>
                            <div class="menu2">
                                <table width="100%" cellspacing="4">
                                    <tr class="menu">
                                        <td>
                                            <a href="/trang-chu">Trang Chủ</a>
                                        </td>
                                        <td>
                                            <a href="/gioi-thieu">Giới Thiệu</a>
                                        </td>
                                        <td>
                                            <a href="/forum" title="Diễn Đàn">Diễn Đàn</a>
                                        </td>
                                        <td>
                                                    <a href="<?php echo htmlspecialchars($box_zalo_url ?? 'https://zalo.me/g/njvxgh490'); ?>" target="_blank">Box Zalo</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var currentUrl = window.location.pathname;
                                    document.querySelectorAll(".menu a").forEach(function(link) {
                                        if (link.getAttribute("href") === currentUrl) {
                                            document.querySelector("#selected")?.removeAttribute("id");
                                            link.parentElement.id = "selected";
                                        }
                                    });
                                });
                            </script>
                            <div class="body">
                                <div class="box_inputboxx" style="width:100%">
    <?php if ($is_logged_in): ?>
        <div id="user-info" style="color:white; text-align:center;">
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
            <div class="user-details">
                <span>Xin chào: <?php echo htmlspecialchars($display_player_name); ?></span>
                <span style="white-space: nowrap; color: yellow; font-weight: bold;">Số dư: <?php echo number_format($user_vnd); ?> VND</span>
                <div class="account-actions">
                    <?php if ($is_admin_for_avatar === 1): ?>
                    <a href="/admin/" style="color:#ffe26f;font-weight:bold;">Control Panel</a>
                    <?php endif; ?>
                    <a href="/app/doi-mat-khau">Đổi mật khẩu</a>
                    <a href="/app/logout">Đăng xuất</a>
                </div>
                <div class="account-actions secondary-actions">
                    <a href="/bang-xep-hang">Top Sức Mạnh</a>
                    <a href="/top-nap">Top Nạp</a>
                    <a href="/top-nhiem-vu">Top Nhiệm Vụ</a>
                </div>

                <div class="center-buttons">
    <div style="display: flex; justify-content: center; align-items: center; margin-top: 5px;">
        <!-- <a href="/app/nap-vang" style="color: cyan; transform: translateX(-21px); display: inline-block;">Nạp Vàng</a>
        <a href="/app/nap-ngoc" style="color: cyan; margin-left: 0px;">Nạp Tiền</a> -->
    </div>
</div>
        </div>
        </div>
<?php else: ?>
    <div id="auth-buttons" class="box_button_login" style="width:100%; text-align:center; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <a id="tab-register" href="/app/login" style="flex-shrink: 0;">
            <button class="w3-button w3-red w3-small w3-hover-green">Đăng Nhập</button>
        </a>
        <a id="tab-leaderboard" href="bang-xep-hang" style="flex-shrink: 0;">
            <button class="w3-button w3-red w3-small w3-hover-green">Top Sức Mạnh</button>
        <!-- </a>
        <a id="tab-top-nap" href="top-nap" style="flex-shrink: 0;">
            <button class="w3-button w3-red w3-small w3-hover-green">Top Nạp Tiền</button>
        </a> -->
		</a>
        <a id="tab-top-nap" href="top-nhiem-vu" style="flex-shrink: 0;">
            <button class="w3-button w3-red w3-small w3-hover-green">Top Nhiệm Vụ</button>
        </a>
    </div>
    <?php endif; ?>
</div>


                                <br>

                                <?php if (!$is_logged_in): ?>
                                <div id="box_login_ads">
    <div id="columns" style="text-align:center">
        <figure>
            <a href="/down/JAR.jar?v=20260213-1348" download> <img height="35" src="/images/jar.png" alt="CHÚ BÉ RỒNG ONLINE"></a>
            <br>
            </a>
            <figcaption>
                <span style="color:rgb(209, 9, 50);">237</span> <a href="#" onclick="if (!window.__cfRLUnblockHandlers) return false; openWinjad()" title="CHÚ BÉ RỒNG ONLINE" target="_blank" data-cf-modified-6e49640f1ec245fe06a7b8fa-="">
                </a>
                <br> <br>
            </figcaption>
        </figure>

        <!-- <figure>
            <a href="/down/NRO.apk?v=20260603-2358" title="CHÚ BÉ RỒNG ONLINE">
                <img height="35" src="/images/android.png" alt="CHÚ BÉ RỒNG ONLINE">
            </a>
            <figcaption><span style="color:rgb(209, 9, 50);">244</span>
                <br>
                <a href="/?c=huong-dan"></a>
            </figcaption>
        </figure> -->

        <figure>
            <a href="/down/NRO.apk?v=20260603-2358" title="CHÚ BÉ RỒNG ONLINE">
                <img height="35" src="/images/play.png" alt="CHÚ BÉ RỒNG ONLINE">
            </a>
            <figcaption><span style="color:rgb(209, 9, 50);">244</span>
                <br> <br>
            </figcaption>
        </figure>

        <figure>
            <a href="/down/PC.rar?v=20260610-2022" title="CHÚ BÉ RỒNG ONLINE">
                <img height="35" src="/images/pc.png" alt="CHÚ BÉ RỒNG ONLINE">
            </a>
            <figcaption><span style="color:rgb(209, 9, 50);">244</span>
                <br> <br>
            </figcaption>
        </figure>

        <figure>
            <a href="<?php echo htmlspecialchars($testflight_url ?? 'https://testflight.apple.com/join/FWEJoZEB'); ?>" title="CHÚ BÉ RỒNG ONLINE">
                <img style="margin-bottom:0px" height="35" src="/images/ip.png" alt="CHÚ BÉ RỒNG ONLINE">
            </a>
            <figcaption>
                <a href="<?php echo htmlspecialchars($testflight_url ?? 'https://testflight.apple.com/join/FWEJoZEB'); ?>">Testflight 1</a> <br> <br>
            </figcaption>
        </figure>
        <figure>
            <!-- <a href="/Users/Payments" title="CHÚ BÉ RỒNG ONLINE">
                <img src="/images/napngoc.png" height="35">
            </a> -->
            <!-- <figcaption>Nạp Ngọc</figcaption> </figure> -->

                                </div>
                                </div>
                                <?php endif; ?>
                                            <div class="body">
                                                <div id="box_forums" class="beta_test">
                                                    <div class="box_list_chuyenmuc">
                                                        <?php if (!empty($pinned_posts)) : ?>
                                                            <div id="stick">
                                                                <?php foreach ($pinned_posts as $post) : ?>
                                                                    <div class="forum-post-item">
                                                                        <div class="post-avatar-wrapper">
                                                                            <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="Post Avatar">
                                                                        </div>
                                                                        <div class="post-content">
                                                                            <a class="post-title-link" href="bai-viet.php?id=<?php echo htmlspecialchars($post['id']); ?>" title="<?php echo htmlspecialchars($post['tieude']); ?>">
                                                                                <?php echo htmlspecialchars($post['tieude']); ?> <img src="/images/gif/hot.gif" class="hot-icon" alt="Hot">
                                                                            </a>
                                                                            <div class="post-meta-info">
                                                                                bởi <a href="javascript:void(0)"><?php echo htmlspecialchars($post['username']); ?></a>
                                                                                <span style="color:red">☆</span>
                                                                                <i>
                                                                                    <?php echo date_format(date_create($post['created_at']), 'd/m/Y H:i'); ?>
                                                                                </i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($unpinned_posts)) : ?>
                                                            <?php foreach ($unpinned_posts as $post) : ?>
                                                                <div class="forum-post-item">
                                                                    <div class="post-avatar-wrapper">
                                                                        <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="User Avatar">
                                                                    </div>
                                                                    <div class="post-content">
                                                                        <a class="post-title-link" href="bai-viet.php?id=<?php echo htmlspecialchars($post['id']); ?>" title="<?php echo htmlspecialchars($post['tieude']); ?>">
                                                                            <?php echo htmlspecialchars($post['tieude']); ?>
                                                                        </a>
                                                                        <div class="post-meta-info">
                                                                            bởi <a href="javascript:void(0)"><?php echo htmlspecialchars($post['username']); ?></a>
                                                                            <span style="color:red">☆</span>
                                                                            <i>
                                                                                <?php echo date_format(date_create($post['created_at']), 'd/m/Y H:i'); ?>
                                                                            </i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>

                                                        <?php if ($total_pages > 1) : ?>
                                                        <div class="pagination-container">
                                                            <div class="pagination">
                                                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                                                    <a href="?page=<?php echo $i; ?>" class="pagination-item <?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <br>
                                                    <div class="bg_tree"></div>
                                                    <div class="foot_bg"></div>
                                                    <div class="left_b_bottom">
                                                        <div class="right_b_bottom">
                                                            <div class="footer">
                                                                <div class="left_bottom"></div>
                                                                <div class="right_bottom"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="copyright" style="line-height: 13px">
                                                        <p>Giấy phép thiết lập Mạng Xã Hội trên mạng số: 374/GP-BTTTT <br><br>do Bộ Thông Tin và Truyền Thông cấp ngày: 07/08/2015</p>
                                                        Bản Quyền thuộc về Tuổi Thơ
                                        </div>
                                            <script src="/view/static/js/ThreeCanvas.js" type="text/javascript"></script>
                                            <script src="/view/static/js/Snow3d.js" type="text/javascript"></script>
                                            <script src="/view/static/js/animation.js?v5" type="text/javascript"></script>
                                            <script src="/view/static/js/rocket-loader.min.js" data-cf-settings="3248e74b3f0d3f240922716b-|49" defer></script>
                                            <script>
                                                $(document).ready(function() {
                                                    var lastPostTime = 0;
                                                    $("form[name='loginform'], form[name='registerform']").submit(function(event) {
                                                        event.preventDefault();
                                                        var now = Date.now();
                                                        if (now - lastPostTime < 10000) {
                                                            var secondsLeft = Math.ceil((10000 - (now - lastPostTime)) / 1000);
                                                            $("#comment_error").css("color", "red").text("Bạn chỉ có thể post mỗi 10 giây. Vui lòng chờ " + secondsLeft + " giây.");
                                                            return;
                                                        }

                                                        var form = $(this);
                                                        var formData = form.serialize();
                                                        var action = form.attr("id");
                                                        var csrfToken = $("#csrf_token").val();

                                                        formData += '&csrf_token=' + csrfToken + '&action=' + action;

                                                        $.post("/Api/Auth", formData)
                                                            .done(function(response) {
                                                                if (response.status === "success") {
                                                                    $("#comment_error").css("color", "green").text(response.message);
                                                                    lastPostTime = Date.now();
                                                                    if ($("#authModal").length) {
                                                                        $("#authModal").modal("hide");
                                                                    }
                                                                    setTimeout(function() {
                                                                        window.location.reload();
                                                                    }, 2000);
                                                                } else {
                                                                    $("#comment_error").css("color", "red").text(response.message);
                                                                }
                                                            })
                                                            .fail(function(jqXHR) {
                                                                var errorMessage = (jqXHR.responseJSON && jqXHR.responseJSON.message) || "Vui lòng thử lại trong ít phút nữa.";
                                                                $("#comment_error").css("color", "red").text(errorMessage);
                                                            });
                                                    });
                                                });
                                                function showTab(tab) {
                                                }
                                            </script>
</body>
</html>


