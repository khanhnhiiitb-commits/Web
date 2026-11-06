
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4">Quản lý người dùng</h2>

    <a href="index.php?controller=admin&action=dashboard"
       class="btn btn-secondary mb-3">
        Quay lại Dashboard
    </a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <?php if ((int) $user['status'] === 1): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                <form method="POST"
                                      action="index.php?controller=admin&action=updateUserStatus">

                                    <input type="hidden" name="id"
                                           value="<?= (int) $user['id'] ?>">

                                    <?php if ((int) $user['status'] === 1): ?>
                                        <input type="hidden" name="status" value="0">
                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            Khóa
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit"
                                                class="btn btn-success btn-sm">
                                            Mở khóa
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Tài khoản hiện tại</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
