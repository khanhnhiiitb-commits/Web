<?php /** @var array $data */ ?>
<div class="container mt-4">
    <h2 class="mb-4 text-success border-bottom pb-2">Góc Sống Xanh</h2>
    <div class="row">
        <?php foreach($data['posts'] as $post): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if($post['thumbnail']): ?>
                    <img src="<?= $post['thumbnail'] ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                    <p class="text-muted mb-2"><small>Tác giả: <?= $post['full_name'] ?> | <?= date('d/m/Y', strtotime($post['created_at'])) ?></small></p>
                    <p class="card-text"><?= substr(strip_tags($post['content']), 0, 100) ?>...</p>
                    <a href="/post/detail/<?= $post['id'] ?>" class="btn btn-sm btn-success">Đọc tiếp</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>