<div class="container-fluid px-0">
    <h2 class="mb-4">Quản lý giao dịch & Lịch sử trao đổi</h2>

    <div class="table-responsive shadow-sm rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-4">Mã Yêu Cầu</th>
                    <th class="py-3 px-4">Món đồ trao đổi</th>
                    <th class="py-3 px-4">Người nhận (Requester)</th>
                    <th class="py-3 px-4">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($data['transactions']) && is_array($data['transactions']) && count($data['transactions']) > 0) { 
                    foreach ($data['transactions'] as $txn) { 
                ?>
                <tr>
                    <td class="py-3 px-4 fw-bold text-secondary">#<?php echo $txn['id']; ?></td>
                    
                    <!-- Giả sử cột item_id là ID món đồ, vì bảng này chưa JOIN với posts -->
                    <td class="py-3 px-4">Món đồ ID: <?php echo htmlspecialchars($txn['item_id'] ?? ''); ?></td>
                    
                    <td class="py-3 px-4 fw-bold"><?php echo htmlspecialchars($txn['requester_name'] ?? 'Ẩn danh'); ?></td>
                    <td class="py-3 px-4">
                        <?php if (($txn['status'] ?? '') === 'completed') { ?>
                            <span class="badge bg-success px-3 py-2 rounded-pill">Hoàn thành</span>
                        <?php } else if (($txn['status'] ?? '') === 'pending') { ?>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Đang xử lý</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($txn['status'] ?? ''); ?></span>
                        <?php } ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else { 
                ?>
                <tr>
                    <td colspan="4" class="py-4 text-center text-muted">Không có giao dịch nào để hiển thị.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>