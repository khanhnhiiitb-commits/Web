<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sống Xanh Sharing - Admin Dashboard</title>
    <!-- Nhúng Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Nhúng Icon Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
 <style>
    body {
        background: linear-gradient(160deg, #e8f5e9 0%, #f4f6f9 50%, #f4f6f9 100%) !important;
        background-attachment: fixed; /* Giữ nền cố định khi cuộn */
    }
    
    .sidebar {
        background-color: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .card, .bg-white {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03) !important;
        border: none !important;
    }
</style>
</head>
<body>
    <div class="d-flex">
        <!-- SIDEBAR -->
        <aside class="sidebar shadow-sm p-3">
            <div class="fs-4 fw-bold text-success mb-4 text-center">
                🌱 Sống Xanh Admin
            </div>
       <nav class="nav flex-column">
                <a class="nav-link px-3 py-2" href="index.php?controller=Admin&action=dashboard"><i class="bi bi-graph-up pe-2"></i>Tổng quan</a>
                <a class="nav-link px-3 py-2" href="index.php?controller=Admin&action=products"><i class="bi bi-box-seam pe-2"></i>Quản lý bài đăng</a>
                <a class="nav-link px-3 py-2" href="index.php?controller=Admin&action=users"><i class="bi bi-people pe-2"></i>Quản lý tài khoản</a>
                <a class="nav-link px-3 py-2" href="index.php?controller=Admin&action=transactions"><i class="bi bi-arrow-left-right pe-2"></i>Lịch sử giao dịch</a>
         
                <hr class="text-secondary my-2 mx-3">
                <a class="nav-link px-3 py-2 text-danger fw-bold" href="index.php?controller=Auth&action=logout" onclick="return confirm('Bạn có muốn đăng xuất không?');">
                    <i class="bi bi-box-arrow-right pe-2"></i>Đăng xuất
                </a>
                <!-- Nút quay về trang chủ -->
<li class="nav-item mb-2" style="list-style: none;">
    <a href="index.php" class="nav-link text-success fw-semibold text-decoration-none d-flex align-items-center py-2 px-3">
        <i class="bi bi-house-door me-2"></i> Về trang chủ
    </a>
</li>
            </nav>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <main class="flex-grow-1 p-4">
            <!-- TOPBAR -->
            <header class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm mb-4">
                <div class="input-group w-25">
                    <span class="input-group-text bg-transparent rounded-start-pill border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control rounded-end-pill border-start-0 ps-0" placeholder="Tìm kiếm...">
                </div>
                <div class="fw-bold text-secondary">
                    Xin chào, Admin <i class="bi bi-person-circle fs-5 ms-2 text-success"></i>
                </div>
            </header>

            <?php echo $content ?? ''; ?>

        </main>
    </div>
    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>