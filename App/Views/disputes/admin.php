<?php
$e = static fn($v) => htmlspecialchars(
    (string)($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$disputes = is_array($disputes ?? null) ? $disputes : [];

$statusLabels = [
    'open' => 'Mới gửi',
    'reviewing' => 'Đang xem xét',
    'resolved' => 'Đã giải quyết',
    'closed' => 'Đã đóng'
];
?>

<div class="container py-4">

    <h2 class="text-success fw-bold mb-4">
        <i class="bi bi-shield-exclamation me-2"></i>
        Quản lý khiếu nại
    </h2>

    <?php if (!empty($_SESSION['dispute_notice'])): ?>
        <div class="alert alert-success">
            <?= $e($_SESSION['dispute_notice']) ?>
        </div>
        <?php unset($_SESSION['dispute_notice']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['dispute_error'])): ?>
        <div class="alert alert-danger">
            <?= $e($_SESSION['dispute_error']) ?>
        </div>
        <?php unset($_SESSION['dispute_error']); ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Món đồ</th>
                            <th>Người khiếu nại</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($disputes as $d): ?>
                            <?php
                            $status = $d['status'] ?? '';

                            $label = $statusLabels[$status]
                                ?? $status;
                            ?>

                            <tr>
                                <td>#<?= (int)($d['id'] ?? 0) ?></td>

                                <td>
                                    <?= $e($d['item_title'] ?? '') ?>
                                </td>

                                <td>
                                    <?= $e($d['reporter_name'] ?? '') ?>
                                </td>

                                <td>
                                    <?= $e($d['reason'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= $e($label) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $e($d['created_at'] ?? '') ?>
                                </td>

                                <td>
                                    <a
                                        class="btn btn-sm btn-success"
                                        href="index.php?controller=Dispute&action=show&id=<?= (int)$d['id'] ?>"
                                    >
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        <?php if (empty($disputes)): ?>
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-4">
                                    Chưa có khiếu nại nào.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
