<?php
$title = 'Quản lý người xin đồ - Give & Take';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success"><i class="fa-solid fa-users-rectangle me-2"></i> Quản lý yêu cầu nhận đồ</h2>
            <p class="text-muted mb-0">Xem và phê duyệt các yêu cầu nhận đồ từ cộng đồng cho những món đồ bạn đăng.</p>
        </div>
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
            <div class="display-4 text-muted mb-3">📭</div>
            <h5 class="text-secondary fw-semibold">Chưa có yêu cầu nhận đồ nào đang chờ duyệt.</h5>
            <p class="text-muted small">Khi có thành viên muốn xin đồ của bạn, danh sách sẽ hiển thị tại đây.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success text-success-emphasis">
                    <tr>
                        <th>Món đồ của bạn</th>
                        <th>Người xin</th>
                        <th>Lời nhắn</th>
                        <th>Thời gian</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($req['item_title']) ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-user-circle text-success me-1"></i> 
                                <?= htmlspecialchars($req['requester_name']) ?>
                            </td>
                            <td class="text-muted fst-italic">
                                "<?= htmlspecialchars($req['message'] ?: 'Không có') ?>"
                            </td>
                            <td class="small text-muted">
                                <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="index.php?controller=request&action=accept&id=<?= $req['request_id'] ?>" method="POST" class="d-inline">
                                        <button type="submit" class="btn btn-success btn-sm fw-semibold px-3" onclick="return confirm('Bạn chắc chắn muốn đồng ý tặng món đồ này cho người dùng này chứ?');">
                                            <i class="fa-solid fa-check me-1"></i> Đồng ý
                                        </button>
                                    </form>
                                    <form action="index.php?controller=request&action=reject&id=<?= $req['request_id'] ?>" method="POST" class="d-inline">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Bạn muốn từ chối yêu cầu này?');">
                                            <i class="fa-solid fa-xmark me-1"></i> Từ chối
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

