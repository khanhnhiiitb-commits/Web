<div class="container-fluid px-0" style="background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    
    <!-- Tiêu đề trang -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">Tổng quan vận hành hệ thống <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1 fs-6 ms-2">● Đang hoạt động ổn định</span></h2>
            <p class="text-secondary mb-0">Xin chào Admin, dưới đây là tình hình hoạt động của cộng đồng hôm nay.</p>
        </div>
        <div>
           <div class="btn-group shadow-sm me-3" role="group">
                <a href="?controller=Admin&action=dashboard" class="btn btn-white text-secondary bg-white border border-end-0">Hôm nay</a>
                <a href="?controller=Admin&action=dashboard" class="btn btn-white text-secondary bg-white border border-end-0">7 ngày qua</a>
                <a href="?controller=Admin&action=dashboard" class="btn btn-success fw-bold">Tháng này</a>
            </div>
<a href="index.php?controller=Admin&action=exportExcel" class="btn btn-success shadow-sm fw-bold"><i class="bi bi-file-earmark-excel"></i> Xuất báo cáo Excel</a>        </div>
    </div>

    <!-- 4 Thẻ Thống Kê (Dashboard Cards) -->
    <div class="row g-4 mb-4">
        <!-- Tổng tài khoản -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 position-relative">
                    <p class="text-secondary fw-semibold mb-2">Tổng số tài khoản</p>
                    <h3 class="display-6 fw-bold text-dark mb-3"><?php echo number_format($totalUsers ?? 0); ?></h3>
                    <p class="text-success small fw-semibold mb-0">
                        <i class="bi bi-graph-up-arrow"></i> +12.5% <span class="text-secondary fw-normal">so với tháng trước</span>
                    </p>
                    <div class="position-absolute top-0 end-0 mt-4 me-4 bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng bài đăng -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 position-relative">
                    <p class="text-secondary fw-semibold mb-2">Tổng số bài đăng</p>
                    <h3 class="display-6 fw-bold text-dark mb-3"><?php echo number_format($totalPosts ?? 0); ?></h3>
                    <p class="text-success small fw-semibold mb-0">
                        <i class="bi bi-graph-up-arrow"></i> +8.3% <span class="text-secondary fw-normal">285 bài đăng tuần này</span>
                    </p>
                    <div class="position-absolute top-0 end-0 mt-4 me-4 bg-info bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-box-seam text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Giao dịch thành công -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 position-relative">
                    <p class="text-secondary fw-semibold mb-2">Tổng giao dịch thành công</p>
                    <h3 class="display-6 fw-bold text-dark mb-3"><?php echo number_format($totalRequests ?? 0); ?></h3>
                    <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-1">Tỉ lệ hoàn thành 88%</span>
                    <span class="text-secondary small ms-2">Xuất sắc</span>
                    <div class="position-absolute top-0 end-0 mt-4 me-4 bg-success bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-handshake text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bài chờ duyệt -->
        <div class="col-md-3">
            <div class="card h-100 border shadow-sm rounded-4" style="border-color: #ffdce0 !important;">
                <div class="card-body p-4 position-relative">
                    <p class="text-secondary fw-semibold mb-2">Bài chờ duyệt <span class="text-danger">●</span></p>
                    <h3 class="display-6 fw-bold text-dark mb-3"><?php echo number_format($pendingRequests ?? 0); ?></h3>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger small fw-semibold">Cần xử lý ngay</span>
