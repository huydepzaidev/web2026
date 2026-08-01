<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');
require_once 'post_detail_logic.php';

?>
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width,maximum-scale=1,user-scalable=no"/>
		<meta http-equiv="content-language" content="vi" />
        <title><?php echo $post_detail ? htmlspecialchars($post_detail['tieude']) : 'Bài viết không tồn tại'; ?> - Diễn Đàn - Chú Bé Rồng Online</title>
    <link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/images/favicon-48x48.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
		<meta name="keywords" content="Chú Bé Rồng Online - Ngọc Rồng Online - TÍNH NĂNG MỚI: ĐỆ TỬ MỚI, Chú Bé Rồng Online, Ngoc Rong Online, Ngọc Rồng Mobile, Ngoc Rong Dien Thoai, Dragon Ball Online, game ngoc rong, ngoc rong, ngoc rong online, ngoc rong mobile, game 7 vien ngoc rong, game ngọc rồng, ngọc rồng, game 7 viên ngọc rồng" />
		<meta name="description" content="Ngoc Rong Online, Ngọc Rồng Mobile, Ngoc Rong Dien Thoai, Dragon Ball Online" />
		<meta name="robots" content="INDEX,FOLLOW" />
    <script src="/view/static/js/disable_devtools.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="app/wiew/css/StyleSheet.css" />
		<link rel="stylesheet" href="app/wiew/css/w3.css">           
        <link rel="stylesheet" href="/view/static/css/template.css?v=1.10">        		
         <link rel="stylesheet" type="text/css" href="view/static/css/eff.css" />
    </head>
    <body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
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
		<div class="bg_tree"></div>
		<div class="bg_noel"></div>
				<div class="menu2" style="">
        <table width="100%" border="0" cellspacing="4">
			<tr class="menu">
				<td style="border: 3px solid #924C31;padding: 2px;"><a href="trang-chu">Trang Chủ</a></td>
				<td style="border: 3px solid #924C31;padding: 2px;"><a href="gioi-thieu">Giới Thiệu</a></td>
				<td id="selected" style="border: 3px solid #FFAF4D;padding: 2px;"><a href="forum">Diễn Đàn</a></td>
				<td style="border: 3px solid #924C31;padding: 2px;">
			               <a href="<?php echo htmlspecialchars($box_zalo_url ?? 'https://zalo.me/g/njvxgh490'); ?>" target="_blank">Box Zalo</a>
			</tr>
		</table>
		</div><div class="body"><style>
    .w-40px { width: 40px !important; }
    .a-hv { cursor: pointer; }
</style>
<div id="box_forums">
    <?php echo $_alert; ?>
    <div class="box_list_parent">
        <div class="box_parent_list_next">
            <div class="box_phantrang">
                <div class="backlink">
                    <a style="color:#fff;" href="forum">Quay l&#7841;i</a>
                </div>
                <div class="pagination"></div>
            </div>
        </div>
        <form method="POST" name="UpdateHide">
            <div class="box_list_parent_next">
                <?php if ($post_detail): ?>
                    <table cellpadding="0" cellspacing="0" width="99%" border="0" style="table-layout:fixed;word-wrap: break-word;">
                        <tr>
                            <td width="50px;" align="center" class="box_list_c_s">
                                <img class="avatar" src="<?php echo htmlspecialchars($post_detail['author_avatar_path']); ?>" alt="<?php echo htmlspecialchars($post_detail['username']); ?>" />
                                <div class="box_list_b_s" style="background-color: #FFAF4D;">
                                    <div class="box_list_ads">
                                        <div class="box_oxx_admin" style="border:none">
                                            <a style="font-size: 8px;text-decoration: none;" href="javascript:void(0)"><?php echo htmlspecialchars($post_detail['username']); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="box_list_b_s">
                                <div class="box_list_ads">
                                    <div class="box_oxx_admin">
                                        <span style="font-weight:normal;color:black;font-size:9px;"><i>
                                            <img style="vertical-align:middle;" title="<?php echo htmlspecialchars($post_detail['username']); ?> is offline" src="images/img/offline.png" border="0" />
                                            <?php echo htmlspecialchars($post_detail['created_at']); ?></i></span>
                                    </div>
                                    <div class="box_title_bviet"><?php echo htmlspecialchars($post_detail['tieude']); ?></div>
                                    <div class="box_ndung_bviet">
                                        <?php echo nl2br(htmlspecialchars($post_detail['noidung'])); ?>
                                        <?php if (!empty($post_detail['display_image_path'])): ?>
                                            <br /><center><img src="<?php echo htmlspecialchars($post_detail['display_image_path']); ?>" /></center>
                                        <?php endif; ?>
                                    </div>
                                    <div class="box_timee_bviet" style="padding:3px;">
                                        <span style="color:#333;"><span style="color:red">&hearts;</span> 1.000.000.000 ng&#432;&#7901;i th&#237;ch b&#224;i n&#224;y.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p><center><a href="/forum" target="_blank"><img src="https://my.teamobi.com/images/new.gif"> Ng&#7885;c R&#7891;ng Tu&#7893;i Th&#417; <img src="https://my.teamobi.com/images/new.gif"></a></center></p>
                <?php else: ?>
                    <div class="box_list_parent_next" style="margin-top: 10px;">
                        <div class="box_list_c_s" style="padding: 10px; text-align: center;">
                            B&#224;i vi&#7871;t kh&#244;ng t&#7891;n t&#7841;i ho&#7863;c &#273;&#227; b&#7883; x&#243;a.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="box_parent_list_next" style="margin:0px;text-align:right;">
                <div class="box_phantrang"><div class="pagination"></div></div>
            </div>
        </form>
        <div class="box_list_chuyenmuc"></div>
    </div>
</div>
<div class="clearfix"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script src="https://forum.ngocrongonline.com/app/js/icon.js?v6" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
</div>
<br></div>
</div><br>
</div>
</div>
</div><script type="d8583da729ad4da4fb7fe69d-text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-22738816-8', 'ngocrongonline.com');
  ga('send', 'pageview');

</script>
	<script src="https://ngocrongonline.com/view/static/js/ThreeCanvas.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script src="https://ngocrongonline.com/view/static/js/Snow3d.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script src="https://ngocrongonline.com/view/static/js/animation.js?v4" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>


   
    <script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="d8583da729ad4da4fb7fe69d-|49" defer></script></body>
</html>


