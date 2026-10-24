<?php
$totalUsers = $totalUsers ?? 0;
$totalPosts = $totalPosts ?? 0;
$totalRequests = $totalRequests ?? 0;
$pendingRequests = $pendingRequests ?? 0;

$recentUsers = $recentUsers ?? [];
$recentRequests = $recentRequests ?? [];
?>

<main>
    <div class="container py-5">

        <!-- Tiêu đề trang -->
        <div class="row align-items-center mb-4">
            <div class="col-12 col-md-8">
                <h2 class="fw-bold text-success">Trang quản trị</h2>
                <p class="text-muted mb-0">
                    Tổng quan hoạt động của hệ thống Give &amp; Take.
                </p>
            </div>

            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-success px-3 py-2">
                    Quản trị viên
                </span>
            </div>
        </div>

        <!-- Các thẻ thống kê -->
        <div class="row g-4 mb-5">

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted mb-2">Tổng người dùng</div>
                        <h3 class="fw-bold text-success">
                            <?php echo (int) $totalUsers; ?>
                        </h3>
                        <p class="small text-muted mb-0">
                            Tài khoản trong hệ thống
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted mb-2">Tổng bài đăng</div>
                        <h3 class="fw-bold text-primary">
                            <?php echo (int) $totalPosts; ?>
                        </h3>
                        <p class="small text-muted mb-0">
                            Bài đăng chia sẻ đồ
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted mb-2">Tổng yêu cầu</div>
                        <h3 class="fw-bold text-info">
                            <?php echo (int) $totalRequests; ?>
                        </h3>
                        <p class="small text-muted mb-0">
                            Yêu cầu nhận hoặc mượn đồ
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted mb-2">Yêu cầu chờ xử lý</div>
                        <h3 class="fw-bold text-warning">
                            <?php echo (int) $pendingRequests; ?>
                        </h3>
                        <p class="small text-muted mb-0">
                            Đang chờ phản hồi
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bảng người dùng gần đây -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Người dùng gần đây</h4>
                    <a href="index.php?controller=admin&amp;action=users"
                       class="btn btn-outline-success btn-sm">
                        Quản lý người dùng
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($recentUsers)) { ?>
                                <?php foreach ($recentUsers as $recentUser) { ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars(
                                                $recentUser['full_name'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $recentUser['email'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php
                                            $role = $recentUser['role'] ?? 'member';
                                            echo $role === 'admin'
                                                ? 'Quản trị viên'
                                                : 'Thành viên';
                                            ?>
                                        </td>

                                        <td>
                                            <?php if ((int) ($recentUser['status'] ?? 0) === 1) { ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Đã khóa</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Chưa có dữ liệu người dùng.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bảng yêu cầu gần đây -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Yêu cầu gần đây</h4>
                    <a href="index.php?controller=admin&amp;action=requests"
                       class="btn btn-outline-success btn-sm">
                        Quản lý yêu cầu
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã yêu cầu</th>
                                <th>Người gửi</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($recentRequests)) { ?>
                                <?php foreach ($recentRequests as $request) { ?>
                                    <tr>
                                        <td>
                                            #<?php echo (int) ($request['id'] ?? 0); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $request['full_name'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php
                                            $status = $request['status'] ?? 'pending';

                                            if ($status === 'pending') {
                                                echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                                            } elseif ($status === 'accepted') {
                                                echo '<span class="badge bg-success">Đã chấp nhận</span>';
                                            } elseif ($status === 'rejected') {
                                                echo '<span class="badge bg-danger">Đã từ chối</span>';
                                            } elseif ($status === 'cancelled') {
                                                echo '<span class="badge bg-secondary">Đã hủy</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Không xác định</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Chưa có dữ liệu yêu cầu.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