<a href="index.php?controller=Admin&action=products" class="btn btn-sm btn-light text-primary fw-semibold rounded-pill px-3">Duyệt ngay →</a>                    </div>
                    <div class="position-absolute top-0 end-0 mt-4 me-4 bg-danger bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-clipboard2-x text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng chứa Biểu đồ và Danh sách -->
    <div class="row g-4">
        
        <!-- Cột trái: Biểu đồ và Giao dịch gần đây -->
        <div class="col-md-8">
            
            <!-- Biểu đồ -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Biểu đồ xu hướng trao đổi & Đăng tin</h5>
                            <p class="text-secondary small mb-0">So sánh lượt chia sẻ đồ dùng và số lần trao đổi hoàn tất theo từng tuần</p>
                        </div>
                        <div>
                            <span class="badge bg-success rounded-pill me-2"><i class="bi bi-circle-fill small"></i> Đăng tặng</span>
                            <span class="badge bg-info bg-opacity-25 text-info rounded-pill"><i class="bi bi-circle-fill small"></i> Thành công</span>
                        </div>
                    </div>
                    
                    <div class="bg-light rounded d-flex align-items-end p-3 position-relative" style="height: 250px; overflow: hidden;">
                        <svg class="position-absolute w-100 h-100" style="left:0; top:0; z-index: 1;">
                            <path d="M0,150 Q100,50 300,120 T600,100 T900,30" fill="none" stroke="#198754" stroke-width="3"/>
                            <path d="M0,200 Q150,180 350,150 T700,120 T900,80" fill="none" stroke="#0dcaf0" stroke-width="3"/>
                        </svg>
                        <div class="w-100 d-flex justify-content-between text-secondary small" style="z-index: 2;">
                            <span>Tuần 1</span><span>Tuần 2</span><span>Tuần 3</span><span>Tuần 4</span>
                        </div>
                    </div>

                    <div class="mt-4 bg-info bg-opacity-10 rounded-3 p-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded p-2 me-3 shadow-sm text-success fw-bold">CO₂</div>
                            <div>
                                <h6 class="mb-0 fw-bold">Tác động môi trường đo lường</h6>
                                <small class="text-secondary">Lối sống xanh chia sẻ giúp cắt giảm rác thải thứ cấp</small>
                            </div>
                        </div>
                        <h4 class="text-success fw-bold mb-0">-4.2 <span class="fs-6 fw-normal">Tấn CO2 tuần qua</span></h4>
                    </div>
                </div>
            </div>

            <!-- Giao dịch gần đây -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Giao dịch & Trao đổi gần đây</h5>
                            <p class="text-secondary small mb-0">Các hoạt động trao tặng và chia sẻ vòng tuần hoàn mới hoàn tất</p>
                        </div>
                        <a href="<?= BASE_URL ?>index.php?controller=Admin&action=transactions" class="text-decoration-none fw-semibold text-primary">Xem tất cả giao dịch ></a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle text-secondary mb-0">
                            <thead class="bg-light text-secondary small">
                                <tr>
                                    <th class="ps-3 border-0 py-2">MÓN ĐỒ TRAO ĐỔI</th>
                                    <th class="border-0 py-2">NGƯỜI CHIA SẺ</th>
                                    <th class="border-0 py-2">NGƯỜI NHẬN</th>
                                    <th class="border-0 py-2">TRẠNG THÁI</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold">
                                <?php if (!empty($recentRequests)) { 
                                    foreach ($recentRequests as $req) { ?>
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-3"><i class="bi bi-box fs-4 text-secondary"></i></div>
                                            <div class="text-dark">Đơn yêu cầu #<?php echo $req['id']; ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($req['full_name'] ?? 'Ẩn danh'); ?></td>
                                    <td><?php echo htmlspecialchars($req['requester_name'] ?? 'Chưa rõ'); ?></td>
                                    <td>
                                        <?php if (($req['status'] ?? '') === 'completed') { ?>
                                            <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>
                                        <?php } else { ?>
                                            <span class="badge bg-info bg-opacity-25 text-info rounded-pill px-3 py-2"><i class="bi bi-arrow-repeat me-1"></i><?php echo $req['status']; ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="4" class="text-center py-4">Chưa có giao dịch gần đây.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Cột phải: Danh mục và Chờ duyệt -->
        <div class="col-md-4">
            <!-- Danh mục đồ dùng -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Danh mục đồ dùng</h5>
                        <span class="badge bg-light text-secondary rounded-pill">Toàn hệ thống</span>
                    </div>
                    
                    <div class="d-flex justify-content-center mb-4 position-relative">
                        <div class="rounded-circle border border-5 border-success d-flex flex-column justify-content-center align-items-center" style="width: 150px; height: 150px; border-top-color: #0dcaf0 !important;">
                            <h4 class="fw-bold text-dark mb-0"><?php echo number_format($totalPosts ?? 0); ?></h4>
                            <small class="text-secondary">món đồ</small>
                        </div>
                    </div>
                    
                    <ul class="list-group list-group-flush small fw-semibold text-secondary">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pb-2">
                            <span><i class="bi bi-circle-fill text-success me-2"></i>Đồ điện tử</span>
                            <span>32% <span class="text-muted fw-normal">(1,222)</span></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pb-2">
                            <span><i class="bi bi-circle-fill text-info me-2"></i>Quần áo & Phụ kiện</span>
                            <span>28% <span class="text-muted fw-normal">(1,070)</span></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bài chờ duyệt List -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Chờ duyệt <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fs-6"><?php echo $pendingRequests ?? 0; ?> mới</span></h5>
                        <a href="<?= BASE_URL ?>index.php?controller=Admin&action=products" class="text-decoration-none fw-semibold text-primary small">Duyệt tất cả (<?php echo $pendingRequests ?? 0; ?>)</a>
                    </div>
                    
                    <?php 
                    if (!empty($recentPendingPosts)) { 
                        foreach ($recentPendingPosts as $post) { 
                    ?>
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded-3">
                        <div class="bg-white rounded p-3 me-3 border"><i class="bi bi-image text-secondary fs-4"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($post['title'] ?? 'Chưa có tên'); ?> <span class="badge bg-white text-secondary fw-normal border ms-1">Mới</span></h6>
                            <p class="text-secondary small mb-2">Người đăng: <?php echo htmlspecialchars($post['user_name'] ?? 'Ẩn danh'); ?></p>
                            <div class="d-flex gap-2">
                                <form action="<?= BASE_URL ?>index.php?controller=Admin&action=approveProduct" method="POST" class="w-100 m-0">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success fw-semibold w-100"><i class="bi bi-check2"></i> Duyệt nhanh</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <p class="text-center text-muted small py-3">Không có bài đăng nào đang chờ duyệt.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>