<?php
$e = static fn($v) => htmlspecialchars(
    (string)($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$dispute = $dispute ?? [];
$messages = is_array($messages ?? null) ? $messages : [];
$csrfToken = $csrfToken ?? '';
$isAdmin = $isAdmin ?? false;

$id = (int)($dispute['id'] ?? 0);
$status = $dispute['status'] ?? '';
?>

<div class="container py-4" style="max-width: 950px;">

    <h2 class="text-success fw-bold mb-4">
        Chi tiết khiếu nại #<?= $id ?>
    </h2>

    <?php if (!empty($_SESSION['dispute_error'])): ?>
        <div class="alert alert-danger">
            <?= $e($_SESSION['dispute_error']) ?>
        </div>
        <?php unset($_SESSION['dispute_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['dispute_notice'])): ?>
        <div class="alert alert-success">
            <?= $e($_SESSION['dispute_notice']) ?>
        </div>
        <?php unset($_SESSION['dispute_notice']); ?>
    <?php endif; ?>

    <!-- Thông tin khiếu nại -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold">Thông tin khiếu nại</h5>

            <p><strong>Món đồ:</strong>
                <?= $e($dispute['item_title'] ?? '') ?>
            </p>

            <p><strong>Lý do:</strong>
                <?= $e($dispute['reason'] ?? '') ?>
            </p>

            <p><strong>Mô tả:</strong>
                <?= nl2br($e($dispute['description'] ?? '')) ?>
            </p>

            <p><strong>Trạng thái:</strong>
                <span class="badge bg-info text-dark">
                    <?= $e($status) ?>
                </span>
            </p>

            <?php if (!empty($dispute['resolution_note'])): ?>
                <div class="alert alert-success">
                    <strong>Kết quả xử lý:</strong>
                    <?= nl2br($e($dispute['resolution_note'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lịch sử trao đổi -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Trao đổi khiếu nại</h5>

            <?php if (!$messages): ?>
                <p class="text-muted">Chưa có tin nhắn nào.</p>
            <?php endif; ?>

            <?php foreach ($messages as $message): ?>
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>
                            <?= $e($message['sender_name'] ?? 'Thành viên') ?>
                        </strong>
                        <small class="text-muted">
                            <?= $e($message['created_at'] ?? '') ?>
                        </small>
                    </div>

                    <div class="mt-2">
                        <?= nl2br($e($message['content'] ?? '')) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (in_array($status, ['open', 'reviewing'], true)): ?>
                <form
                    action="index.php?controller=Dispute&action=message"
                    method="POST"
                    class="mt-3"
                >
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="dispute_id"
                           value="<?= $id ?>">

                    <textarea name="content"
                              class="form-control mb-2"
                              rows="3"
                              maxlength="3000"
                              placeholder="Nhập nội dung trao đổi..."
                              required></textarea>

                    <button type="submit" class="btn btn-success">
                        Gửi tin nhắn
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin xử lý khiếu nại -->
    <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">
                    Xử lý khiếu nại
                </h5>

                <form action="index.php?controller=Dispute&action=update"
                      method="POST">

                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="dispute_id"
                           value="<?= $id ?>">

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>

                        <select name="status" class="form-select" required>
                            <?php foreach ([
                                'open' => 'Mới gửi',
                                'reviewing' => 'Đang xem xét',
                                'resolved' => 'Đã giải quyết',
                                'closed' => 'Đã đóng'
                            ] as $value => $label): ?>
                                <option value="<?= $e($value) ?>"
                                    <?= $status === $value ? 'selected' : '' ?>>
                                    <?= $e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ghi chú xử lý
                        </label>
                        <textarea name="resolution_note"
                                  class="form-control"
                                  rows="4"
                                  maxlength="5000"><?= $e($dispute['resolution_note'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        Cập nhật khiếu nại
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <a href="<?= $isAdmin
        ? 'index.php?controller=Dispute&action=admin'
        : 'index.php?controller=Dispute&action=index' ?>"
       class="btn btn-outline-secondary">
        Quay lại danh sách
    </a>
</div>
