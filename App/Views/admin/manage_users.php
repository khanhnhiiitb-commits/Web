<div class="container-fluid px-0">
    <h2 class="mb-4">Quản lý thành viên & Tài khoản</h2>

    <div class="table-responsive shadow-sm rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-4">ID</th>
                    <th class="py-3 px-4">Thành viên</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Trạng thái</th>
                    <th class="py-3 px-4 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($users) && is_array($users) && count($users) > 0) { 
                    foreach ($users as $user) { 
                ?>
                <tr>
                    <td class="py-3 px-4"><?php echo $user['id']; ?></td>
                    <td class="py-3 px-4 fw-bold"><?php echo htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật'); ?></td>
                    <td class="py-3 px-4 text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="py-3 px-4">
                        <?php if ((int)$user['status'] === 1) { ?>
                            <span class="badge bg-success px-3 py-2 rounded-pill">Hoạt động</span>
                        <?php } else { ?>
                            <span class="badge bg-danger px-3 py-2 rounded-pill">Bị khóa</span>
                        <?php } ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                       <!-- Form ẩn để gửi lệnh Khóa / Mở Khóa -->
                        <form action="?controller=Admin&action=updateUserStatus" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <?php if ((int)$user['status'] === 1) { ?>
                                <input type="hidden" name="status" value="0">
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">Khóa</button>
                            <?php } else { ?>
                                <input type="hidden" name="status" value="1">
                                <button type="submit" class="btn btn-outline-success btn-sm px-3">Mở khóa</button>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
                <?php 
                    } 
                } else { 
                ?>
                <tr>
                    <td colspan="5" class="py-4 text-center text-muted">Không có tài khoản nào.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>