<?php
require_once '../../Config/config.php';
$sql = "SELECT i.*, c.name as category_name 
        FROM items i 
        JOIN categories c ON i.category_id = c.id 
        WHERE i.status = 'available' 
        ORDER BY i.created_at DESC LIMIT 12";
$items = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - Sống Xanh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar giả định -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">🌿 Sống Xanh Sharing</a>
        <a href="post-product.php" class="btn btn-light text-success fw-bold">Đăng đồ tặng</a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-success pb-2">
        <h2 class="text-success fw-bold">Đồ mới chia sẻ</h2>
        <a href="search.php" class="btn btn-outline-success">Tìm kiếm & Lọc</a>
    </div>
    
    <div class="row">
        <?php foreach($items as$item): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 border-success shadow-sm">
                <img src="uploads/<?= htmlspecialchars($item['image_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Hình ảnh">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-success"><?= htmlspecialchars($item['title']) ?></h5>
                    <span class="badge bg-success mb-2 w-50"><?= htmlspecialchars($item['category_name']) ?></span>
                    <p class="card-text small text-muted">📍 <?= htmlspecialchars($item['pickup_location']) ?></p>
                    <a href="product-detail.php?id=<?= $item['id'] ?>" class="btn btn-success mt-auto">Xem chi tiết</a>
                </div>
                <?php endforeach; 
            else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">Chưa có món đồ nào được đăng hoặc cơ sở dữ liệu đang trống.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>