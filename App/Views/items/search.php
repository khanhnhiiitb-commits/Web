<?php
require_once '../../Config/config.php';

$keyword = $_GET['keyword'] ?? '';
$cat_id = $_GET['category_id'] ?? '';

$sql = "SELECT i.*, c.name as category_name FROM items i JOIN categories c ON i.category_id = c.id WHERE i.status = 'available'";
$params = [];

if ($keyword) {
    $sql .= " AND i.title LIKE ?";
    $params[] = "%$keyword%";
}
if ($cat_id) {
    $sql .= " AND i.category_id = ?";
    $params[] = $cat_id;
}
$sql .= " ORDER BY i.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm đồ tái sử dụng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-success mb-4">Tìm kiếm đồ cần nhận</h2>
    <form action="search.php" method="GET" class="row mb-4">
        <div class="col-md-6 mb-2">
            <input type="text" name="keyword" class="form-control border-success" placeholder="Tên món đồ..." value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="col-md-4 mb-2">
            <select name="category_id" class="form-select border-success">
                <option value="">Tất cả danh mục</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <button type="submit" class="btn btn-success w-100">Tìm kiếm</button>
        </div>
    </form>
    
    <div class="row">
        <?php foreach($items as $item): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 border-success">
                <img src="uploads/<?= htmlspecialchars($item['image_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title text-success"><?= htmlspecialchars($item['title']) ?></h5>
                    <a href="product-detail.php?id=<?= $item['id'] ?>" class="btn btn-success mt-auto w-100">Xem chi tiết</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($items)): ?>
            <div class="alert alert-success">Không tìm thấy món đồ nào phù hợp.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>