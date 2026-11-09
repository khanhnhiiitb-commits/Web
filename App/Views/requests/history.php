<?php
$title = 'Lịch sử giao dịch - Give & Take';

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$received = is_array($received ?? null) ? $received : [];
$given    = is_array($given ?? null) ? $given : [];

// [class badge, chữ hiển thị]
$statusMap = [
    'pending'   => ['bg-warning text-dark', 'Đang chờ duyệt'],
    'accepted'  => ['bg-primary',           'Đã chấp nhận, chờ sắp xếp giao nhận'],
    // [BƯỚC 1 - MỚI] Trạng thái giao nhận
    'arranged' => ['bg-info text-dark', 'Đã sắp xếp giao nhận'],
    'in_progress' => ['bg-info text-dark', 'Đang giao nhận'],
    'awaiting_confirmation' => ['bg-warning text-dark', 'Chờ hai bên xác nhận'],
    'disputed' => ['bg-danger', 'Đang tranh chấp'],
    'completed' => ['bg-success',           'Hoàn thành'],
    'rejected'  => ['bg-danger',            'Bị từ chối'],
    'cancelled' => ['bg-secondary',         'Đã hủy'],
];

// Nút gửi POST (hủy / xác nhận) kèm hộp thoại xác nhận
$actionForm = function (string $action, int $id, string $label, string $btnClass, string $confirmMsg): void {
    echo '<form action="index.php?controller=request&action=' . $action . '&id=' . $id . '" method="POST" class="d-inline">';
    echo '<button type="submit" class="btn btn-sm ' . $btnClass . '" onclick="return confirm(\'' . $confirmMsg . '\');">' . $label . '</button>';
    echo '</form>';
};
?>

<div class="py-2">
    <h2 class="fw-bold text-success mb-1">
        <i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử giao dịch
    </h2>
    <p class="text-muted mb-4">Theo dõi các giao dịch đang chờ, đã xác nhận, hoàn thành và bị từ chối.</p>

    <?php if (isset($_SESSION['request_message'])): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <?= e($_SESSION['request_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-received" type="button">
                Tôi nhận (<?= count($received) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-given" type="button">
                Tôi tặng (<?= count($given) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TÔI NHẬN -->
        <div class="tab-pane fade show active" id="tab-received">
            <?php if (empty($received)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm text-muted">
                    Bạn chưa gửi yêu cầu nhận đồ nào.
                </div>
            <?php else: ?>
                <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th>Món đồ</th>
                                <th>Người tặng</th>
                                <th>Ngày gửi</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($received as $r): ?>
                                <?php [$cls, $txt] = $statusMap[$r['request_status']] ?? ['bg-secondary', 'Không xác định']; ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($r['item_title']) ?></td>
                                    <td><?= e($r['owner_name']) ?></td>
                                    <td class="small text-muted"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                                    <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-success me-1 mb-1"
                                           href="index.php?controller=Message&action=chat&id=<?= (int)$r['request_id'] ?>">
                                            <i class="bi bi-chat-dots"></i> Chat
                                        </a>
                                        <?php if (in_array($r['request_status'], ['accepted', 'arranged', 'in_progress', 'awaiting_confirmation'], true)): ?>
                                            <a class="btn btn-sm btn-outline-primary me-1 mb-1"
                                               href="index.php?controller=Delivery&amp;action=tracking&amp;id=<?= (int)$r['request_id'] ?>">
                                                <i class="bi bi-box-seam"></i> Xem giao nhận
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($r['request_status'] === 'pending'): ?>
                                            <?php $actionForm('cancel', (int)$r['request_id'], 'Hủy yêu cầu', 'btn-outline-danger', 'Bạn muốn hủy yêu cầu nhận món đồ này?'); ?>
                                        <?php elseif ($r['request_status'] === 'awaiting_confirmation'): // [BƯỚC 3A - SỬA] Chỉ xác nhận sau giai đoạn giao nhận ?>
                                            <?php if (empty($r['receiver_confirmed_at'])): ?>
                                                <?php $actionForm('confirmHandover', (int)$r['request_id'], 'Đã nhận được đồ', 'btn-success', 'Xác nhận bạn đã nhận được món đồ này?'); ?>
                                            <?php else: ?>
                                                <span class="small text-muted">Bạn đã xác nhận, chờ người tặng</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- TÔI TẶNG -->
        <div class="tab-pane fade" id="tab-given">
            <?php if (empty($given)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm text-muted">
                    Chưa có ai gửi yêu cầu cho đồ bạn đăng.
                </div>
            <?php else: ?>
                <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th>Món đồ</th>
                                <th>Người xin</th>
                                <th>Ngày gửi</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($given as $g): ?>
                                <?php [$cls, $txt] = $statusMap[$g['request_status']] ?? ['bg-secondary', 'Không xác định']; ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($g['item_title']) ?></td>
                                    <td><?= e($g['requester_name']) ?></td>
                                    <td class="small text-muted"><?= e(date('d/m/Y H:i', strtotime($g['created_at']))) ?></td>
                                    <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-success me-1 mb-1"
                                           href="index.php?controller=Message&action=chat&id=<?= (int)$g['request_id'] ?>">
                                            <i class="bi bi-chat-dots"></i> Chat
                                        </a>
                                        <?php if (in_array($g['request_status'], ['accepted', 'arranged', 'in_progress', 'awaiting_confirmation'], true)): ?>
                                            <a class="btn btn-sm btn-outline-primary me-1 mb-1"
                                               href="index.php?controller=Delivery&amp;action=tracking&amp;id=<?= (int)$g['request_id'] ?>">
                                                <i class="bi bi-truck"></i>
                                                <?= $g['request_status'] === 'accepted' ? 'Thiết lập giao nhận' : 'Theo dõi giao nhận' ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($g['request_status'] === 'pending'): ?>
                                            <a href="index.php?controller=request&action=manageMyItems"
                                               class="btn btn-sm btn-outline-success">Duyệt yêu cầu</a>
                                        <?php elseif ($g['request_status'] === 'awaiting_confirmation'): // [BƯỚC 3A - SỬA] Không xác nhận ngay sau khi chấp nhận ?>
                                            <?php if (empty($g['giver_confirmed_at'])): ?>
                                                <?php $actionForm('confirmHandover', (int)$g['request_id'], 'Đã trao đồ', 'btn-success', 'Xác nhận bạn đã trao món đồ này?'); ?>
                                            <?php else: ?>
                                                <span class="small text-muted">Bạn đã xác nhận, chờ người nhận</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>