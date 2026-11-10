<?php
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('itemImageUrl')) {
    function itemImageUrl($image): string
    {
        $image = trim((string) ($image ?? ''));

        if ($image === '') {
            return ''; 
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

$items = is_array($items ?? null) ? $items : [];
?>

<?php if (!empty($items)): ?>

    <?php foreach ($items as $item): ?>

        <div class="col-12 col-sm-6 col-lg-4">

            <div class="card product-card h-100">

                <?php $hasImage = trim((string)($item['image_url'] ?? '')) !== ''; ?>
                <div class="gt-product-media <?= $hasImage ? '' : 'gt-no-image' ?>">
                <?php if ($hasImage): ?>
                <img
                    loading="lazy"
                    src="<?= e(itemImageUrl($item['image_url'] ?? '')) ?>"
                    class="product-image"
                    alt="<?= e($item['title'] ?? 'Sản phẩm') ?>"
                    onerror="this.onerror=null;this.hidden=true;this.parentElement.classList.add('gt-no-image');"
                >
                <?php endif; ?>
                <div class="gt-image-fallback" aria-label="Chưa có hình ảnh"><i class="bi bi-image"></i><span>Chưa có hình ảnh</span></div>
                </div>

                <div class="card-body d-flex flex-column gt-product-body">

                    <span class="badge bg-success mb-2 align-self-start">
                        <?= e($item['category_name'] ?? 'Chưa phân loại') ?>
                    </span>

                    <h5 class="card-title fw-bold">
                        <?= e($item['title'] ?? '') ?>
                    </h5>

                    <p class="text-muted small gt-product-meta">
                        <i class="bi bi-geo-alt"></i>
                        <?= e($item['pickup_location'] ?? 'Chưa cập nhật') ?>
                    </p>

                    <p class="text-muted small gt-product-meta">
                        <i class="bi bi-heart"></i>
                        Món đồ đang chờ người nhận.
                    </p>

                    <a
                        href="index.php?controller=Item&action=detail&id=<?= (int) $item['id'] ?>"
                        class="btn btn-outline-success mt-auto"
                    >
                        Xem chi tiết
                    </a>

                </div>
            </div>
        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="col-12">
        <div class="empty-box">

            <i class="bi bi-search text-success"
               style="font-size: 50px;"></i>

            <h5 class="mt-3">
                Không tìm thấy món đồ phù hợp
            </h5>

            <p class="text-muted">
                Bạn hãy thử từ khóa, danh mục hoặc địa điểm khác nhé.
            </p>

        </div>
    </div>

<?php endif; ?>