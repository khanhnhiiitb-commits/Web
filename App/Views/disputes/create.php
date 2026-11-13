<?php
$e = static fn($v) => htmlspecialchars(
    (string)($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$request = $request ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container py-4" style="max-width: 850px;">

    <h2 class="text-success fw-bold mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Gửi khiếu nại
    </h2>

    <p class="text-muted">
        Vui lòng cung cấp thông tin để quản trị viên
        xem xét và xử lý khiếu nại.
    </p>

    <?php if (!empty($_SESSION['dispute_error'])): ?>
        <div class="alert alert-danger">
            <?= $e($_SESSION['dispute_error']) ?>
        </div>
        <?php unset($_SESSION['dispute_error']); ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Thông tin giao dịch
            </h5>

            <p>
                <strong>Món đồ:</strong>
                <?= $e($request['item_title'] ?? 'Chưa cập nhật') ?>
            </p>

            <p>
                <strong>Mã yêu cầu:</strong>
                #<?= (int)($request['id'] ?? 0) ?>
            </p>

            <hr>

            <form
                action="index.php?controller=Dispute&action=store"
                method="POST"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $e($csrfToken) ?>"
                >

                <input
                    type="hidden"
                    name="request_id"
                    value="<?= (int)($request['id'] ?? 0) ?>"
                >

                <div class="mb-3">
                    <label for="reason" class="form-label fw-semibold">
                        Lý do khiếu nại
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="reason"
                        name="reason"
                        minlength="5"
                        maxlength="255"
                        placeholder="Ví dụ: Món đồ không đúng mô tả"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">
                        Mô tả chi tiết
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                        maxlength="5000"
                        placeholder="Trình bày cụ thể vấn đề gặp phải..."
                    ></textarea>
                </div>

                <div class="d-flex justify-content-between gap-2">
                    <a
                        href="index.php?controller=Request&action=history"
                        class="btn btn-outline-secondary"
                    >
                        Quay lại
                    </a>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="bi bi-send me-1"></i>
                        Gửi khiếu nại
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
