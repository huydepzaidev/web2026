<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<?php
require_once dirname(__DIR__) . '/base_path.php';
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Ch&agrave;o m&#7915;ng b&#7841;n &#273;&#7871;n v&#7899;i Ch&uacute; B&eacute; R&#7891;ng Online - &#272;&#259;ng nh&#7853;p, &#272;&#259;ng k&yacute;</title>
	<link rel="stylesheet" href="/app/wiew/css/StyleSheet.css" type="text/css" />
	<link rel="stylesheet" href="/app/wiew/css/template.css" type="text/css" />
	<script src="/view/static/js/disable_devtools.js"></script>
	<link rel="shortcut icon" href='/images/favicon.png' type="image/x-icon" />
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-22738816-4']);
		_gaq.push(['_setDomainName', '.teamobi.com']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();
	</script>
	<link rel="stylesheet" href="/app/wiew/css/w3.css">
</head>
<style>
		.snowEffect {
			position: fixed;
			width: 100%;
			height: 100%;
			left: 0;
			top: 0;
			z-index: 99;
			overflow: hidden;
			pointer-events: none;
		}

		#snowcanvas {
			position: fixed;
			z-index: 0;
		}

        /* ThĂªm CSS cho thĂ´ng bĂ¡o */
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }        /* local-login-fallback */
        html, body {
            min-height: 100%;
        }
        body {
            background: #b7b7b7;
        }
        .body_body {
            max-width: 630px;
            margin: 0 auto 12px;
            padding: 0 6px;
            box-sizing: border-box;
        }
        .body-content {
            background: #ff5601;
            border-radius: 14px 14px 0 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .body-content .a {
            padding: 8px 0 4px;
        }
        .link-more .h {
            background: #ffaf4d;
        }
        .body {
            background: #ffaf4d;
            padding: 10px 8px 16px;
        }
        .menu2 table {
            table-layout: fixed;
        }
        .menu td {
            height: 24px;
            border: 2px solid #7a3b20;
        }
        .menu td a {
            display: block;
            padding: 4px 0;
        }
        #loginForm table {
            margin: 8px auto;
        }
        #loginForm input[type="text"],
        #loginForm input[type="password"] {
            width: 160px;
            max-width: 100%;
            padding: 4px 6px;
            box-sizing: border-box;
        }
        #button1 {
            margin-top: 6px;
            min-width: 92px;
            border-radius: 4px;
        }
        .left_b_bottom,
        .right_b_bottom,
        .footer {
            min-height: 18px;
        }
        /* auth-page-polish */
        html, body { min-height: 100%; }
        body {
            background:
                radial-gradient(circle at top, rgba(255, 244, 183, .42), transparent 34rem),
                linear-gradient(180deg, #f3c16b 0%, #c86f31 42%, #7d3b24 100%);
            font-family: Arial, Helvetica, sans-serif;
        }
        .body_body {
            max-width: 560px;
            margin: 18px auto;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .bg_top, .left_top { display: none; }
        .body-content {
            background: #ff7a1f;
            border: 3px solid #713418;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 16px 34px rgba(55, 21, 8, .35);
        }
        .body-content .a {
            height: auto !important;
            min-height: 108px;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding: 12px 0 10px;
            background: linear-gradient(180deg, #ff8c2b 0%, #ff6717 100%);
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }
        .body-content .a img {
            max-width: 88%;
            height: auto;
            max-height: 90px;
            display: block;
            margin: 0 auto;
        }
        .link-more .h { background: #ffd078; }
        .bg_tree, .bg_noel { display: none; }
        .menu2 {
            background: #5f260b !important;
            padding: 4px;
            clear: both;
            position: relative;
            z-index: 2;
        }
        .menu2 table { table-layout: fixed; margin: 0; }
        .menu td {
            height: 28px;
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 4px;
            overflow: hidden;
        }
        .menu td a {
            display: block;
            padding: 6px 0;
            font-weight: 700;
            color: #fff !important;
            text-decoration: none;
        }
        .menu td#selected {
            background: #ffcf6b;
        }
        .menu td#selected a {
            color: #4b210c !important;
        }
        .body {
            background: linear-gradient(180deg, #ffd487 0%, #ffb55d 100%) !important;
            padding: 16px 12px 20px;
            color: #49210e;
        }
        .body > div:first-child {
            font-size: 12px !important;
            margin-bottom: 10px;
            font-weight: 700;
        }
        #loginForm, #registerForm {
            display: inline-block;
            width: min(100%, 360px);
            background: rgba(255,255,255,.42);
            border: 1px solid rgba(104,49,17,.28);
            border-radius: 8px;
            padding: 12px;
            box-sizing: border-box;
        }
        #loginForm table, #registerForm table {
            width: 100%;
            margin: 8px 0;
        }
        #loginForm td, #registerForm td {
            padding: 5px 3px;
            text-align: left;
        }
        #loginForm label, #registerForm label {
            font-weight: 700;
            white-space: nowrap;
        }
        #loginForm input[type="text"], #loginForm input[type="password"],
        #registerForm input[type="text"], #registerForm input[type="password"] {
            width: 100%;
            min-height: 30px;
            border: 1px solid #a75e22;
            border-radius: 5px;
            padding: 5px 7px;
            box-sizing: border-box;
            background: #fffaf1;
        }
        #button1 {
            width: 100%;
            margin-top: 8px;
            border-radius: 5px;
            border: 1px solid #8a1d12;
            background: linear-gradient(180deg, #e84a35 0%, #b7281d 100%) !important;
            font-weight: 700;
        }
        .auth-link-button {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 12px;
            border: 1px solid #8a1d12;
            border-radius: 5px;
            background: rgba(255, 250, 241, .9);
            color: #8a1d12 !important;
            font-weight: 700;
            text-decoration: none;
        }
        .auth-link-button:hover {
            background: #ffcf6b;
            text-decoration: none;
        }
        .message {
            text-align: left;
            line-height: 1.4;
        }
        .left_b_bottom, .right_b_bottom, .footer { display: none; }
        .copyright {
            margin-top: 10px;
            color: #fff5d6;
            text-shadow: 0 1px 1px rgba(0,0,0,.35);
        }
        @media (max-width: 420px) {
            #loginForm td, #registerForm td { display: block; width: 100%; }
            #loginForm tr, #registerForm tr { display: block; margin-bottom: 8px; }
        }
