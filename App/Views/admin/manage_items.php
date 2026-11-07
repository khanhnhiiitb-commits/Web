<div class="container-fluid px-0">
    <h2 class="mb-4">Kiểm duyệt & Quản lý bài đăng</h2>

    <!-- Khung bọc bảng có đổ bóng nhẹ và bo góc -->
    <div class="table-responsive shadow-sm rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-4">Món đồ</th>
                    <th class="py-3 px-4">Người đăng</th>
                    <th class="py-3 px-4">Danh mục</th>
                    <th class="py-3 px-4">Trạng thái</th>
                    <th class="py-3 px-4 text-center">Thao tác duyệt</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // 1. Kiểm tra biến $data['items'] có tồn tại và chứa dữ liệu hay không
                if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) { 
                    
                    // 2. Bắt đầu vòng lặp in ra từng bài đăng
                    foreach ($data['items'] as $item) { 
                ?>
                <tr>
                    <td class="py-3 px-4 fw-bold"><?php echo htmlspecialchars($item['title'] ?? 'Chưa có tên'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['user'] ?? 'Ẩn danh'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['category'] ?? 'Khác'); ?></td>
                    
                    <td class="py-3 px-4">
                        <?php if (($item['status'] ?? '') === 'pending') { ?>
                            <!-- Nhãn Chờ duyệt (Màu vàng) -->
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Chờ duyệt</span>
                        <?php } else { ?>
                            <!-- Nhãn Đã duyệt (Màu xanh) -->
                            <span class="badge bg-success px-3 py-2 rounded-pill">Đã duyệt</span>
                        <?php } ?>
                    </td>
                    
                    <td class="py-3 px-4">
                        <!-- Nhóm nút bấm xếp ngang có khoảng cách (gap-2) -->
                        <div class="d-flex justify-content-center gap-2">
                            <?php if (($item['status'] ?? '') === 'pending') { ?>
                                <button class="btn btn-success btn-sm px-3">Duyệt</button>
                                <button class="btn btn-secondary btn-sm px-3">Từ chối</button>
                            <?php } ?>
                            <button class="btn btn-danger btn-sm px-3">Xóa</button>
                        </div>
                    </td>
                </tr>
                <?php 
                    } // Kết thúc vòng lặp foreach
                    
                } else { 
                    // 3. Nếu không có dữ liệu (mảng rỗng)
                ?>
                <tr>
                    <td colspan="5" class="py-4 text-center text-muted">
                        Không có bài đăng nào để hiển thị.
                    </td>
                </tr>
                <?php 
                } // Kết thúc khối lệnh if
                ?>
            </tbody>
        </table>
    </div>
</div>