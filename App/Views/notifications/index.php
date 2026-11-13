<div class="container py-4">
    <h2>Thông báo của tôi</h2>
    <?php if (empty($notifications)): ?><p>Chưa có thông báo.</p><?php endif; ?>
    <?php foreach ($notifications as $notification): ?>
        <div class="card mb-2 <?= empty($notification['is_read']) ? 'border-success' : '' ?>">
            <div class="card-body">
                <strong><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                <p class="mb-1"><?= nl2br(htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                <small class="text-muted"><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>
