<?php
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 0);
$totalItems = (int) ($totalItems ?? 0);
?>

<?php if ($totalPages > 1): ?>

<nav aria-label="Phân trang sản phẩm" class="mt-4">
    <ul class="pagination justify-content-center flex-wrap">

        <!-- Trang trước -->
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <button
                type="button"
                class="page-link"
                data-page="<?= max(1, $page - 1) ?>"
                <?= $page <= 1 ? 'disabled' : '' ?>
            >
                <i class="bi bi-chevron-left"></i>
                Trước
            </button>
        </li>

        <!-- Các số trang -->
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        ?>

        <?php if ($startPage > 1): ?>
            <li class="page-item">
                <button type="button" class="page-link" data-page="1">1</button>
            </li>

            <?php if ($startPage > 2): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <button
                    type="button"
                    class="page-link"
                    data-page="<?= $i ?>"
                    <?= $i === $page ? 'aria-current="page"' : '' ?>
                >
                    <?= $i ?>
                </button>
            </li>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>

            <li class="page-item">
                <button
                    type="button"
                    class="page-link"
                    data-page="<?= $totalPages ?>"
                >
                    <?= $totalPages ?>
                </button>
            </li>
        <?php endif; ?>

        <!-- Trang tiếp theo -->
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <button
                type="button"
                class="page-link"
                data-page="<?= min($totalPages, $page + 1) ?>"
                <?= $page >= $totalPages ? 'disabled' : '' ?>
            >
                Sau
                <i class="bi bi-chevron-right"></i>
            </button>
        </li>

    </ul>
</nav>

<?php endif; ?>

<?php if ($totalItems > 0): ?>
    <p class="text-center text-muted small">
        Trang <?= $page ?> / <?= $totalPages ?> —
        Tổng <?= $totalItems ?> sản phẩm
    </p>
<?php endif; ?>