
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký - Give & Take</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Đăng ký tài khoản</h2>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST"
                          action="index.php?controller=auth&action=register">

                        <div class="mb-3">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="full_name"
                                   class="form-control" required
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email"
                                   class="form-control" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu *</label>
                            <input type="password" name="password"
                                   class="form-control" required
                                   minlength="8">
                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">
                            Đăng ký
                        </button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Đã có tài khoản?
                        <a href="index.php?controller=auth&action=login">
                            Đăng nhập
                        </a>
                    </p>

                    <p class="text-center mt-2 mb-0">
                        <a href="index.php">Về trang chủ</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
