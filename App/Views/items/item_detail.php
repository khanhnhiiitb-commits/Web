<?php
session_start();
require_once '../../Config/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT i.*, c.name as category_name, u.full_name, u.phone 
                       FROM items i 
                       JOIN categories c ON i.category_id = c.id 
                       JOIN users u ON i.user_id = u.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) die("<div class='container mt-5'><h3 class='text-danger'>Món đồ không tồn tại!</h3></div>");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <a href="index.php" class="btn btn-outline-success mb-3">&larr; Quay lại trang chủ</a>
    <div class="card border-success shadow-sm">
        <div class="row g-0">
            <div class="col-md-6">
                <img src="uploads/<?= htmlspecialchars($item['image_url']) ?>" class="img-fluid rounded-start" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <div class="card-body">
                    <h2 class="text-success fw-bold"><?= htmlspecialchars($item['title']) ?></h2>
                    <span class="badge bg-success fs-6 mb-3"><?= htmlspecialchars($item['category_name']) ?></span>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item px-0"><strong>Tình trạng:</strong> <span class="text-success"><?= htmlspecialchars($item['condition_note']) ?></span></li>
                        <li class="list-group-item px-0"><strong>Địa điểm nhận:</strong> <?= htmlspecialchars($item['pickup_location']) ?></li>
                        <li class="list-group-item px-0"><strong>Người tặng:</strong> <?= htmlspecialchars($item['full_name']) ?></li>
                    </ul>
                    
                    <h5 class="text-success">Mô tả:</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                    
                    <?php if ($item['status'] === 'available' && (!isset($_SESSION['user_id']) || $item['user_id'] != $_SESSION['user_id'])): ?>
                        <form action="request-item.php" method="POST" class="mt-4 bg-light p-3 border border-success rounded">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label text-success fw-bold">Lời nhắn xin đồ:</label>
                                <textarea name="message" class="form-control border-success" rows="2" placeholder="Ví dụ: Em là sinh viên, anh/chị cho em xin cuốn sách này nhé..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold">Gửi yêu cầu xin nhận đồ</button>
                        </form>
                    <?php elseif ($item['status'] === 'gifted'): ?>
                        <div class="alert alert-secondary mt-3">Món đồ này đã được tặng cho người khác.</div>
                    <?php else: ?>
                        <div class="alert alert-success mt-3">Đây là món đồ của bạn.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>