<?php
require_once __DIR__ . '/data_post.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa bài viết - Chú Bé Rồng Online</title>
    <meta name="keywords" content="Chú Bé Rồng Online, ngoc rong mobile, game ngoc rong, game 7 vien ngoc rong, game bay vien ngoc rong">
    <meta name="description" content="Website chính thức của Chú Bé Rồng Online – Game Bay Viên Ngọc Rồng Mobile nhập vai trực tuyến trên máy tính và điện thoại về Game 7 Viên Ngọc Rồng hấp dẫn nhất hiện nay!">
    <meta http-equiv="refresh" content="600">
    <meta name="robots" content="index,follow">

    <link rel="icon" href="images/favicon-48x48.ico" type="image/x-icon">
    <link rel="shortcut icon" href="images/favicon-48x48.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="images/favicon-48x48.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="images/favicon-48x48.png">
    <link rel="icon" type="image/png" sizes="64x64" href="images/favicon-64x64.png">
    <link rel="icon" type="image/png" sizes="128x128" href="images/favicon-128x128.png">
	<script src="/view/static/js/disable_devtools.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="/view/static/css/dangbai.css?v=1.2">
    <link rel="stylesheet" href="/app/wiew/css/StyleSheet.css" type="text/css" />
    <link rel="stylesheet" href="/app/wiew/css/template.css" type="text/css" />
    <link rel="shortcut icon" href='https://forum.ngocrongonline.com/app/view/images/favicon.png' type="image/x-icon" />    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="/view/static/js/ThreeCanvas.js" type="text/javascript"></script>
    <script src="/view/static/js/Snow3d.js" type="text/javascript"></script>
    <script src="/view/static/js/animation.js?v=4" type="text/javascript"></script>
</head>
<body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>
    <div class="body-content">
        <h1 class="a"><img src="/images/logo_sk_he.png" alt="Chú Bé Rồng Online"/></h1>
        <div id="top">
            <div class="link-more">
                <div class="h">
                    <div class="bg_tree"></div>
                    <div class="bg_noel"></div>
                    <div class="menu2">
                        <table width="100%" border="0" cellspacing="4">
                            <tr class="menu">
                                <td><a href="/">Trang Chủ</a></td>
                                <td><a href="/gioi-thieu">Giới Thiệu</a></td>
                                <td id="selected"><a href="/forum">Diễn Đàn</a></td>
                                <td>
                                   <a href="<?php echo htmlspecialchars($box_zalo_url ?? 'https://zalo.me/g/njvxgh490'); ?>" target="_blank">Box Zalo</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="container pt-5 pb-5">
            <div class="dangbai-form-wrapper"> 
                <div class="post-creation-header">
                    <h2>Chỉnh sửa bài viết</h2>
                </div>

                <?php echo $_alert; ?>

                <?php if ($post_detail !== null): ?>
                    <form action="edit-post.php?id=<?php echo htmlspecialchars($post_id); ?>" method="POST" class="post-creation-form">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">

                        <div class="form-group">
                            <label for="tieude">Tiêu đề:</label>
                            <input type="text" id="tieude" name="tieude" class="form-control" value="<?php echo htmlspecialchars($post_detail['tieude']); ?>" required>
                        </div>

                        <div class="form-group mt-3">
                            <label for="noidung">Nội dung:</label>
                            <textarea id="noidung" name="noidung" class="form-control" rows="10" required><?php echo htmlspecialchars($post_detail['noidung']); ?></textarea>
                        </div>

                        <button type="submit" name="submit_edit" class="btn btn-primary mt-3">Cập nhật bài viết</button>
                        <a href="/Forum" class="btn btn-secondary mt-3">Hủy</a>
                    </form>
                <?php else: ?>
                    <p>Không tìm thấy bài viết hoặc bạn không có quyền chỉnh sửa.</p>
                    <a href="/Forum" class="btn btn-primary">Quay lại diễn đàn</a>
                <?php endif; ?>
            </div>
        </div>

        <script>
            const form = document.querySelector('.post-creation-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const submitError = form.querySelector('#submit-error');
            const tieudeInput = document.getElementById('tieude');
            const noidungTextarea = document.getElementById('noidung');

            form.addEventListener('submit', (event) => {
                const titleLength = tieudeInput.value.trim().length;
                const contentLength = noidungTextarea.value.trim().replace(/<[^>]*>?/gm, '').length;

                if (titleLength < 5 || contentLength < 10) {
                    event.preventDefault();
                    toastr.error('Tiêu đề và nội dung phải có ít nhất 5/10 ký tự!');
                    if (submitError) {
                        submitError.innerHTML = '<strong>Lỗi:</strong> Tiêu đề và nội dung phải có ít nhất 5/10 ký tự!';
                        submitError.style.display = 'block';
                        submitBtn.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else {
                    if (submitError) {
                        submitError.style.display = 'none';
                    }
                }
            });
        </script>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
