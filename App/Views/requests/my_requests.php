<?php
$statusLabels = [
    'pending' => 'Đang chờ duyệt',
    'rejected' => 'Bị từ chối',
    'accepted' => 'Đã được cho',
    'cancelled' => 'Đã hủy'
];

$statusColors = [
    'pending' => 'warning',
    'rejected' => 'danger',
    'accepted' => 'success',
    'cancelled' => 'secondary'
];

function escapeRequestText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đồ mình đi xin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">Đồ mình đi xin</h2>

    <?php if (!empty($_SESSION['request_message'])): ?>
        <div class="alert alert-info">
            <?= escapeRequestText($_SESSION['request_message']) ?>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>STT</th>
                            <th>Tên món đồ</th>
                            <th>Lời nhắn đã gửi</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $index => $request): ?>
                            <?php
                            $status = $request['request_status'];
                            $label = $statusLabels[$status] ?? $status;
                            $color = $statusColors[$status] ?? 'secondary';
                            ?>

                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <?= escapeRequestText($request['item_title']) ?>
                                </td>

                                <td>
                                    <?= nl2br(escapeRequestText($request['message'])) ?>
                                </td>

                                <td>
                                    <span class="badge text-bg-<?= $color ?>">
                                        <?= escapeRequestText($label) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                Bạn chưa gửi yêu cầu nhận món đồ nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="<?= BASE_URL ?>item" class="btn btn-outline-secondary mt-3">
        Quay lại danh sách đồ
    </a>
</div>

</body>
</html>