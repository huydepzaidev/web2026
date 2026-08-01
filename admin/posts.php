<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post();
    $action = (string) ($_POST['action'] ?? '');
    $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

    try {
        if ($action === 'save') {
            $title = trim((string) ($_POST['tieude'] ?? ''));
            $content = trim((string) ($_POST['noidung'] ?? ''));
            $category = max(0, (int) ($_POST['theloai'] ?? 0));
            $pinned = isset($_POST['ghimbai']) ? 1 : 0;
            $image = trim((string) ($_POST['image'] ?? ''));
            if (mb_strlen($title) < 3 || mb_strlen($title) > 75) {
                throw new RuntimeException('Tiêu đề phải có từ 3 đến 75 ký tự.');
            }
            if (mb_strlen($content) < 10) {
                throw new RuntimeException('Nội dung phải có ít nhất 10 ký tự.');
            }
            if ($image !== '' && mb_strlen($image) > 255) {
                throw new RuntimeException('Đường dẫn ảnh quá dài.');
            }
            if ($postId) {
                $existing = admin_one('SELECT id, tieude FROM posts WHERE id = ?', 'i', [$postId]);
                if (!$existing) throw new RuntimeException('Bài viết không tồn tại.');
                admin_execute(
                    'UPDATE posts SET tieude = ?, noidung = ?, theloai = ?, ghimbai = ?, image = ? WHERE id = ?',
                    'ssiisi',
                    [$title, $content, $category, $pinned, $image, $postId]
                );
                admin_audit('Cập nhật bài viết', 'post', $postId, ['before_title' => $existing['tieude'], 'title' => $title, 'pinned' => $pinned]);
                admin_flash('success', 'Đã cập nhật bài viết.');
            } else {
                admin_execute(
                    'INSERT INTO posts (tieude, noidung, username, theloai, ghimbai, image, trangthai, tinhtrang, `like`)
                     VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0)',
                    'sssiis',
                    [$title, $content, $admin_user['username'], $category, $pinned, $image]
                );
                global $conn;
                $newId = (int) $conn->insert_id;
                admin_audit('Tạo bài viết', 'post', $newId, ['title' => $title, 'pinned' => $pinned]);
                admin_flash('success', 'Đã đăng bài viết mới.');
            }
        } elseif ($action === 'toggle_pin' && $postId) {
            $post = admin_one('SELECT id, tieude, ghimbai FROM posts WHERE id = ?', 'i', [$postId]);
            if (!$post) throw new RuntimeException('Bài viết không tồn tại.');
            $newValue = (int) $post['ghimbai'] === 1 ? 0 : 1;
            admin_execute('UPDATE posts SET ghimbai = ? WHERE id = ?', 'ii', [$newValue, $postId]);
            admin_audit($newValue ? 'Ghim bài viết' : 'Bỏ ghim bài viết', 'post', $postId, ['title' => $post['tieude']]);
            admin_flash('success', $newValue ? 'Đã ghim bài viết.' : 'Đã bỏ ghim bài viết.');
        } elseif ($action === 'delete' && $postId) {
            $post = admin_one('SELECT id, tieude FROM posts WHERE id = ?', 'i', [$postId]);
            if (!$post) throw new RuntimeException('Bài viết không tồn tại.');
            global $conn;
            $conn->begin_transaction();
            try {
                admin_execute('DELETE FROM comments WHERE post_id = ?', 'i', [$postId]);
                admin_execute('DELETE FROM posts WHERE id = ?', 'i', [$postId]);
                $conn->commit();
            } catch (Throwable $error) {
                $conn->rollback();
                throw $error;
            }
            admin_audit('Xóa bài viết', 'post', $postId, ['title' => $post['tieude']]);
            admin_flash('success', 'Đã xóa bài viết và các bình luận liên quan.');
        } else {
            throw new RuntimeException('Thao tác không hợp lệ.');
        }
    } catch (Throwable $error) {
        admin_flash('error', $error->getMessage());
    }
    admin_redirect('posts.php');
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $editId ? admin_one('SELECT * FROM posts WHERE id = ?', 'i', [$editId]) : null;
$showForm = isset($_GET['create']) || $editing;
$search = trim((string) ($_GET['q'] ?? ''));
$where = '';
$types = '';
$params = [];
if ($search !== '') {
    $where = ' WHERE tieude LIKE ? OR username LIKE ?';
    $term = '%' . $search . '%';
    $types = 'ss';
    $params = [$term, $term];
}
$total = (int) admin_scalar('SELECT COUNT(*) FROM posts' . $where, $types, $params);
[$page, $pages, $offset, $perPage] = admin_page($total);
$posts = admin_all(
    'SELECT p.*, (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
     FROM posts p' . $where . ' ORDER BY p.ghimbai DESC, p.id DESC LIMIT ? OFFSET ?',
    $types . 'ii',
    array_merge($params, [$perPage, $offset])
);

