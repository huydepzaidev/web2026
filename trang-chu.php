<?php
include_once 'connect.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Chú Bé Rồng Online</title>
    <link rel="icon" href="favicon.ico?v=3" type="image/x-icon" />
    <link rel="shortcut icon" href="favicon.ico?v=3" type="image/x-icon" />
    <link rel="apple-touch-icon" href="favicon-32x32.png?v=3" />
    <link rel="icon" type="image/png" href="favicon-32x32.png?v=3" sizes="32x32" />
    <meta name="keywords" content="Chú Bé Rồng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong" />
    <meta name="description" content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngoc Rong Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!" />
    <meta http-equiv="refresh" content="600" />
    <meta name="robots" content="INDEX,FOLLOW" />

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="view/static/css/template.css?v=1.10">
    <link rel="stylesheet" href="view/static/css/eff.css?v=1.00">
    <link rel="stylesheet" href="view/static/css/w3.css?v=1.01">
    <script src="view/static/js/disable_devtools.js"></script>
    <link rel="stylesheet" href="view/static/css/styleSheet.css?v=1.1">
    <script src="view/static/js/ThreeCanvas.js" type="text/javascript"></script>
    <script src="view/static/js/Snow3d.js" type="text/javascript"></script>
    <script src="view/static/js/animation.js?v4" type="text/javascript"></script>
</head>
<body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
        <a href="#" id="backTop"><img id='backTopimg' src='images/favicon-32x32.png' alt='top' /> </a>

        <div class="div-12">
            <img height=12 src="images/18-1.png" style="vertical-align: middle;" />
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
                    <a href="./" title="game bảy viên Chú Bé Rồng Online">
                        <img height=90 src="images/logo_sk_he.png" alt="game bảy viên Chú Bé Rồng Online" /></a>
                </h1>
          <div id="top">
    <div class="link-more">
        <div class="h">
            <div class="bg_noel"></div>
            <div class="menu2">
                <table width="100%" cellspacing="4">
                    <tr class="menu">
                        <td id='selected'>
                            <a href="trang-chu">Trang Chủ</a>
                        </td>
                        <td>
                            <a href="gioi-thieu">Giới Thiệu</a>
                        </td>
                        <td>
                            <a href="forum" title="Diễn Đàn">Diễn Đàn</a>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($box_zalo_url ?? 'https://zalo.me/g/njvxgh490'); ?>" target="_blank">Box Zalo</a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bg_top_22">
                <img src="images/banner_Summer.png" width="100%">
            </div>
        </div>
    </div>
</div>
<div class="download">
    <div class="bg-content text-center">
        <div id="columns" style="text-align:center; display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">						
            <figure>
                <a href="<?php echo htmlspecialchars($download_java); ?>" <?= is_external_url($download_java) ? 'target="_blank" rel="noopener noreferrer"' : 'download' ?> title="CHÚ BÉ RỒNG ONLINE - JAVA">
                    <img height="35" src="images/jar.png" alt="CHÚ BÉ RỒNG ONLINE - JAVA">
                </a>
            </figure>
            
            <figure>
                <a href="<?php echo htmlspecialchars($download_android); ?>" <?= is_external_url($download_android) ? 'target="_blank" rel="noopener noreferrer"' : 'download' ?> title="CHÚ BÉ RỒNG ONLINE - ANDROID">
                    <img height="35" src="images/play.png" alt="CHÚ BÉ RỒNG ONLINE - ANDROID">
                </a>
            </figure>
            
            <figure>
                <a href="<?php echo htmlspecialchars($download_windows); ?>" <?= is_external_url($download_windows) ? 'target="_blank" rel="noopener noreferrer"' : 'download' ?> title="CHÚ BÉ RỒNG ONLINE - PC / WINDOWS">
                    <img height="35" src="images/pc.png" alt="CHÚ BÉ RỒNG ONLINE - PC / WINDOWS">
                </a>
            </figure>

            <figure>
                <a href="<?php echo htmlspecialchars($download_iphone); ?>" <?= is_external_url($download_iphone) ? 'target="_blank" rel="noopener noreferrer"' : 'download' ?> title="CHÚ BÉ RỒNG ONLINE - IPHONE / IOS">
                    <img style="margin-bottom:0px" height="35" src="images/ip.png" alt="CHÚ BÉ RỒNG ONLINE - IPHONE / IOS">
                </a>
            </figure>
        </div>
    </div>
