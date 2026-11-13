<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success">Bảng tin chia sẻ</h2>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a class="btn btn-success" href="index.php?controller=Post&amp;action=create">+ Viết bài</a>
        <?php endif; ?>
    </div>
    <?php foreach (['post_success' => 'success', 'post_error' => 'danger'] as $key => $style): ?>
        <?php if (!empty($_SESSION[$key])): ?>
            <div class="alert alert-<?= $style ?>"><?= htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') ?></div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php foreach ($posts as $post): ?>
        <div class="card shadow-sm mb-4"><div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h5>
            <div class="text-muted mb-2">Đăng bởi <?= htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8') ?>
                &middot; <?= (int)$post['comment_count'] ?> bình luận</div>
            <p class="card-text"><?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
            <hr><h6>Bình luận</h6>
            <?php foreach (($commentsByPost[(int)$post['id']] ?? []) as $comment): ?>
                <p class="mb-2"><strong><?= htmlspecialchars($comment['commenter_name'], ENT_QUOTES, 'UTF-8') ?>:</strong>
                    <?= nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endforeach; ?>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <form method="post" action="index.php?controller=Post&amp;action=comment" class="mt-3">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($postCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                    <label class="form-label">Viết bình luận</label>
                    <textarea name="comment_text" class="form-control mb-2" maxlength="2000" rows="2" required></textarea>
                    <button class="btn btn-sm btn-outline-success" type="submit">Gửi bình luận</button>
                </form>
            <?php endif; ?>
        </div></div>
    <?php endforeach; ?>
    <?php if (!$posts): ?><p>Chưa có bài viết nào.</p><?php endif; ?>
</div>