</style>
<body>
<div class="snowEffect">
	<canvas id="snowcanvas" height="100%" width="100%"></canvas>
</div>
<div class="body_body">
	<div class="left_top"></div>
	<div class="bg_top">
		<div class="right_top"></div>
	</div>
	<div class="body-content">
		<div class="a" align="center"><img src="/images/logo_sk_he.png" height="90"/></div>
		<div id="top">
			<div class="link-more">
				<div class="h" align="center">
					<div class="bg_tree"></div>
					<div class="bg_noel"></div>
					<div class="menu2" style="background: #561d00;">
						<table width="100%" border="0" cellspacing="4">
							<tr class="menu">
								<td><a href="/">Trang Ch&#7911;</a></td>
								<td><a href="/gioi-thieu">Gi&#7899;i Thi&#7879;u</a></td>
								<td><a href="/forum">Di&#7877;n &#272;&agrave;n</a></td>
							</tr>
						</table>
					</div>
					<div class="body" style="text-align:center">
						<div style="font-size:10px;">S&#7917; d&#7909;ng t&agrave;i kho&#7843;n Ch&uacute; B&eacute; R&#7891;ng Online &#273;&#7875; &#273;&#259;ng nh&#7853;p.</div>
						<center>
							<form id="loginForm" method="POST" name="login">
                                <input type="hidden" name="action" value="login" />
                                <input type="hidden" name="keySig" value="a511129a7ce15460414e6fe318eebc2b" />
								<input type="hidden" name="nav" value="" readonly="readonly" />
								<table>
									<tr>
										<td colspan=2><label for="user">T&agrave;i Kho&#7843;n:</label></td>
										<td colspan=2><input name="user" type="text" value="" required /></td>
									</tr>
									<tr>
										<td colspan=2><label for="pass">M&#7853;t kh&#7849;u:</label></td>
										<td colspan=2><input name="pass" type="password" value="" required />
											<input type="hidden" name="checkru" value="d3540b1767470e0a87215174bd0ed85d" />
										</td>
									</tr>
								</table>
								<table>
									<tr>
										<td>
											<input type="radio" name="server" value=1 required /> Server 1 sao
										</td>
									</tr>
								</table>
                                <div id="loginMessage" class="message" style="display:none;"></div>
								<button type="submit" class="w3-button w3-red" value="&#272;&#259;ng nh&#7853;p" id="button1" name="submit">&#272;&#259;ng nh&#7853;p</button><br />
								<div style="font-size:10px;">
									(&#272;&#259;ng k&yacute; game &#7903; d&#432;&#7899;i)<br>
									<a href="register" class="auth-link-button">&#272;&#259;ng K&yacute;</a>
								</div>
							</form><br>
						</center>
					</div>
				</div>
				<br>
			</div><br>
		</div>
	</div>
	<div class="left_b_bottom">
		<div class="right_b_bottom">
			<div class="footer">
				<div class="left_bottom"></div>
				<div class="right_bottom"></div>
			</div>
		</div>
	</div>
	<div class="copyright"><br><b>B&#7843;n quy&#7873;n thu&#7897;c v&#7873; Ch&uacute; B&eacute; R&#7891;ng Online - 2013</b></div>
</div>
<script src="/assets/jquery/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault();

        var form = $(this);
        var url = <?= json_encode(webgoc_url('app/auth_process.php'), JSON_UNESCAPED_SLASHES) ?>;

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                var messageDiv = $('#loginMessage');
                messageDiv.css('display', 'block');
                messageDiv.removeClass('success error');

                if (response.status === 'success') {
                    messageDiv.addClass('success');
                    messageDiv.text(response.message);
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                } else {
                    messageDiv.addClass('error');
                    messageDiv.text(response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
                var messageDiv = $('#loginMessage');
                messageDiv.css('display', 'block');
                messageDiv.addClass('error');
                messageDiv.text('Da xay ra loi ket noi. Vui long thu lai sau.');
            }
        });
    });
});
</script>

<script src="/view/static/js/ThreeCanvas.js" type="text/javascript"></script>
<script src="/view/static/js/Snow3d.js" type="text/javascript"></script>
<script src="/view/static/js/animation.js?v4" type="text/javascript"></script>
</body>
</html>
