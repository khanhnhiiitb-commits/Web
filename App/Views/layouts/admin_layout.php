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
        background-color: #f9fbf0;
        background-image: 
            url("data:image/svg+xml,%3Csvg viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='50' cy='50' r='14' fill='%23d3ebd9'/%3E%3Ccircle cx='50' cy='22' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='77' cy='41' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='66' cy='73' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='34' cy='73' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='23' cy='41' r='20' fill='%23d3ebd9'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #e0f2e9 0%, #f9fbf0 50%, #e0f2e9 100%) !important;
        background-position: right 5% top 10%, center;
        background-repeat: no-repeat, no-repeat;
        background-size: 350px, cover;
        background-attachment: fixed !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar { 
        min-height: 100vh;
        width: 260px;
        background: rgba(255, 255, 255, 0.6) !important; 
        backdrop-filter: blur(12px) !important; 
        -webkit-backdrop-filter: blur(12px) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.3s ease;
    }

    .sidebar a:hover {
        background: rgba(25, 135, 84, 0.1) !important; 
        border-radius: 8px;
        transform: translateX(5px); 
        transition: all 0.3s ease;
        color: #198754 !important;
    }

    .card, .bg-white {
        background: rgba(255, 255, 255, 0.85) !important; 
        backdrop-filter: blur(8px) !important;
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        border-radius: 16px !important; 
        box-shadow: 0 8px 25px rgba(25, 135, 84, 0.05) !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(25, 135, 84, 0.1) !important;
    }

    .top-header { 
        background: rgba(255, 255, 255, 0.6) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    
    .form-control {
        border-radius: 20px !important;
        border: 1px solid rgba(0,0,0,0.1) !important;
        background: rgba(255, 255, 255, 0.7) !important;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        background: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25) !important;
        border-color: #198754 !important;
    }
 </style>
</head>
<body>
    <div class="d-flex">
        <!-- SIDEBAR -->
        <aside class="sidebar p-3 d-flex flex-column">
            <div class="fs-4 fw-bold text-success mb-4 text-center mt-2">
                🌱 Sống Xanh
            </div>
            
            <nav class="nav flex-column flex-grow-1">
                <!-- Các mục menu được căn chỉnh icon với d-flex align-items-center và margin me-3 -->
                <a class="nav-link text-success fw-bold px-3 py-2 mb-1 d-flex align-items-center" href="index.php?controller=Admin&action=dashboard">
                    <i class="bi bi-graph-up me-3 fs-5"></i> Tổng quan
                </a>
                <a class="nav-link text-secondary px-3 py-2 mb-1 d-flex align-items-center" href="index.php?controller=Admin&action=products">
                    <i class="bi bi-box-seam me-3 fs-5"></i> Quản lý bài đăng
                </a>
                <a class="nav-link text-secondary px-3 py-2 mb-1 d-flex align-items-center" href="index.php?controller=Admin&action=users">
                    <i class="bi bi-people me-3 fs-5"></i> Quản lý tài khoản
                </a>
                <a class="nav-link text-secondary px-3 py-2 mb-1 d-flex align-items-center" href="index.php?controller=Admin&action=transactions">
                    <i class="bi bi-arrow-left-right me-3 fs-5"></i> Lịch sử giao dịch
                </a>
         
                <hr class="text-secondary my-3 mx-3 opacity-25">
                
                <a class="nav-link text-danger fw-bold px-3 py-2 mb-1 d-flex align-items-center" href="index.php?controller=Auth&action=logout" onclick="return confirm('Bạn có muốn đăng xuất không?');">
                    <i class="bi bi-box-arrow-right me-3 fs-5"></i> Đăng xuất
                </a>
                <!-- Nút quay về trang chủ -->
                <a class="nav-link text-success fw-semibold px-3 py-2 mb-1 d-flex align-items-center" href="index.php">
                    <i class="bi bi-house-door me-3 fs-5"></i> Về trang chủ
                </a>
            </nav>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <main class="flex-grow-1 p-4" style="min-width: 0;">
            <!-- TOPBAR -->
            <header class="d-flex justify-content-between align-items-center top-header p-3 rounded-4 shadow-sm mb-4">
                <div class="position-relative" style="width: 280px;">
                    <i class="bi bi-search position-absolute text-muted" style="top: 50%; left: 16px; transform: translateY(-50%);"></i>
                    <input type="text" class="form-control rounded-pill py-2 border-0 shadow-sm" style="padding-left: 45px;" placeholder="Tìm kiếm thông tin...">
                </div>
                
                <div class="fw-bold text-secondary d-flex align-items-center">
                    Xin chào, Admin <i class="bi bi-person-circle fs-4 ms-2 text-success"></i>
                </div>
            </header>

            <!-- Nội dung động -->
            <?php echo $content ?? ''; ?>

        </main>
    </div>
    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>