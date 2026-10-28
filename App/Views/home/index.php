<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Góc Sống Xanh</title>
    <!-- Nhúng Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .item-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .item-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Thanh Navbar đơn giản -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">🌿 Góc Sống Xanh</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white active" href="/">Trang chủ</a>
                <a class="nav-link text-white" href="/posts">Bảng tin</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
            <h2 class="text-success fw-bold m-0">Trao đổi đồ cũ</h2>
            <span class="text-muted">Cùng chia sẻ - Giảm rác thải</span>
        </div>
        
        <!-- Bộ lọc tìm kiếm -->
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm" placeholder="🔍 Tìm kiếm tên món đồ...">
            </div>
            <div class="col-md-5">
                <select id="categorySelect" class="form-select form-select-lg shadow-sm">
                    <option value="">Tất cả danh mục / khu vực</option>
                    <?php 
                    $cats = $categories ?? $data['categories'] ?? [];
                    foreach($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Lưới hiển thị sản phẩm -->
        <div class="row" id="itemList">
            <?php 
            $itemList = $items ?? $data['items'] ?? [];
            if (!empty($itemList)): 
                foreach($itemList as $item): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm item-card border-0">
                        <img src="<?= $item['image_url'] ?? 'https://via.placeholder.com/300x200?text=No+Image' ?>" class="card-img-top rounded-top" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($item['title'] ?? '') ?></h5>
                            <p class="card-text text-success mb-2"><small class="badge bg-success-subtle text-success"><?= htmlspecialchars($item['category_name'] ?? 'Khác') ?></small></p>
                            <p class="card-text text-muted small"><?= substr(htmlspecialchars($item['description'] ?? ''), 0, 50) ?>...</p>
                            <a href="/item/detail/<?= $item['id'] ?? 1 ?>" class="btn btn-outline-success mt-auto w-100">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; 
            else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">Chưa có món đồ nào được đăng hoặc cơ sở dữ liệu đang trống.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Script nhúng JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>