<div class="container-fluid px-0">
    <h2 class="mb-4">Lịch sử giao dịch</h2>

    <div class="table-responsive shadow-sm rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-4">Mã GD</th>
                    <th class="py-3 px-4">Món đồ</th>
                    <th class="py-3 px-4">Người xin nhận</th>
                    <th class="py-3 px-4">Ngày yêu cầu</th>
                    <th class="py-3 px-4">Trạng thái</th>
                    <th class="py-3 px-4 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($transactions) && is_array($transactions) && count($transactions) > 0) { 
                    foreach ($transactions as $trans) { 
                ?>
                <tr>
                    <td class="py-3 px-4 fw-bold text-secondary">#<?php echo $trans['id']; ?></td>
                    <td class="py-3 px-4 fw-bold text-dark"><?php echo htmlspecialchars($trans['item_title'] ?? 'Món đồ đã xóa'); ?></td>
                    <td class="py-3 px-4 text-primary"><?php echo htmlspecialchars($trans['requester_name'] ?? 'Ẩn danh'); ?></td>
                    <td class="py-3 px-4 text-muted"><?php echo date('d/m/Y H:i', strtotime($trans['created_at'])); ?></td>
                    <td class="py-3 px-4">
                        <?php 
                        // Chuyển đổi trạng thái tiếng Anh sang tiếng Việt với màu sắc tương ứng
                        switch($trans['status']) {
                            case 'accepted':
                                echo '<span class="badge bg-success px-3 py-2 rounded-pill">Đồng ý tặng</span>';
                                break;
                            case 'rejected':
                                echo '<span class="badge bg-danger px-3 py-2 rounded-pill">Từ chối</span>';
                                break;
                            case 'cancelled':
                                echo '<span class="badge bg-secondary px-3 py-2 rounded-pill">Đã hủy</span>';
                                break;
                            default: // pending
                                echo '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Chờ duyệt</span>';
                        }
                        ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <form action="?controller=Admin&action=deleteTransaction" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch sử giao dịch này không?');">
                            <input type="hidden" name="id" value="<?php echo $trans['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger px-3">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    } 
                } else { 
                ?>
                <tr>
                    <td colspan="6" class="py-4 text-center text-muted">Hệ thống chưa có giao dịch nào.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>