admin_render_header('Quản lý bài viết', 'posts', 'Đăng thông báo, chỉnh sửa và kiểm duyệt nội dung diễn đàn');
?>
<?php if ($showForm): ?>
<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2><?= $editing ? 'Chỉnh sửa bài viết' : 'Đăng bài viết mới' ?></h2><p>Nội dung sẽ hiển thị trực tiếp trên diễn đàn</p></div><span class="spacer"></span><a class="btn btn-secondary btn-sm" href="<?= admin_escape(admin_url('posts.php')) ?>">Đóng</a></div>
    <div class="panel-body">
        <form class="form-grid" method="post">
            <?= admin_csrf_field() ?><input type="hidden" name="action" value="save">
            <?php if ($editing): ?><input type="hidden" name="post_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
            <div class="form-group full"><label>TIÊU ĐỀ</label><input class="form-control" name="tieude" maxlength="75" value="<?= admin_escape($editing['tieude'] ?? '') ?>" required></div>
            <div class="form-group full"><label>NỘI DUNG</label><textarea class="form-control" name="noidung" rows="12" required><?= admin_escape($editing['noidung'] ?? '') ?></textarea><p class="help">Có thể xuống dòng; website hiện hiển thị nội dung dạng văn bản.</p></div>
            <div class="form-group"><label>THỂ LOẠI</label><select class="form-control" name="theloai"><option value="0" <?= (int) ($editing['theloai'] ?? 0) === 0 ? 'selected' : '' ?>>Thông báo chung</option><option value="1" <?= (int) ($editing['theloai'] ?? 0) === 1 ? 'selected' : '' ?>>Sự kiện</option><option value="2" <?= (int) ($editing['theloai'] ?? 0) === 2 ? 'selected' : '' ?>>Hướng dẫn</option></select></div>
            <div class="form-group"><label>ẢNH (TÊN FILE, JSON HOẶC URL)</label><input class="form-control" name="image" maxlength="255" value="<?= admin_escape($editing['image'] ?? '') ?>" placeholder='["sukien.jpg"]'></div>
            <div class="form-group full"><label><input type="checkbox" name="ghimbai" value="1" <?= (int) ($editing['ghimbai'] ?? 0) === 1 ? 'checked' : '' ?>> Ghim bài viết lên đầu diễn đàn</label></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Đăng bài viết' ?></button></div>
        </form>
    </div>
</section>
<?php endif; ?>
<form class="toolbar" method="get">
    <div class="search-box"><input type="search" name="q" value="<?= admin_escape($search) ?>" placeholder="Tìm tiêu đề hoặc tác giả..."><button type="submit">Tìm kiếm</button></div>
    <a class="btn btn-primary" href="<?= admin_escape(admin_url('posts.php?create=1')) ?>">+ Đăng bài mới</a><span class="count"><?= admin_number($total) ?> bài viết</span>
</form>
<section class="panel"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Bài viết</th><th>Tác giả</th><th>Phân loại</th><th>Bình luận</th><th>Thời gian</th><th class="text-right">Thao tác</th></tr></thead><tbody>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td><strong><?= admin_escape($post['tieude']) ?></strong><br><small class="text-muted">#<?= (int) $post['id'] ?><?= (int) $post['ghimbai'] === 1 ? ' · Đang ghim' : '' ?></small></td>
            <td><?= admin_escape($post['username']) ?></td>
            <td><?= (int) $post['ghimbai'] === 1 ? '<span class="badge badge-orange">Ghim</span>' : '<span class="badge badge-gray">Bình thường</span>' ?></td>
            <td><?= admin_number($post['comment_count']) ?></td>
            <td class="nowrap"><?= admin_escape(admin_datetime($post['created_at'])) ?></td>
            <td class="text-right"><div class="actions" style="justify-content:flex-end">
                <a class="btn btn-blue btn-sm" href="<?= admin_escape(admin_url('posts.php?edit=' . (int) $post['id'])) ?>">Sửa</a>
                <a class="btn btn-secondary btn-sm" href="<?= admin_escape(webgoc_url('bai-viet.php?id=' . (int) $post['id'])) ?>" target="_blank" rel="noopener">Xem</a>
                <form method="post"><?= admin_csrf_field() ?><input type="hidden" name="action" value="toggle_pin"><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="btn btn-secondary btn-sm" type="submit"><?= (int) $post['ghimbai'] === 1 ? 'Bỏ ghim' : 'Ghim' ?></button></form>
                <form method="post" data-confirm="Xóa vĩnh viễn bài viết và toàn bộ bình luận liên quan?"><?= admin_csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Xóa</button></form>
            </div></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$posts): ?><tr><td colspan="6"><div class="empty"><strong>Không có bài viết</strong>Hãy đăng bài viết đầu tiên hoặc đổi từ khóa tìm kiếm.</div></td></tr><?php endif; ?>
    </tbody>
</table></div></section>
<?php admin_pagination($page, $pages); admin_render_footer(); ?>

