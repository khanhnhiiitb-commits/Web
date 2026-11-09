<?php
$title = 'Quản lý đồ nhận - Give & Take';

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('itemImageUrl')) {
    function itemImageUrl($image): string {
        $image = trim((string)($image ?? ''));
        if ($image === '') return 'public/no-image.jpg';
        if (preg_match('~^https?://~i', $image)) return $image;
        $image = ltrim(str_replace('\\', '/', $image), '/');
        if (strpos($image, 'public/') === 0) return $image;
        if (strpos($image, 'uploads/items/') === 0) return 'public/' . $image;
        return 'public/uploads/items/' . basename($image);
    }
}

// [class badge, chữ hiển thị] theo trạng thái yêu cầu
$statusMap = [
    'pending'   => ['bg-warning text-dark', 'Đang chờ duyệt'],
    'accepted'  => ['bg-success',           'Đã được chấp nhận'],
    'rejected'  => ['bg-danger',            'Đã bị từ chối'],
    'completed' => ['bg-success',           'Đã hoàn thành'],
    'cancelled' => ['bg-secondary',         'Đã hủy'],
];
?>

<div class="py-2">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="fw-bold text-success mb-1">
                <i class="fa-solid fa-gift me-2"></i> Danh sách đồ tôi đăng ký nhận
            </h2>
            <p class="text-muted mb-0">Theo dõi trạng thái các món đồ bạn đã gửi yêu cầu xin từ cộng đồng.</p>
        </div>
        <a href="index.php?controller=Search&action=index" class="btn btn-success btn-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm thêm đồ khác
        </a>
    </div>

    <?php if (isset($_SESSION['request_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= e($_SESSION['request_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <div class="display-4 text-muted mb-3">📦</div>
            <h5 class="text-secondary fw-semibold">Bạn chưa đăng ký nhận món đồ nào.</h5>
            <p class="text-muted small">Hãy khám phá các món đồ đang được chia sẻ và gửi yêu cầu nhận nhé!</p>
            <a href="index.php?controller=Search&action=index" class="btn btn-success btn-sm mt-2">
                <i class="fa-solid fa-compass me-1"></i> Khám phá ngay
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($requests as $req): ?>
                <?php
                    [$statusClass, $statusText] = $statusMap[$req['request_status']] ?? ['bg-secondary', 'Không xác định'];
                    $stillAvailable = ($req['item_status'] ?? '') === 'available';
                ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <img src="<?= e(itemImageUrl($req['image_url'] ?? '')) ?>"
                             class="product-image"
                             alt="<?= e($req['item_title']) ?>"
                             onerror="this.onerror=null;this.src='public/no-image.jpg';">

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success"><?= e($req['category_name']) ?></span>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>

                            <h5 class="card-title fw-bold"><?= e($req['item_title']) ?></h5>

                            <p class="text-muted small mb-1">
                                <i class="fa-solid fa-user text-success me-1"></i>
                                Người đăng: <span class="text-dark"><?= e($req['owner_name']) ?></span>
                            </p>

                            <?php if (!empty($req['pickup_location'])): ?>
                                <p class="text-muted small mb-1">
                                    <i class="fa-solid fa-location-dot text-success me-1"></i>
                                    <?= e($req['pickup_location']) ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-muted small mb-1">
                                <i class="fa-solid fa-message text-success me-1"></i>
                                Lời nhắn của bạn:
                                <span class="text-dark fst-italic">"<?= e($req['message'] ?: 'Không có lời nhắn') ?>"</span>
                            </p>

                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-clock text-secondary me-1"></i>
                                Ngày gửi: <span class="text-dark"><?= e(date('d/m/Y H:i', strtotime($req['created_at']))) ?></span>
                            </p>

                            <a class="btn btn-outline-success btn-sm mb-2 w-100"
                               href="index.php?controller=Message&action=chat&id=<?= (int)$req['request_id'] ?>">
                                <i class="bi bi-chat-dots me-1"></i> Nhắn tin với người tặng
                            </a>
                            <div class="mt-auto pt-2 border-top">
                                <?php if ($stillAvailable): ?>
                                    <a href="index.php?controller=Item&action=detail&id=<?= (int)$req['item_id'] ?>"
                                       class="btn btn-outline-success btn-sm rounded-pill w-100">
                                        Xem chi tiết đồ
                                    </a>
                                <?php else: ?>
                                    <span class="small text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i>
                                        Món đồ này không còn trong danh sách chia sẻ.
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>