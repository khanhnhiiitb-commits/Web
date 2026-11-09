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
                <?php if (!empty($products)) {
                    foreach ($products as $post) { 
                        $status = trim($post['status'] ?? 'pending');
                ?>
                    <tr>
                        <td class="py-3 px-4 fw-bold"><?php echo htmlspecialchars($post['title'] ?? 'Không có tiêu đề'); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($post['user_name'] ?? 'Ẩn danh'); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($post['category'] ?? 'Chưa phân loại'); ?></td>
                        <td class="py-3 px-4">
                            <?php if ($status === 'available') { ?>
                                <span class="badge bg-success px-3 py-2 rounded-pill">Đã duyệt</span>
                            <?php } else { ?>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Chờ duyệt</span>
                            <?php } ?>
                        </td>
                       <td class="py-3 px-4 text-center">
    <div class="d-flex justify-content-center align-items-center gap-2">
        <?php if ($status !== 'available') { ?>
            <!-- Nút Duyệt -->
            <form action="?controller=Admin&action=approveProduct" method="POST" class="m-0">
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-success" style="width: 75px;">Duyệt</button>
            </form>
        <?php } else { ?>
            <!-- Nút Đã xong (Cùng kích thước width: 75px để thẳng hàng tuyệt đối) -->
            <button type="button" class="btn btn-sm btn-light text-muted border" style="width: 75px;" disabled>Đã xong</button>
        <?php } ?>

        <!-- Nút Xóa -->
        <form action="?controller=Admin&action=deleteProduct" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger" style="width: 75px;">Xóa</button>
        </form>
    </div>
</td>
                    </tr>
                <?php }
                } else { ?>
                    <tr>
                        <td colspan="5" class="py-4 text-center text-muted">Không có bài đăng nào để hiển thị.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>