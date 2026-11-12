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
        /* 1. Nền tổng thể và hoa */
        body {
            background-color: #f9fbf0;
            background-image: 
                url("data:image/svg+xml,%3Csvg viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='50' cy='50' r='14' fill='%23d3ebd9'/%3E%3Ccircle cx='50' cy='22' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='77' cy='41' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='66' cy='73' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='34' cy='73' r='20' fill='%23d3ebd9'/%3E%3Ccircle cx='23' cy='41' r='20' fill='%23d3ebd9'/%3E%3C/svg%3E"),
                linear-gradient(135deg, #e0f2e9 0%, #f9fbf0 50%, #e0f2e9 100%) !important;
            background-position: right 2% top 5%, center;
            background-repeat: no-repeat, no-repeat;
            background-size: 200px, cover;
            background-attachment: fixed !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* 2. Sidebar chung */
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
        .sidebar a { color: #6c757d; transition: all 0.3s ease; text-decoration: none; }
        .sidebar a:hover {
            background: rgba(25, 135, 84, 0.1) !important; 
            border-radius: 8px; transform: translateX(5px); color: #198754 !important;
        }
        .sidebar a.active {
            color: #198754 !important; font-weight: 700 !important;
            background: rgba(25, 135, 84, 0.05); border-radius: 8px; border-left: 4px solid #198754;
        }

        /* 3. Thẻ, Header và Input */
        .card, .bg-white {
            background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(8px) !important;
            border: 1px solid rgba(255, 255, 255, 0.6) !important; border-radius: 16px !important; 
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.05) !important; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(25, 135, 84, 0.1) !important; }
        
        .top-header { 
            background: rgba(255, 255, 255, 0.6) !important; backdrop-filter: blur(10px) !important; border: 1px solid rgba(255, 255, 255, 0.4) !important;
        }
        .form-control { border-radius: 20px !important; border: 1px solid rgba(0,0,0,0.1) !important; background: rgba(255, 255, 255, 0.7) !important; transition: all 0.3s ease; }
        .form-control:focus { background: #fff !important; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25) !important; border-color: #198754 !important; }

        /* 4. CSS DÀNH RIÊNG CHO MOBILE (Responsive) */
        @media (max-width: 991.98px) {
            body {
                background-size: 100px, cover; /* Thu nhỏ hoa */
                background-position: right -15px top -10px, center;
            }
            .sidebar {
                width: 100% !important; 
                min-height: auto !important; 
                border-right: none !important;
                border-radius: 16px;
                margin-bottom: 15px;
            }
            
            /* Sửa lỗi hiển thị Collapse trên mobile */
            .sidebar.collapse:not(.show) {
                display: none !important;
            }
            .sidebar.collapse.show {
                display: flex !important;
            }
            
           
            .main-content { padding: 10px !important; }
            
           
            
           
            .main-content h2, .main-content .h2 { font-size: 1.4rem !important; margin-bottom: 10px !important; line-height: 1.4; }
            .main-content .text-muted { font-size: 0.9rem !important; }
            .main-content .badge { display: inline-block; white-space: normal; margin-top: 5px; line-height: 1.4; }
            
           
            .card h3, .card .display-6, .card .fs-1 { font-size: 1.7rem !important; }
            
            .main-content .container-fluid > .d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px !important;
            }
            
            .main-content .container-fluid > .d-flex > div:last-child {
                width: 100% !important;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
          .main-content .btn-group {
                display: flex !important;
                width: 100% !important;
                justify-content: space-between; 
            }
            .main-content .btn-group .btn {
                flex: 1; 
                font-size: 0.8rem !important;
                padding: 10px 5px !important; 
                text-align: center !important; 
                white-space: nowrap !important;
                display: flex; 
                align-items: center; 
                justify-content: center;
                border-radius: 8px !important; 
            }
            
            /* 6. Nút Xuất Excel*/
            .main-content a.btn-success {
                width: 100%;
                display: block;
                text-align: center;
                padding: 10px !important;
                font-size: 0.9rem !important;
            }
        }
    </style>
</head>
<body>
    <?php $currentAction = $_GET['action'] ?? 'dashboard'; ?>

    <div class="d-flex flex-column flex-lg-row">
        
        <!-- HEADER MOBILE: NÚT HAMBURGER (Đã sửa data-bs-target chuẩn) -->
        <div class="d-flex d-lg-none justify-content-between align-items-center p-3 m-2 bg-white rounded-4 shadow-sm">
            <div class="fs-4 fw-bold text-success d-flex align-items-center">
                <i class="bi bi-recycle me-2"></i> Give & Take
            </div>
            <!-- Đổi sang style nút màu xanh đậm với icon list -->
            <button class="btn btn-success border-0 p-1 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebarMobile" aria-expanded="false" aria-controls="adminSidebarMobile">
                <i class="bi bi-list fs-2 text-white px-1"></i>
            </button>
        </div>

        <!-- SIDEBAR (Đã thêm class collapse và id đồng bộ với nút Hamburger) -->
        <aside class="sidebar p-3 flex-column collapse d-lg-flex mx-2 mx-lg-0 mt-0 mt-lg-0" id="adminSidebarMobile">
            <!-- LOGO DESKTOP -->
            <div class="d-none d-lg-flex fs-4 fw-bold text-success mb-4 text-center mt-2 justify-content-center align-items-center">
                <i class="bi bi-recycle me-2" style="font-size: 1.2rem;"></i> Give & Take
            </div>
            
            <nav class="nav flex-column flex-grow-1">
                <a href="index.php?controller=Admin&action=dashboard" class="nav-link px-3 py-2 mb-1 d-flex align-items-center <?= ($currentAction == 'dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-graph-up me-3 fs-5"></i> <span style="margin-left: 2px;">Tổng quan</span>
                </a>
                <a href="index.php?controller=Admin&action=products" class="nav-link px-3 py-2 mb-1 d-flex align-items-center <?= ($currentAction == 'products') ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-3 fs-5"></i> <span style="margin-left: 2px;">Quản lý bài đăng</span>
                </a>
                <a href="index.php?controller=Admin&action=users" class="nav-link px-3 py-2 mb-1 d-flex align-items-center <?= ($currentAction == 'users') ? 'active' : '' ?>">
                    <i class="bi bi-people me-3 fs-5"></i> <span style="margin-left: 2px;">Quản lý tài khoản</span>
                </a>
                <a href="index.php?controller=Admin&action=transactions" class="nav-link px-3 py-2 mb-1 d-flex align-items-center <?= ($currentAction == 'transactions') ? 'active' : '' ?>">
                    <i class="bi bi-arrow-left-right me-3 fs-5"></i> <span style="margin-left: 2px;">Lịch sử giao dịch</span>
                </a>
         
                <hr class="text-secondary my-3 mx-3 opacity-25">
                
                <a href="index.php?controller=Auth&action=logout" class="nav-link text-danger fw-bold px-3 py-2 mb-1 d-flex align-items-center" onclick="return confirm('Bạn có muốn đăng xuất không?');">
                    <i class="bi bi-box-arrow-right me-3 fs-5"></i> Đăng xuất
                </a>
                
                <a href="index.php" class="nav-link px-3 py-2 mb-1 d-flex align-items-center" style="color: #198754; font-weight: 600;">
                    <i class="bi bi-house-door me-3 fs-5"></i> Về trang chủ
                </a>
            </nav>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <main class="main-content flex-grow-1 p-4" style="min-width: 0;">
            <!-- TOPBAR -->
            <header class="d-flex justify-content-between align-items-center top-header p-3 rounded-4 shadow-sm mb-4">
                <div class="position-relative w-100" style="max-width: 320px;">
                    <i class="bi bi-search position-absolute text-muted" style="top: 50%; left: 16px; transform: translateY(-50%);"></i>
                    <input type="text" class="form-control rounded-pill py-2 border-0 shadow-sm" style="padding-left: 45px;" placeholder="Tìm kiếm thông tin...">
                </div>
                
                <div class="fw-bold text-secondary d-flex align-items-center mt-2 mt-lg-0">
                    Xin chào, Admin <i class="bi bi-person-circle fs-3 ms-2 text-success"></i>
                </div>
            </header>

            <!-- Nội dung động -->
            <?php echo $content ?? ''; ?>

        </main>
    </div>
    
    <!-- JS của Bootstrap cực kỳ quan trọng để Nút Hamburger hoạt động -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>