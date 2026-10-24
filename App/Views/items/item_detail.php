<?php 
/** 
 * @var array $item 
 */
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="row">
    <!-- Cột trái: Hình ảnh món đồ -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <img src="<?= BASE_URL ?>public/uploads/items/<?= htmlspecialchars($item['image_url']) ?>" 
                 class="card-img-top rounded" 
                 alt="<?= htmlspecialchars($item['title']) ?>"
                 style="max-height: 500px; object-fit: cover;">
        </div>
    </div>

    <!-- Cột phải: Thông tin chi tiết -->
    <div class="col-md-6">
        <h2 class="fw-bold text-success mb-3"><?= htmlspecialchars($item['title']) ?></h2>
        
        <div class="mb-3">
            <span class="badge bg-success-subtle text-success fs-6 me-2"><?= htmlspecialchars($item['category_name']) ?></span>
            <span class="badge bg-secondary fs-6">Trạng thái: <?= htmlspecialchars($item['status']) ?></span>
        </div>

        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item px-0">
                <i class="fa-solid fa-clipboard-check text-muted me-2"></i> 
                <strong>Tình trạng:</strong> <?= htmlspecialchars($item['condition_note']) ?>
            </li>
            <li class="list-group-item px-0">
                <i class="fa-solid fa-location-dot text-danger me-2"></i> 
                <strong>Nơi nhận đồ:</strong> <?= htmlspecialchars($item['pickup_location']) ?>
            </li>
            <li class="list-group-item px-0">
                <i class="fa-solid fa-calendar-days text-muted me-2"></i> 
                <strong>Ngày đăng:</strong> <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
            </li>
        </ul>

        <div class="mb-4">
            <h5 class="fw-bold">Mô tả chi tiết:</h5>
            <p class="text-break" style="white-space: pre-line;">
                <?= htmlspecialchars($item['description']) ?>
            </p>
        </div>

        <!-- Khung hành động (Chỉ hiện form xin đồ nếu không phải chủ đồ) -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $item['user_id'] && $item['status'] == 'available'): ?>
            <div class="card border-success shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title text-success fw-bold">Gửi yêu cầu xin nhận đồ</h5>
                    <form action="<?= BASE_URL ?>request/send" method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Lời nhắn gửi chủ đồ:</label>
                            <textarea name="message" class="form-control" rows="3" required placeholder="Trình bày lý do bạn cần món đồ này hoặc hẹn thời gian có thể qua lấy..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <i class="fa-solid fa-paper-plane me-2"></i> Xin Món Đồ Này
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div class="alert alert-warning">
                Bạn cần <a href="<?= BASE_URL ?>auth/login" class="alert-link">đăng nhập</a> để xin món đồ này.
            </div>
        <?php elseif ($_SESSION['user_id'] == $item['user_id']): ?>
            <div class="alert alert-info">
                Đây là món đồ do chính bạn đăng tải.
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">
                Món đồ này hiện không còn ở trạng thái sẵn sàng trao tặng.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>