<?php
$title = 'Quản lý người xin đồ - Give & Take';
?>

<style>
    .rq-header {
        background: linear-gradient(135deg, #e8f5ed 0%, #f6fbf8 100%);
        border: 1px solid #d3ebdc;
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
    }
    .rq-header-icon {
        width: 56px;
        height: 56px;
        flex: 0 0 56px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: #198754;
        color: #fff;
        font-size: 1.5rem;
        box-shadow: 0 6px 16px rgba(25, 135, 84, .25);
    }
    .rq-card {
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(20, 60, 40, .06);
        overflow: hidden;
    }
    .rq-table { margin-bottom: 0; }
    .rq-table thead th {
        background: #f1f8f4;
        color: #146c43;
        font-weight: 600;
        font-size: .875rem;
        border-bottom: 1px solid #d3ebdc;
        padding: .9rem 1rem;
        white-space: nowrap;
    }
    .rq-table tbody td {
        padding: 1rem;
        border-color: #eef3f0;
    }
    .rq-table tbody tr:hover > * { background-color: #f8fcfa; }
    .rq-item {
        font-weight: 600;
        color: #212529;
        min-width: 160px;
    }
    .rq-user {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 140px;
        font-weight: 500;
    }
    .rq-avatar {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e1f3e8;
        color: #198754;
        font-size: 1.15rem;
    }
    .rq-message {
        max-width: 280px;
        min-width: 160px;
        padding: .45rem .75rem;
        background: #f7f9f8;
        border-left: 3px solid #a9d9bd;
        border-radius: .25rem .6rem .6rem .25rem;
        color: #5c6762;
        font-style: italic;
        font-size: .9rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .rq-time { white-space: nowrap; line-height: 1.35; }
    .rq-time .rq-date { display: block; color: #343a40; font-size: .875rem; }
    .rq-time .rq-hour { display: block; color: #8a948f; font-size: .78rem; }
    .rq-actions .btn {
        border-radius: 50rem;
        font-weight: 600;
        font-size: .85rem;
        padding: .4rem .9rem;
        white-space: nowrap;
        transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .rq-actions .btn:focus-visible { box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .35); }
    .rq-empty {
        background: #fff;
        border: 1px dashed #bfdccb;
        border-radius: 1rem;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }
    .rq-empty-icon {
        width: 88px;
        height: 88px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eef7f1;
        font-size: 2.6rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 575.98px) {
        .rq-header { padding: 1.1rem; }
        .rq-header-icon { width: 46px; height: 46px; flex-basis: 46px; font-size: 1.25rem; }
        .rq-actions { flex-wrap: wrap; }
    }
</style>

<div class="container py-4">
    <div class="rq-header d-flex align-items-center gap-3 mb-4">
        <span class="rq-header-icon"><i class="fa-solid fa-users-rectangle"></i></span>
        <div>
            <h2 class="fw-bold text-success mb-1 fs-3">Quản lý yêu cầu nhận đồ</h2>
            <p class="text-muted mb-0">Xem và phê duyệt các yêu cầu nhận đồ từ cộng đồng cho những món đồ bạn đăng.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['request_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 border-start border-4 border-success shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['request_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="rq-empty">
            <div class="rq-empty-icon">📭</div>
            <h5 class="text-secondary fw-semibold">Chưa có yêu cầu nhận đồ nào đang chờ duyệt.</h5>
            <p class="text-muted small mb-0">Khi có thành viên muốn xin đồ của bạn, danh sách sẽ hiển thị tại đây.</p>
        </div>
    <?php else: ?>
        <div class="rq-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle rq-table">
                    <thead>
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
                            <?php $rid = (int)$req['request_id']; ?>
                            <tr>
                                <td class="rq-item">
                                    <?= htmlspecialchars($req['item_title']) ?>
                                </td>
                                <td>
                                    <div class="rq-user">
                                        <span class="rq-avatar"><i class="fa-solid fa-user"></i></span>
                                        <span><?= htmlspecialchars($req['requester_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rq-message" title="<?= htmlspecialchars($req['message'] ?: 'Không có') ?>">
                                        "<?= htmlspecialchars($req['message'] ?: 'Không có') ?>"
                                    </div>
                                </td>
                                <td class="rq-time">
                                    <span class="rq-date"><?= date('d/m/Y', strtotime($req['created_at'])) ?></span>
                                    <span class="rq-hour"><?= date('H:i', strtotime($req['created_at'])) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="rq-actions d-flex justify-content-center gap-2">
                                        <a class="btn btn-sm btn-outline-success" title="Nhắn tin"
                                           href="index.php?controller=Message&action=chat&id=<?= $rid ?>">
                                            <i class="bi bi-chat-dots me-1"></i> Chat
                                        </a>
                                        <form action="index.php?controller=request&action=accept&id=<?= $rid ?>" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn chắc chắn muốn đồng ý tặng món đồ này cho người dùng này chứ?');">
                                                <i class="fa-solid fa-check me-1"></i> Đồng ý
                                            </button>
                                        </form>
                                        <form action="index.php?controller=request&action=reject&id=<?= $rid ?>" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn muốn từ chối yêu cầu này?');">
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
        </div>
    <?php endif; ?>
</div>
