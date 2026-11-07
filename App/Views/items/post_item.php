<?php
function itemEscape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<div class="container py-5">
    <h2>Đăng món đồ muốn chia sẻ</h2>
    <p class="text-muted">
        Hãy cung cấp thông tin và hình ảnh rõ ràng để mọi người dễ dàng lựa chọn.
    </p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= itemEscape($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form
        action="index.php?controller=Item&action=store"
        method="POST"
        enctype="multipart/form-data"
    >
        <div class="mb-3">
            <label class="form-label">Tên món đồ</label>
            <input
                type="text"
                name="title"
                class="form-control"
                maxlength="200"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Chọn danh mục --</option>

                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>">
                        <?= itemEscape($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea
                name="description"
                class="form-control"
                rows="4"
                required
            ></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tình trạng món đồ</label>
            <input
                type="text"
                name="condition_note"
                class="form-control"
                maxlength="100"
                placeholder="Ví dụ: Còn mới 90%, sử dụng tốt..."
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh món đồ</label>
            <input
                type="file"
                name="image"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
                required
            >
            <small class="text-muted">
                Chỉ nhận JPG, PNG, WEBP; dung lượng tối đa 5MB.
            </small>
        </div>

        <div class="mb-3">
            <label class="form-label">Địa điểm nhận đồ</label>
            <input
                type="text"
                name="pickup_location"
                class="form-control"
                maxlength="255"
                placeholder="Ví dụ: Quận 1, TP. Hồ Chí Minh"
                required
            >
        </div>

        <button type="submit" class="btn btn-success">
            Đăng món đồ
        </button>

        <a href="index.php" class="btn btn-secondary">
            Quay lại
        </a>
    </form>
</div>