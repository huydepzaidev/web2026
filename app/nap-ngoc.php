<?php
session_start();
include_once '../connect.php';
include_once '../forum_data.php';
include_once '../data_nap_the.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Trang Chủ - Chú Bé Rồng Onlines - Ngọc Rồng Online</title>
    <meta name="keywords" content="Chú Bé Rồng Online,ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong" />
    <meta name="description" content="Website chính thức của Chú Bé Rồng Online – Game Bay Vien Ngọc Rồng Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!" />
    <meta http-equiv="refresh" content="600" />
    <meta name="robots" content="INDEX,FOLLOW" />

    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="shortcut icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/view/static/css/template.css?v=1.10">
    <link rel="stylesheet" href="/view/static/css/eff.css?v=1.00">
    <link rel="stylesheet" href="/view/static/css/w3.css?v=1.01">
    <link rel="stylesheet" href="/view/static/css/styleSheet.css?v=1.1">
	<link rel="stylesheet" href="/view/static/css/forum.css?v=1.1">
	<script src="/view/static/js/disable_devtools.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY_HERE"></script>
</head>

<body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
        <a href="#" id="backTop"><img id='backTopimg' src='/images/favicon-32x32.png' alt='top' /> </a>

        <div class="div-12">
            <img height=12 src="/images/12.png" style="vertical-align: middle;" />
            <span style="vertical-align: middle;">Dành cho người chơi trên 12 tuổi. Chơi quá 180 phút mỗi ngày sẽ hại sức khỏe.
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
                            <div class="h">
                                <div class="menu2">
                                    <table width="100%" cellspacing="4">
                                        <tr class="menu">
                                            <td>
                                                <a href="/Trang-Chu">Trang Chủ</a>
                                            </td>
                                            <td>
                                                <a href="/Gioi-Thieu">Giới Thiệu</a>
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
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var currentUrl = window.location.pathname;
                                    document.querySelectorAll(".menu a").forEach(function(link) {
                                        var linkHref = link.getAttribute("href");
                                        if (linkHref === "/Trang-Chu" && currentUrl === "/") {
                                            document.querySelector("#selected")?.removeAttribute("id");
                                            link.parentElement.id = "selected";
                                        }
                                        else if (linkHref === currentUrl) {
                                            document.querySelector("#selected")?.removeAttribute("id");
                                            link.parentElement.id = "selected";
                                        }
                                    });
                                });
                            </script>
                            <div class="body">
                                <div class="box_inputboxx" style="width:100%">
                                    <?php if ($is_logged_in) : ?>
                                        <div id="user-info" style="color:white; text-align:center; padding: 10px; background-color: #f38500; border-radius: 8px;">
                                            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                                            <div class="user-details"> <br>
                                                <span style="font-weight: bold;">Xin chào: <?php echo htmlspecialchars($display_player_name); ?></span><br>
                                                <span style="white-space: nowrap; color: yellow; font-weight: bold;">Số dư: <?php echo number_format($user_vnd, 0, ',', '.'); ?> VND</span><br>
                                                <a href="/app/change-password" style="color: cyan;">Đổi mật khẩu</a> <br>
                                                <a href="/app/logout" style="color: cyan;">Đăng xuất</a> <br>

                                                <div class="center-buttons">
                                                    <div style="display: flex; justify-content: center; align-items: center; margin-top: 5px;">
                                                        <!-- <a href="/app/nap-vang" style="color: cyan; transform: translateX(-21px); display: inline-block;">Nạp Vàng</a>
                                                        <a href="/app/nap-ngoc" style="color: cyan; margin-left: 0px;">Nạp Tiền</a> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="box_button_login" style="width:100%; position: relative; text-align:center;">
                                            <a id="tab-login-btn" href="/app/login">
                                                <button class="w3-button w3-red w3-small w3-hover-green">Đăng nhập</button>
                                            </a>
                                            <a id="tab-register-btn" href="/app/register">
                                                <button class="w3-button w3-blue w3-small w3-hover-green">Đăng ký</button>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <br>
                                <div class="body">
                                    <table width="100%" border="0" cellspacing="0">
                                        <tbody>
                                            <tr class="menu1">
                                                <td id="recharge-selected-tab" style="width:50%; background-color: #ff5601;">Nạp Tiền Tự Động</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div style="text-align:center; margin-bottom: 20px;">
                                        <button id="btnCard" class="active-recharge-btn" style="padding: 10px 20px; background-color: #ff5601; color: #fff; border: none; border-radius: 4px; margin-right: 10px;">Nạp Thẻ Cào Tự Động</button>
                                        <button id="btnTransfer" class="inactive-recharge-btn" style="padding: 10px 20px; background-color: #ccc; color: #000; border: none; border-radius: 4px;">Chuyển Khoản</button>
                                    </div>

                                    <div id="cardSection">
                                        <div class="box_list_chuyenmuc">
                                            <div class="box_midss">
                                                <div class="box_detai" style="padding:5px;">
                                                    <center>
                                                        <b style="color:red">Vui lòng nhập đầy đủ thông tin</b>
                                                        <br>
                                                        <div id="comment_error" style="color:red;"></div>
                                                    </center>
                                                    <form id="cardPaymentForm" method="post" action="/Api/Card">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                        <table style="width:100%; max-width:500px; margin:0 auto;">
                                                            <tbody>
                                                                <tr>
                                                                    <td><b>Loại Thẻ</b></td>
                                                                    <td>
                                                                        <select id="cardType" name="cardType" style="width:100%; border-radius: 4px; border: 1px solid #CCC; padding: 2px;" required>
                                                                            <option value="VIETTEL" selected>VIETTEL</option>
                                                                            <option value="VINAPHONE">VINAPHONE</option>
                                                                            <option value="MOBIPHONE">MOBIPHONE</option>
                                                                            <option value="VNMOBI">VNMOBI</option>
                                                                            <option value="VCOIN">VCOIN</option>
                                                                            <option value="ZING">ZING</option>
                                                                            <option value="GATE">GATE</option>
                                                                            <option value="GARENA">GARENA</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Số tiền</b></td>
                                                                    <td>
                                                                        <select id="amount" style="width:100%; border-radius: 4px; border: 1px solid #CCC; padding: 2px;" name="amount" required>
                                                                            <option value="">Chọn mệnh giá</option>
                                                                            <option value="10000">10.000</option>
                                                                            <option value="20000">20.000</option>
                                                                            <option value="30000">30.000</option>
                                                                            <option value="50000">50.000</option>
                                                                            <option value="100000">100.000</option>
                                                                            <option value="200000">200.000</option>
                                                                            <option value="300000">300.000</option>
                                                                            <option value="500000">500.000</option>
                                                                            <option value="1000000">1.000.000</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Mã Thẻ</b></td>
                                                                    <td><input type="text" id="cardPin" name="cardPin" style="width:100%;" required></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Số Seri</b></td>
                                                                    <td><input type="text" id="cardSerial" name="cardSerial" style="width:100%;" required></td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td style="text-align:center; padding-top:10px;">
                                                                        <input class="w3-button w3-red" type="submit" value="Xác Nhận">
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </form>
                                                    <br>
                                                    <hr>
                                                    <h3>Lịch Sử Nạp Thẻ</h3>
                                                    <table width="100%" border="1" cellspacing="0" style="width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
                                                        <thead>
                                                            <tr style="background-color: #b65d2f; color: white; font-weight: bold;">
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Thời gian</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Loại thẻ</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Mệnh giá khai báo</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Mệnh giá thực nhận</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (!empty($history_card_payments)) : ?>
                                                                <?php foreach ($history_card_payments as $payment) : ?>
                                                                    <tr style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: black;"><?php echo htmlspecialchars($payment['created_at']); ?></td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: black;"><?php echo htmlspecialchars($payment['card_telco']); ?></td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: black;"><?php echo number_format($payment['declared_amount'], 0, ',', '.') . ' VNĐ'; ?></td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: black;">
                                                                            <?php
                                                                            if (strpos($payment['status_text'], 'Thành công') !== false) {
                                                                                echo number_format($payment['detected_value'], 0, ',', '.') . ' VNĐ';
                                                                            } elseif ($payment['status_text'] === 'Sai mệnh giá') {
                                                                                echo 'Sai mệnh giá (Thực tế: ' . number_format($payment['detected_value'], 0, ',', '.') . ' VNĐ)';
                                                                            } else {
                                                                                echo 'Đang chờ xử lý';
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">
                                                                            <?php
                                                                                $status_text = htmlspecialchars($payment['status_text']);
                                                                                if (strpos($status_text, 'Thành công') !== false) {
                                                                                    echo '<span style="color: green; font-weight: bold;">' . $status_text . '</span>';
                                                                                } elseif (strpos($status_text, 'Chờ xử lý') !== false || strpos($status_text, 'Đang chờ') !== false) {
                                                                                    echo '<span style="color: orange; font-weight: bold;">' . $status_text . '</span>';
                                                                                } elseif (strpos($status_text, 'Lỗi') !== false || strpos($status_text, 'Sai') !== false) {
                                                                                    echo '<span style="color: red; font-weight: bold;">' . $status_text . '</span>';
                                                                                } else {
                                                                                    echo $status_text;
                                                                                }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php else : ?>
                                                                <tr>
                                                                    <td colspan="5" style="padding: 12px; text-align: center;">Chưa có lịch sử nạp thẻ cào.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                    <div id="pagination" style="text-align:center; padding:15px 0;">
                                                        <?php
                                                        $total_pages_card = ceil($total_card_payments / $payments_per_page);
                                                        if ($total_pages_card > 1) {
                                                            for ($i = 1; $i <= $total_pages_card; $i++) {
                                                                echo '<a href="?page_card=' . $i . '&tab=card" class="pagination-link" data-type="card" style="display: inline-block; padding: 6px 12px; margin-right: 4px; text-decoration: none; border: 1px solid #ff5601; border-radius: 4px; transition: 0.3s; ' . ($i == $current_page_card ? 'background:#ff5601; color:#fff;' : 'background:#fff; color:#ff5601;') . '" onmouseover="this.style.backgroundColor=\'#ff5601\'; this.style.color=\'white\';" onmouseout="this.style.backgroundColor=\'' . ($i == $current_page_card ? '#ff5601' : '#fff') . '\'; this.style.color=\'' . ($i == $current_page_card ? '#fff' : '#ff5601') . '\';">';
                                                                echo $i;
                                                                echo '</a>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="transferSection" style="display:none;">
    <div class="box_list_chuyenmuc">
        <div class="box_midss">
            <div class="box_detai" style="padding:5px;">
                <center>
                    <b style="color:green">Thông tin chuyển khoản</b>
                    <br>
                    <div id="transfer_error" style="color:red;"></div>
                </center>
                <div style="max-width:500px; margin: 0 auto; display:flex; align-items:center; border:1px solid #ccc; border-radius:8px; overflow:hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);">
                    <div style="flex:0 0 40%; background-color: #f5f5f5; text-align:center; padding:10px;">
                        <img id="qrCodeImage" src="https://api.vietqr.io/image/MB-88408121999-compact.png?accountName=LE%20THE%20THINH" alt="QR Code" style="max-width:100%; height:auto;">
                    </div>
                    <div style="flex:1; padding:10px; font-family: 'Times New Roman', serif;">
                        <p style="margin:5px 0;"><b>Tên Tài Khoản:</b> LUONG VAN TAN</p>
                        <p style="margin:5px 0;"><b>Số Tài Khoản:</b> 0392920228</p>
                        <p style="margin:5px 0;"><b>Ngân Hàng:</b> MBBANK</p>
                        <p style="margin:5px 0;"><b>Nội Dung:</b> <span id="transferContentDisplay"></span></p>
                        <span id="actualTransferContent" style="display:none;"></span> <button onclick="copyToClipboard('LUONG VAN TAN', 'Tên Tài Khoản')" style="background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-top: 5px;">Copy Tên TK</button>
                        <button onclick="copyToClipboard('0392920228', 'Số Tài Khoản')" style="background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-top: 5px;">Copy Số TK</button>
                        <button onclick="copyTransferContent()" style="background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-top: 5px;">Copy Nội Dung</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var currentUser = "<?php echo $_SESSION['username'] ?? 'guest'; ?>";
    const baseTransferContent = "naptien";
    function updateTransferInfo() {
        if (!currentUser || currentUser === 'guest') {
            document.getElementById('transfer_error').innerText = "Không thể lấy tên tài khoản. Vui lòng đăng nhập lại.";
            return;
        }

        const fullTransferContent = `[${currentUser}] ${baseTransferContent}`;
        
        document.getElementById('transferContentDisplay').innerText = fullTransferContent;
        document.getElementById('actualTransferContent').innerText = fullTransferContent;
        const qrCodeImgElement = document.getElementById('qrCodeImage');
        const encodedAddInfo = encodeURIComponent(fullTransferContent);
        qrCodeImgElement.src = `https://api.vietqr.io/image/MB-88408121999-compact.png?accountName=LE%20THE%20THINH`;
    }
    function copyToClipboard(text, label) {
        navigator.clipboard.writeText(text).then(function() {
            alert(`Đã copy ${label}: ${text}`);
        }, function(err) {
            console.error('Không thể copy: ', err);
            alert('Không thể copy, vui lòng thử lại hoặc copy thủ công.');
        });
    }
    function copyTransferContent() {
        const textToCopy = document.getElementById('actualTransferContent').innerText;
        copyToClipboard(textToCopy, 'Nội Dung Chuyển Khoản');
    }
    document.addEventListener('DOMContentLoaded', updateTransferInfo);
</script>
                                                    <br>
                                                    <hr>
                                                    <h3>Lịch Sử Chuyển Khoản</h3>
                                                    <table width="100%" border="1" cellspacing="0" style="width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
                                                        <thead>
                                                            <tr style="background-color: #2a9d8f; color: white; font-weight: bold;">
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Số Tiền</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Trạng Thái</th>
                                                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">Thời Gian</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (!empty($history_bank_transfers)) : ?>
                                                                <?php foreach ($history_bank_transfers as $transfer) : ?>
                                                                    <tr style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: black;"><?php echo number_format($transfer['amount'], 0, ',', '.'); ?> VNĐ</td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: <?php echo ($transfer['status'] == 'Thành Công') ? '#2a9d8f' : 'red'; ?>; font-weight: bold; color: black;">
                                                                            <?php echo htmlspecialchars($transfer['status']); ?>
                                                                        </td>
                                                                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;">
                                                                            <?php echo htmlspecialchars($transfer['created_at']); ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php else : ?>
                                                                <tr>
                                                                    <td colspan="3" style="padding: 12px; text-align: center; color: black;">Chưa có lịch sử chuyển khoản.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                    <div id="pagination2" style="text-align:center; padding:15px 0;">
                                                        <?php for ($i = 1; $i <= $total_pages_transfer; $i++) : ?>
                                                            <a href="?page_transfer=<?php echo $i; ?>&tab=transfer" class="pagination-link" data-type="transfer" style="display: inline-block; padding: 6px 12px; margin-right: 4px; text-decoration: none; border: 1px solid #2a9d8f; border-radius: 44px; transition: 0.3s; <?php echo ($i == $current_page_transfer) ? 'background:#2a9d8f; color:#fff;' : 'background:#fff; color:#2a9d8f;'; ?>" onmouseover="this.style.backgroundColor='#2a9d8f'; this.style.color='white';" onmouseout="this.style.backgroundColor='<?php echo ($i == $current_page_transfer) ? '#2a9d8f' : '#fff'; ?>'; this.style.color='<?php echo ($i == $current_page_transfer) ? '#fff' : '#2a9d8f'; ?>';">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                            <div class="copyright" style="line-height: 7px">
                                <p>Giấy phép thiết lập Mạng Xã Hội trên mạng số: 374/GP-BTTTT <br><br>do Bộ Thông Tin và Truyền Thông cấp ngày: 07/08/2015</p>
                                Bản Quyền thuộc về Gomobi
                            </div>
                        </div>
                    </div>
                    <script src="/view/static/js/ThreeCanvas.js" type="text/javascript"></script>
                    <script src="/view/static/js/Snow3d.js" type="text/javascript"></script>
                    <script src="/view/static/js/animation.js?v5" type="text/javascript"></script>
                    <script src="/view/static/js/rocket-loader.min.js" data-cf-settings="3248e74b3f0d3f240922716b-|49" defer></script>
                    <script>
                        $(document).ready(function() {
                            var activeTab = new URLSearchParams(window.location.search).get('tab') || 'card';
                            function setActiveRechargeTab(tabId) {
                                $("#cardSection").hide();
                                $("#transferSection").hide();
                                $("#btnCard").removeClass('active-recharge-btn').addClass('inactive-recharge-btn').css({
                                    "background-color": "#ccc",
                                    "color": "#000"
                                });
                                $("#btnTransfer").removeClass('active-recharge-btn').addClass('inactive-recharge-btn').css({
                                    "background-color": "#ccc",
                                    "color": "#000"
                                });

                                if (tabId === 'card') {
                                    $("#cardSection").show();
                                    $("#btnCard").removeClass('inactive-recharge-btn').addClass('active-recharge-btn').css({
                                        "background-color": "#ff5601",
                                        "color": "#fff"
                                    });
                                } else if (tabId === 'transfer') {
                                    $("#transferSection").show();
                                    $("#btnTransfer").removeClass('inactive-recharge-btn').addClass('active-recharge-btn').css({
                                        "background-color": "#ff5601",
                                        "color": "#fff"
                                    });
                                }
                                var newUrl = new URL(window.location.href);
                                newUrl.searchParams.set('tab', tabId);
                                window.history.replaceState(null, '', newUrl.toString());
                            }
                            setActiveRechargeTab(activeTab);

                            $("#btnCard").click(function() {
                                setActiveRechargeTab('card');
                            });

                            $("#btnTransfer").click(function() {
                                setActiveRechargeTab('transfer');
                            });
                            var lastPostTimeCardForm = 0;
                            var minDelayCardForm = 3000;

                            $("#cardPaymentForm").submit(function(event) {
                                event.preventDefault();

                                var currentTime = Date.now();
                                if (currentTime - lastPostTimeCardForm < minDelayCardForm) {
                                    toastr.warning('Vui lòng đợi ' + ((minDelayCardForm - (currentTime - lastPostTimeCardForm)) / 1000).toFixed(1) + ' giây trước khi nạp thẻ tiếp.', 'Khoảng cách quá gần!');
                                    return;
                                }

                                $("#comment_error").css("color", "black").text("Đang xử lý...");
                                var submitButton = $(this).find('input[type="submit"]');
                                submitButton.prop('disabled', true).val('Đang xử lý...');

                                var csrfToken = $('input[name="csrf_token"]').val();
                                var formData = $(this).serializeArray();
                                formData.push({ name: "csrf_token", value: csrfToken });

                                $.ajax({
                                    url: "/api/Card",
                                    type: "POST",
                                    data: formData,
                                    dataType: "json",
                                    success: function(response) {
                                        if (response.success) {
                                            toastr.success(response.message, 'Thành công!');
                                            lastPostTimeCardForm = Date.now();
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 2000);
                                        } else {
                                            toastr.error(response.message, 'Lỗi!');
                                        }
                                        $("#comment_error").css("color", response.success ? "green" : "red").text(response.message);
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        var errorMessage = "Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại sau.";
                                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                            errorMessage = jqXHR.responseJSON.message;
                                        } else if (errorThrown) {
                                            errorMessage = "Lỗi: " + errorThrown;
                                        }
                                        $("#comment_error").css("color", "red").text(errorMessage);
                                        toastr.error(errorMessage, 'Lỗi hệ thống!');
                                    },
                                    complete: function() {
                                        submitButton.prop('disabled', false).val('Xác Nhận');
                                    }
                                });
                            });

                            window.copyToClipboard = function(text, label) {
                                var tempInput = document.createElement("input");
                                tempInput.value = text;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand("copy");
                                document.body.removeChild(tempInput);
                                toastr.success(label + ' đã được sao chép: ' + text, 'Sao chép thành công!');
                            };
                            var lastPostTimeLoginRegister = 0;
                            $("form[name='loginform'], form[name='registerform']").submit(function(event) {
                                event.preventDefault();
                                var now = Date.now();
                                if (now - lastPostTimeLoginRegister < 10000) {
                                    var secondsLeft = Math.ceil((10000 - (now - lastPostTimeLoginRegister)) / 1000);
                                    toastr.warning("Bạn chỉ có thể thực hiện hành động này mỗi 10 giây. Vui lòng chờ " + secondsLeft + " giây.");
                                    return;
                                }

                                var form = $(this);
                                var formData = form.serialize();
                                var action = form.attr("id");
                                var csrfToken = form.find("input[name='csrf_token']").val();

                                formData += '&csrf_token=' + csrfToken + '&action=' + action;

                                $.ajax({
                                    url: "/api/card",
                                    type: "POST",
                                    data: formData,
                                    dataType: "json",
                                    success: function(response) {
                                        if (response.status === "success") {
                                            toastr.success(response.message, 'Thành công!');
                                            lastPostTimeLoginRegister = Date.now();
                                            if ($("#authModal").length && typeof $.fn.modal === 'function') {
                                                $("#authModal").modal("hide");
                                            }
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 2000);
                                        } else {
                                            toastr.error(response.message, 'Lỗi!');
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        var errorMessage = "Vui lòng thử lại trong ít phút nữa.";
                                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                            errorMessage = jqXHR.responseJSON.message;
                                        } else if (errorThrown) {
                                            errorMessage = "Lỗi: " + errorThrown;
                                        }
                                        toastr.error(errorMessage, 'Lỗi hệ thống!');
                                    }
                                });
                            });
                        });
                    </script>
           </body>
</html>