</div>
<div class="bg-content">
                    <div class="title">
                        <h4>Giới Thiệu</h4>
                    </div>
                    <div class="content">
                        <p>Ngọc Rồng Online là Trò Chơi Trực Tuyến với cốt truyện xoay quanh bộ truyện tranh 7 viên Ngọc Rồng. Người chơi sẽ hóa thân thành một trong những anh hùng của 3 hành tinh: Trái Đất, Xayda, Namếc. Cùng luyện tập, tăng cường sức mạnh và kỹ năng. Đoàn kết cùng chiến đấu chống lại các thế lực hung ác. Cùng nhau tranh tài.<br>
                            Đặc điểm nổi bật:<br>
                            - Thể loại hành động, nhập vai. Trực tiếp điều khiển nhân vật hành động. Dễ chơi, dễ điều khiển nhân vật. Đồ họa sắc nét. Có phiên bản đồ họa cao cho điện thoại mạnh và phiên bản pixel cho máy cấu hình thấp.<br>
                            - Cốt truyện bám sát nguyên tác. Người chơi sẽ gặp tất cả nhân vật từ Bunma, Quy lão kame, Jacky-chun, Tàu Pảy Pảy... cho đến Fide, Pic, Poc, Xên, Broly, đội Bojack.<br>
                            - Đặc điểm nổi bật nhất: Tham gia đánh doanh trại độc nhãn. Tham gia đại hội võ thuật. Tham gia săn lùng ngọc rồng để mang lại điều ước cho bản thân.<br>
                            - Tương thích tất cả các dòng máy trên thị trường hiện nay: Máy tính PC, Điện thoại di động Nokia Java, Android, iPhone, Windows Phone, và máy tính bảng Android, iPad.</p>
                        <p class="content-p">Cơ Bản</p>
                        <p class="text-center">
                            <img alt="" src="images/gif//gif_maphongba.gif">&nbsp;
                            <img alt="" src="images/gif//gif_gif_Saiyain.gif">&nbsp;
                            <img alt="" src="images/gif/gif_supber_kame.gif">&nbsp;
                        </p>

                        <p class="content-p">VIP</p>
                        <p class="text-center">
                            <img alt="" src="images/gif//gif_maphongba_VIP.gif">&nbsp;
                            <img alt="" src="images/gif//gif_gif_Saiyain_VIP.gif">&nbsp;
                            <img alt="" src="images/gif/gif_supber_kame_VIP.gif">&nbsp;
                        </p>
                        <p class="text-center">
                            <a href="?c=skill">Xem thêm</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-content">
                <div>
                    <div class="title">
                        <h4>Hướng Dẫn Tân Thủ</h4>
                    </div>
                    <div class="content">
                        <p><strong>1. Đăng ký tài khoản</strong></p>

                        <p>Ngọc Rồng Online sử dụng Tài Khoản riêng, không chung với bất kỳ Trò Chơi nào khác.<br>
                            Bạn có thể đăng ký tài khoản miễn phí ngay trong game, hoặc trên trang Diễn Đàn.<br>
                            Khi đăng ký, bạn nên sử dụng đúng số điện thoại hoặc email thật của mình. Nếu sử dụng thông tin sai, người có số điện thoại hoặc email thật sẽ có thể lấy mật khẩu của bạn.<br>
                            Số điện thoại và email của bạn sẽ không hiện ra cho người khác thấy. Admin không bao giờ hỏi mật khẩu của bạn.</p>

                        <p><strong>2. Hướng dẫn điều khiển</strong></p>

                        <p>Đối với máy bàn phím: Dùng phím mũi tên, phím số, để điều khiển nhân vật. Phím chọn giữa để tương tác.<br>
                            Đối với máy cảm ứng: Dùng tay chạm vào màn hình cảm ứng để di chuyển. Chạm nhanh 2 lần vào 1 đối tượng để tương tác.<br>
                            Đối với PC: Dùng chuột, click chuột phải để di chuyển, click chuột trái để chọn, click đôi vào đối tượng để tương tác</p>

                        <p><strong>3. Một số thông tin căn bản</strong></p>

                        <p>- Đậu thần dùng để tăng KI và HP ngay lập tức.<br>
                            - Bạn chỉ mang theo người được 10 hạt đậu. Nếu muốn mang nhiều hơn, hãy xin từ bạn bè trong Bang.<br>
                            - Tất cả các sách kỹ năng đều có thể học miễn phí tại Quy Lão Kame, khi bạn có đủ điểm tiềm năng.<br>
                            - Bạn không thể bay, dùng kỹ năng, nếu hết KI.<br>
                            - Tấn công quái vật cùng bạn bè trong Bang sẽ mang lại nhiều điểm tiềm năng hơn đánh một mình.<br>
                            - Tập luyện với bạn bè tại khu vực thích hợp sẽ mang lại nhiều điểm tiềm năng hơn đánh quái vật.<br>
                            - Khi được nâng cấp, đậu thần sẽ phục hồi nhiều HP và KI hơn.<br>
                            - Vào trò chơi đều đặn mỗi ngày để nhận được Ngọc miễn phí.<br>
                            - Đùi gà sẽ phục hồi 100% HP, KI. Cà chua phục hồi 100% KI. Cà rốt phục hồi 100% HP.<br>
                            - Cây đậu thần kết một hạt sau một thời gian, cho dù bạn đang offline.<br>
                            - Sau 3 ngày không tham gia trò chơi, bạn sẽ bị giảm sức mạnh do lười luyện tập.<br>
                            - Bạn sẽ giảm thể lực khi đánh quái, nhưng sẽ tăng lại thể lực khi không đánh nữa.</p>
                    </div>
                </div>
            </div>
            <div class="bg-content">
                <div>
                    <div class="title">
                        <h4>Bạn nên tải phiên bản nào?</h4>
                    </div>
                    <div class="content">
                        <p>Nếu bạn dùng điện thoại Nokia cũ, có bàn phím như Nokia 6300, Nokia E72, Nokia X2, Nokia C2, Hãy tải bản JAVA</p>

                        <p>Nếu bạn dùng máy cảm ứng sử dụng Android như: Samsung Galaxy Y, HTC, LG, Sky, HKPhone. Hãy tải bản Android APK hoặc Android Playstore đều được.</p>

                        <p>Nếu bạn dùng điện thoại cảm ứng của NOKIA Lumia, hoặc các máy HTC chạy Windows Phone, hãy tải bản cho Windows Phone.</p>

                        <p>Nếu bạn dùng máy vi tính cá nhân, laptop chạy Windows XP - Windows 7, hãy tải bản PC.</p>

                        <p>Nếu bạn dùng iPhone, iPod, iPad, hãy tải bản iPhone Appstore. Nếu bạn biết chắc rằng máy mình đã jailbreak, có cài AppSync hoặc AppstoreVN, hãy cài từ bản iPhone jailbreak để tốc độ nhanh hơn.</p>
                    </div>
                </div>
            </div>
        </div>
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
    <div class="copyright" style="line-height: 7px">
        <p>Giấy phép thiết lập Mạng Xã Hội trên mạng số: 374/GP-BTTTT <br><br>do Bộ Thông Tin và Truyền Thông cấp ngày: 07/08/2015</p>
        Bản Quyền thuộc về Gomobi
        - <a href="http://teamobi.com/dieukhoan.htm"><small>Điều Khoản Sử Dụng</small></a>
        <center><br><select name="forma" onchange="location = this.options[this.selectedIndex].value;">
 <option value="http://wap.teamobi.com/game/30">Avatar</option>
 <option value="http://am.teamobi.com/">Avatar Musik</option>
 <option value="http://wap.teamobi.com/game/118">Mobi Army A</option>
 <option value="http://wap.teamobi.com/game/31">Khí Phách Anh Hùng</option>
 <option value="http://ninjaschool.vn/">Ninja School Online</option>
 <option value="./">Chú Bé Rồng</option>
 <option value="http://knightageonline.com/">Knight Age Online</option>
 <option value="http://haitactihon.com/">Hải Tặc Tí Hon</option>
 <option value="http://sonthuyphantranh.com/">Sơn Thủy Phân Tranh</option>
</select></center>    </div>
</div>
<script type="text/javascript">
    (function($) {

        "use strict"

        $(function() {
            if ($('#backTop').length) {
                var scrollTrigger = 100, // px
                    backToTop = function() {
                        var scrollTop = $(window).scrollTop();
                        if (scrollTop > scrollTrigger) {
                            $('#backTop').addClass('show');
                        } else {
                            $('#backTop').removeClass('show');
                        }
                    };
                backToTop();
                $(window).on('scroll', function() {
                    backToTop();
                });
                $('#backTop').on('click', function(e) {
                    e.preventDefault();
                    $('html,body').animate({
                        scrollTop: 0
                    }, 700);
                });
            }

        });

    })(jQuery);
</script>
</body>

</html>




