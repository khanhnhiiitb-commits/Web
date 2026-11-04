<?php
function escapeManageRequestText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duyệt người xin đồ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-2">Duyệt người xin đồ của mình</h2>
    <p class="text-muted mb-4">
        Xem lời nhắn và chọn đồng ý hoặc từ chối yêu cầu nhận đồ.
    </p>

    <?php if (!empty($_SESSION['request_message'])): ?>
        <div class="alert alert-info">
            <?= escapeManageRequestText($_SESSION['request_message']) ?>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>STT</th>
                            <th>Món đồ</th>
                            <th>Người xin</th>
                            <th>Lời nhắn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $index => $request): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <?= escapeManageRequestText($request['item_title']) ?>
                                </td>

                                <td>
                                    <?= escapeManageRequestText($request['requester_name']) ?>
                                </td>

                                <td>
                                    <?= nl2br(escapeManageRequestText($request['message'])) ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form method="POST"
                                              action="<?= BASE_URL ?>request/accept/<?= (int) $request['request_id'] ?>"
                                              onsubmit="return confirm('Bạn có chắc muốn đồng ý cho món đồ này?');">
                                            <button type="submit"
                                                    class="btn btn-success">
                                                Đồng Ý Cho
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="<?= BASE_URL ?>request/reject/<?= (int) $request['request_id'] ?>"
                                              onsubmit="return confirm('Bạn có chắc muốn từ chối yêu cầu này?');">
                                            <button type="submit"
                                                    class="btn btn-danger">
                                                Từ Chối
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                Hiện không có yêu cầu nào đang chờ duyệt.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="<?= BASE_URL ?>item/myItems"
       class="btn btn-outline-secondary mt-3">
        Quay lại đồ của tôi
    </a>
</div>

</body>
</html>