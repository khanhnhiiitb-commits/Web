<div class="container-fluid px-0">
    <h2 class="mb-4">Tổng quan vận hành hệ thống</h2>

    <div class="row g-4 mb-4">
        <!-- Thẻ Tổng tài khoản -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tổng số tài khoản</p>
                    <h3 class="text-success mb-2"><?php echo number_format($data['totalUsers'] ?? 0); ?></h3>
                    <small class="text-success">+12.5% so với tháng trước</small>
                </div>
            </div>
        </div>

        <!-- Thẻ Tổng bài đăng -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tổng số bài đăng</p>
                    <h3 class="text-dark mb-2"><?php echo number_format($data['totalPosts'] ?? 0); ?></h3>
                    <small class="text-success">+8.3% so với tuần trước</small>
                </div>
            </div>
        </div>

        <!-- Thẻ Giao dịch -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Giao dịch thành công</p>
                    <h3 class="text-dark mb-2"><?php echo number_format($data['totalRequests'] ?? 0); ?></h3>
                    <small class="text-success">Tỉ lệ hoàn thành 88%</small>
                </div>
            </div>
        </div>

        <!-- Thẻ Chờ duyệt -->
        <div class="col-md-3">
            <div class="card border border-warning shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 fw-bold">Bài chờ duyệt 🔴</p>
                    <h3 class="text-danger mb-2"><?php echo number_format($data['pendingRequests'] ?? 0); ?></h3>
                    <a href="<?= BASE_URL ?>admin/items" class="text-decoration-none">Duyệt ngay →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Khung chờ vẽ biểu đồ -->
    <div class="bg-white p-5 rounded shadow-sm d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
        [Khu vực vẽ Biểu đồ Chart.js theo thiết kế]
    </div>
</div>