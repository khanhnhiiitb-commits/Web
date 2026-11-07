<?php
$title = 'Quản lý đồ nhận - Give & Take';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success"><i class="fa-solid fa-gift me-2"></i> Danh sách đồ tôi đăng ký nhận</h2>
            <p class="text-muted mb-0">Theo dõi trạng thái các món đồ bạn đã gửi yêu cầu xin từ cộng đồng.</p>
        </div>
        <a href="index.php?controller=home&action=search" class="btn btn-success btn-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm thêm đồ khác
        </a>
    </div>

    <?php if (isset($_SESSION['request_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['request_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <div class="display-4 text-muted mb-3">📦</div>
            <h5 class="text-secondary fw-semibold">Bạn chưa đăng ký nhận món đồ nào.</h5>
            <p class="text-muted small">Hãy khám phá các món đồ đang được chia sẻ và gửi yêu cầu nhận nhé!</p>
            <a href="index.php?controller=home&action=search" class="btn btn-success btn-sm mt-2">
                <i class="fa-solid fa-compass me-1"></i> Khám phá ngay
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($requests as $req): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title fw-bold text-dark mb-0">
                                    <?= htmlspecialchars($req['item_title'] ?? 'Món đồ') ?>
                                </h5>
                                <?php 
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Đang chờ';
                                    if ($req['request_status'] === 'accepted') {
                                        $statusClass = 'bg-success';
                                        $statusText = 'Đã chấp nhận';
                                    } elseif ($req['request_status'] === 'rejected') {
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Đã từ chối';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?> px-2 py-1"><?= $statusText ?></span>
                            </div>

                            <p class="text-muted small mb-2">
                                <i class="fa-solid fa-message text-success me-1"></i> Lời nhắn của bạn: 
                                <span class="text-dark fst-italic">"<?= htmlspecialchars($req['message'] ?: 'Không có lời nhắn') ?>"</span>
                            </p>

                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-clock text-secondary me-1"></i> Ngày gửi: 
                                <span class="text-dark"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></span>
                            </p>

                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="small text-success fw-semibold">
                                    <i class="fa-solid fa-shield-heart me-1"></i> Give & Take
                                </span>
                                <a href="index.php?controller=item&action=detail&id=<?= $req['item_id'] ?? 0 ?>" class="btn btn-outline-success btn-sm rounded-pill">
                                    Xem chi tiết đồ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>