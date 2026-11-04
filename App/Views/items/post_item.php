<?php
session_start();
require_once '../../Config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $condition_note = trim($_POST['condition_note']);
    $pickup_location = trim($_POST['pickup_location']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $image_url = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image_url);
    }

    $stmt = $pdo->prepare("INSERT INTO items (user_id, category_id, title, description, condition_note, image_url, pickup_location, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', NOW())");
    $stmt->execute([$user_id, $category_id, $title, $description, $condition_note, $image_url, $pickup_location]);
    
    header("Location: profile.php?msg=posted");
    exit();
}

$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng đồ chia sẻ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card border-success shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0 text-center">Đăng Món Đồ Chia Sẻ</h4>
        </div>
        <div class="card-body">
            <form action="post-product.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Tiêu đề món đồ</label>
                    <input type="text" name="title" class="form-control border-success" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-success fw-bold">Danh mục</label>
                        <select name="category_id" class="form-select border-success" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-success fw-bold">Tình trạng</label>
                        <input type="text" name="condition_note" class="form-control border-success" placeholder="Mới 90%, Sứt mẻ nhẹ..." required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Địa điểm giao nhận</label>
                    <input type="text" name="pickup_location" class="form-control border-success" placeholder="Ký túc xá, Quận 1..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Hình ảnh thực tế</label>
                    <input type="file" name="image" class="form-control border-success" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Mô tả chi tiết</label>
                    <textarea name="description" class="form-control border-success" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold">Đăng Bài Cùng Sống Xanh</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>