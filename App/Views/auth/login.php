
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - Give & Take</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Đăng nhập</h2>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST"
                          action="index.php?controller=auth&action=login">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email"
                                   class="form-control" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password"
                                   class="form-control" required>
                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">
                            Đăng nhập
                        </button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Chưa có tài khoản?
                        <a href="index.php?controller=auth&action=register">
                            Đăng ký
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
