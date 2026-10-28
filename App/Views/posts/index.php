<div class="container mt-4">
    <h2 class="mb-4 text-primary border-bottom pb-2">Bảng Tin Chia Sẻ</h2>
    <div class="row">
        <?php foreach(($data['posts'] ?? []) as $post): ?>
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">Đăng bởi: <?= htmlspecialchars($post['author_name']) ?></h6>
                    <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>