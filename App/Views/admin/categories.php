<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success">Quản lý Danh mục</h2>
        <button class="btn btn-success">+ Thêm danh mục mới</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Tên Danh Mục</th>
                        <th>Mô tả</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $cat['id'] ?></td>
                        <td class="fw-semibold text-primary"><?= htmlspecialchars($cat['name']) ?></td>
                        <td><?= htmlspecialchars($cat['description']) ?></td>
                        <td class="text-center">
                            <?php if($cat['status'] == 1): ?>
                                <span class="badge bg-success">Hiển thị</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Đã ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary">Sửa</button>
                            <button class="btn btn-sm btn-outline-danger">Xóa</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>