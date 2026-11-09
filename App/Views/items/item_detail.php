
<?php
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars(
            (string)($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('itemImageUrl')) {
    function itemImageUrl($image): string
    {
        $image = trim((string)($image ?? ''));

        if ($image === '') {
            return 'https://placehold.co/600x450/eaf5ea/198754?text=Song+Xanh';
        }

        if (preg_match('~^https?://~i', $image)) {
            return $image;
        }

        $image = ltrim(str_replace('\\', '/', $image), '/');

        if (strpos($image, 'public/') === 0) {
            return $image;
        }

        if (strpos($image, 'uploads/items/') === 0) {
            return 'public/' . $image;
        }

        return 'public/uploads/items/' . basename($image);
    }
}

$status = $item['status'] ?? 'pending';

$statusLabels = [
    'available' => 'Có thể nhận',
    'pending'   => 'Đang chờ duyệt',
    'reserved'  => 'Đã có người nhận',
    'gifted'    => 'Đã trao tặng',
    'hidden'    => 'Đã ẩn'
];

$statusClasses = [
    'available' => 'bg-success',
    'pending'   => 'bg-warning text-dark',
    'reserved'  => 'bg-info text-dark',
    'gifted'    => 'bg-secondary',
    'hidden'    => 'bg-dark'
];

$statusLabel = $statusLabels[$status] ?? $status;
$statusClass = $statusClasses[$status] ?? 'bg-secondary';

$isOwner = $isOwner ?? false;
$canReceive = $canReceive ?? false;
$isLoggedIn = !empty($_SESSION['user_id']);
?>

<div class="container py-4 py-md-5">

    <div class="mb-4">
        <a href="index.php"
           class="text-decoration-none text-success">
            <i class="bi bi-arrow-left"></i>
            Quay lại trang chủ
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="row g-0">

            <!-- Hình ảnh sản phẩm -->
            <div class="col-md-6 bg-light">

                <img
                    src="<?= e(itemImageUrl($item['image_url'] ?? '')) ?>"
                    alt="<?= e($item['title'] ?? '') ?>"
                    class="w-100"
                    style="height: 440px; object-fit: contain;"
                    onerror="this.onerror=null;this.src='https://placehold.co/600x450/eaf5ea/198754?text=Song+Xanh';"
                >

            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-6">

                <div class="card-body p-4 p-lg-5">

                    <div class="d-flex flex-wrap gap-2 mb-3">

                        <span class="badge bg-success-subtle text-success">
                            <?= e($item['category_name'] ?? 'Chưa phân loại') ?>
                        </span>

                        <span class="badge <?= e($statusClass) ?>">
                            <?= e($statusLabel) ?>
                        </span>

                    </div>

                    <h2 class="fw-bold mb-4">
                        <?= e($item['title'] ?? '') ?>
                    </h2>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">
                            <i class="bi bi-patch-check me-1"></i>
                            Tình trạng món đồ
                        </div>

                        <div class="fw-semibold">
                            <?= e($item['condition_note'] ?? 'Chưa cập nhật') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">
                            <i class="bi bi-geo-alt me-1"></i>
                            Địa điểm nhận
                        </div>

                        <div class="fw-semibold">
                            <?= e($item['pickup_location'] ?? 'Chưa cập nhật') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">
                            <i class="bi bi-person-circle me-1"></i>
                            Người chia sẻ
                        </div>

                        <div class="fw-semibold">
                            <?= e($item['owner_name'] ?? 'Thành viên') ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted small mb-1">
                            <i class="bi bi-calendar3 me-1"></i>
                            Ngày đăng
                        </div>

                        <div>
                            <?php
                            $createdAt = $item['created_at'] ?? null;
                            echo $createdAt
                                ? e(date('d/m/Y H:i', strtotime($createdAt)))
                                : 'Chưa cập nhật';
                            ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Khu vực hành động -->
                    <div class="mt-4">

                        <?php if ($isOwner): ?>

                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Đây là món đồ bạn đã đăng chia sẻ.
                            </div>

                        <?php elseif ($canReceive): ?>

                            <!-- Form đăng ký nhận đồ -->
                            <form action="index.php?controller=Item&action=requestItem" method="POST">
                                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label small fw-semibold text-muted">Lời nhắn gửi chủ món đồ (tùy chọn):</label>
                                    <textarea class="form-control form-control-sm" id="message" name="message" rows="2" placeholder="Ví dụ: Mình rất cần món đồ này cho học tập, bạn cho mình xin nhé..."></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-success btn-lg w-100 shadow-sm"
                                    onclick="return confirm('Bạn chắc chắn muốn đăng ký nhận món đồ này chứ?');"
                                >
                                    <i class="bi bi-heart-fill me-2"></i>
                                    Đăng ký nhận đồ ngay
                                </button>
                            </form>

                        <?php elseif (!$isLoggedIn && $status === 'available'): ?>

                            <a
                                href="index.php?controller=Auth&action=login"
                                class="btn btn-success btn-lg w-100"
                            >
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Đăng nhập để nhận đồ
                            </a>

                        <?php else: ?>

                            <div class="alert alert-secondary mb-0">
                                Món đồ hiện không thể đăng ký nhận.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Mô tả chi tiết -->
    <div class="card border-0 shadow-sm rounded-4 mt-4">

        <div class="card-body p-4">

            <h4 class="fw-bold mb-3">
                <i class="bi bi-card-text text-success me-2"></i>
                Mô tả chi tiết
            </h4>

            <div style="white-space: pre-line; line-height: 1.8;">
                <?= e($item['description'] ?? 'Chưa có mô tả.') ?>
            </div>

        </div>
    </div>

</div>
