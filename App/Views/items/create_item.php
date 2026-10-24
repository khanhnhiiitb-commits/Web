<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fa-solid fa-box-open me-2"></i> Đăng món đồ mới
            </div>
            <div class="card-body">
                <!-- Thuộc tính enctype cực kỳ quan trọng để upload file -->
                <form action="<?= BASE_URL ?>item/store" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên món đồ</label>
                        <input type="text" name="title" class="form-control" required placeholder="VD: Sách Lập trình PHP">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Danh mục</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tình trạng</label>
                            <input type="text" name="condition_note" class="form-control" required placeholder="VD: Mới 90%, Bị sờn góc...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ giao nhận</label>
                        <input type="text" name="pickup_location" class="form-control" required placeholder="VD: KTX ĐH Kinh Tế, Quận 5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hình ảnh thực tế</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Chấp nhận JPG, PNG, WEBP. Tối đa 2MB.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Mô tả công năng, kích thước, lưu ý khi sử dụng..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">Đăng Lên Chờ Duyệt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>