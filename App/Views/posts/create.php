<div class="container py-4" style="max-width:800px">
    <h2>Đăng bài chia sẻ cộng đồng</h2>
    <?php if (!empty($_SESSION['post_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['post_error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['post_error']); ?>
    <?php endif; ?>
    <form action="index.php?controller=Post&amp;action=store" method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($postCsrf, ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3"><label for="post-title" class="form-label">Tiêu đề</label>
            <input id="post-title" class="form-control" name="title" maxlength="255" required></div>
        <div class="mb-3"><label for="post-content" class="form-label">Nội dung</label>
            <textarea id="post-content" class="form-control" name="content" rows="7" required></textarea></div>
        <button class="btn btn-success" type="submit">Đăng bài</button>
        <a class="btn btn-outline-secondary" href="index.php?controller=Post&amp;action=index">Trở lại</a>
    </form>
</div>
