<!DOCTYPE html>
<html lang="vi">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sống Xanh Sharing - Admin Dashboard</title>
    <!-- Nhúng thư viện Bootstrap hoặc CSS tĩnh của bạn ở đây -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin_style.css">
</head>
<body>
    <div class="admin-container" style="display: flex;">
        <!-- SIDEBAR -->
        <aside class="sidebar" style="width: 250px; background: #f8f9fa; min-height: 100vh; padding: 20px;">
            <div class="logo" style="margin-bottom: 30px; font-weight: bold; color: #198754;">
                🌱 Sống Xanh Admin
            </div>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 15px;"><a href="<?= BASE_URL ?>admin/index" style="text-decoration: none; color: #333;">📊 Tổng quan</a></li>
                <li style="margin-bottom: 15px;"><a href="<?= BASE_URL ?>admin/manageItems" style="text-decoration: none; color: #333;">📦 Quản lý bài đăng</a></li>
                <li style="margin-bottom: 15px;"><a href="<?= BASE_URL ?>admin/manageUsers" style="text-decoration: none; color: #333;">👥 Quản lý tài khoản</a></li>
                <li style="margin-bottom: 15px;"><a href="<?= BASE_URL ?>admin/manageTransactions" style="text-decoration: none; color: #333;">🤝 Giao dịch & Trao đổi</a></li>
            </ul>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <main class="main-content" style="flex: 1; padding: 20px; background: #f1f4f6;">
            <!-- TOPBAR -->
            <header class="topbar" style="display: flex; justify-content: space-between; margin-bottom: 30px; background: #fff; padding: 15px; border-radius: 8px;">
                <div class="search-bar">
                    <input type="text" placeholder="Tìm kiếm đồ dùng, bài đăng..." style="padding: 5px 15px; width: 300px; border-radius: 20px; border: 1px solid #ccc;">
                </div>
                <div class="user-info">
                    <span>Xin chào, Admin</span>
                </div>
            </header>

            <!-- CHÈN VIEW ĐỘNG TỪ CONTROLLER -->
<?php 
if (isset($view)) {
    require_once '../App/Views/' . $view . '.php'; 
} else {
    echo "<p style='color:red'>Lỗi: Không tìm thấy giao diện (View) để hiển thị.</p>";
}
?>        </main>
    </div>
</body>
</html>