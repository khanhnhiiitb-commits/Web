<?php
if (!isset($users)) {
    $users = [];
}
?>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Quản lý người dùng</h3>

        <a href="index.php?controller=Admin&amp;action=dashboard"
           class="btn btn-outline-secondary">
            Về Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= (int) $user['id'] ?></td>

                            <td>
                                <?= htmlspecialchars($user['full_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['email']) ?>
                            </td>

                            <td>
                                <?= $user['role'] === 'admin'
                                    ? 'Admin'
                                    : 'Member' ?>
                            </td>

                            <td>
                                <?php if ((int) $user['status'] === 1): ?>
                                    <span class="badge bg-success">
                                        Hoạt động
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        Đã khóa
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>

                                    <form method="POST"
                                          action="index.php?controller=Admin&amp;action=updateUserStatus">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int) $user['id'] ?>">

                                        <?php if ((int) $user['status'] === 1): ?>
                                            <input type="hidden"
                                                   name="status"
                                                   value="0">

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">
                                                Khóa
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden"
                                                   name="status"
                                                   value="1">

                                            <button type="submit"
                                                    class="btn btn-success btn-sm">
                                                Mở khóa
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                <?php else: ?>
                                    <span class="text-muted">
                                        Tài khoản hiện tại
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>
