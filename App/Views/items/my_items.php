<?php 
/** 
 * @var array $items 
 */
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-success">Kho Đồ Của Tôi</h2>
    <a href="<?= BASE_URL ?>item/create" class="btn btn-success fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Đăng Đồ Mới
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-success">
                <tr>
                    <th scope="col" class="ps-3">Hình ảnh</th>
                    <th scope="col">Tên món đồ</th>
                    <th scope="col">Danh mục</th>
                    <th scope="col">Trạng thái</th>
                    <th scope="col">Ngày đăng</th>
                    <th scope="col" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($items)): ?>
                    <?php foreach($items as $item): ?>
                        <tr>
                            <td class="ps-3">
                                <img src="<?= BASE_URL ?>public/uploads/items/<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="Ảnh" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                            </td>
                            <td class="fw-bold text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($item['title']) ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($item['category_name']) ?></span>
                            </td>
                            <td>
                                <?php if($item['status'] == 'available'): ?>
                                    <span class="badge bg-success">Đang chia sẻ</span>
                                <?php elseif($item['status'] == 'gifted'): ?>
                                    <span class="badge bg-primary">Đã tặng</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($item['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>item/detail/<?= $item['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>item/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <!-- Nút duyệt yêu cầu xin đồ (Chuyển hướng sang RequestController) -->
                                    <a href="<?= BASE_URL ?>request/manage/<?= $item['id'] ?>" class="btn btn-sm btn-outline-warning" title="Xem người xin đồ">
                                        <i class="fa-solid fa-users"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Bạn chưa đăng món đồ nào. Hãy đăng món đồ đầu tiên để chia sẻ với cộng đồng!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>