<div class="container-fluid px-0">
    <h2 class="mb-4">Kiểm duyệt & Quản lý bài đăng</h2>

    <div class="table-responsive shadow-sm rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-4">Món đồ</th>
                    <th class="py-3 px-4">Người đăng</th>
                    <th class="py-3 px-4">Danh mục</th>
                    <th class="py-3 px-4">Trạng thái</th>
                    <th class="py-3 px-4 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($products) && is_array($products) && count($products) > 0) { 
                    foreach ($products as $item) { 
                ?>
                <tr>
                    <td class="py-3 px-4 fw-bold"><?php echo htmlspecialchars($item['title'] ?? 'Chưa có tên'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['user'] ?? 'Ẩn danh'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['category'] ?? 'Khác'); ?></td>
                    <td class="py-3 px-4">
                        <?php if (($item['status'] ?? '') === 'pending') { ?>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Chờ duyệt</span>
                        <?php } else { ?>
                            <span class="badge bg-success px-3 py-2 rounded-pill">Đã duyệt</span>
                        <?php } ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <?php if (($item['status'] ?? '') === 'pending') { ?>
                                <!-- Nút Duyệt bài -->
                              <!-- Nút Duyệt bài -->
                                <form action="<?= BASE_URL ?>index.php?controller=Admin&action=approveProduct" method="POST" class="m-0">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm px-3">Duyệt</button>
                                </form>
                            <?php } ?>
                            
                            <!-- Nút Xóa bài -->
                         <!-- Nút Xóa bài -->
                            <form action="<?= BASE_URL ?>index.php?controller=Admin&action=deleteProduct" method="POST" class="m-0">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm px-3" onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php 
                    } 
                } else { 
                ?>
                <tr><td colspan="5" class="py-4 text-center text-muted">Không có bài đăng nào để hiển thị.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>