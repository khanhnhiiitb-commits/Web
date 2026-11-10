<?php
$esc = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$statusNames = [
    'pending_review' => ['bg-warning text-dark', 'Chờ admin duyệt'],
    'available' => ['bg-success', 'Đang chia sẻ'],
    'reserved' => ['bg-info text-dark', 'Đã có người nhận'],
    'gifted' => ['bg-secondary', 'Đã tặng'],
    'rejected' => ['bg-danger', 'Bị từ chối'],
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold">Kho đồ của tôi</h2>
        <a href="index.php?controller=Item&amp;action=create" class="btn btn-success">Đăng món đồ</a>
    </div>
    <?php if (empty($items)): ?>
        <p class="text-muted">Bạn chưa đăng món đồ nào.</p>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($items as $item): ?>
            <?php [$color, $name] = $statusNames[$item['status'] ?? ''] ?? ['bg-secondary', $item['status'] ?? 'Không rõ']; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= $esc($item['title']) ?></h5>
                        <p class="small text-muted mb-2"><?= $esc($item['category_name']) ?></p>
                        <span class="badge <?= $color ?> mb-3"><?= $esc($name) ?></span>
                        <div><a href="index.php?controller=Item&amp;action=detail&amp;id=<?= (int)$item['id'] ?>"
                                class="btn btn-outline-success btn-sm">Xem chi tiết</a></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
