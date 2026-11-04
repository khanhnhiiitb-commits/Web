<?php
session_start();
require_once '../../Config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM items i JOIN categories c ON i.category_id = c.id WHERE i.user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đồ đã đăng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-success mb-4 border-bottom border-success pb-2">Các món đồ tôi đã chia sẻ</h2>
    <a href="post-product.php" class="btn btn-success mb-4">+ Chia sẻ đồ mới</a>
    
    <div class="table-responsive">
        <table class="table table-bordered border-success table-hover">
            <thead class="table-success">
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($my_items as $item): ?>
                <tr>
                    <td><img src="uploads/<?= htmlspecialchars($item['image_url']) ?>" style="width: 80px; height: 80px; object-fit: cover;" class="rounded"></td>
                    <td class="align-middle fw-bold text-success"><?= htmlspecialchars($item['title']) ?></td>
                    <td class="align-middle"><?= htmlspecialchars($item['category_name']) ?></td>
                    <td class="align-middle">
                        <?php if($item['status'] == 'available'): ?>
                            <span class="badge bg-success">Đang chia sẻ</span>
                        <?php elseif($item['status'] == 'gifted'): ?>
                            <span class="badge bg-secondary">Đã tặng</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Đang ẩn/Chờ</span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>