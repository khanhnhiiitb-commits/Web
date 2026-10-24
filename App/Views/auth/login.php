 <?php
$error = $error ?? '';
?>

<main>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h2 class="text-success fw-bold">Đăng nhập</h2>
                            <p class="text-muted">
                                Chào mừng bạn quay lại Give &amp; Take
                            </p>
                        </div>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <form action="index.php?controller=auth&amp;action=login"
                              method="POST">

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="Nhập email của bạn"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Mật khẩu
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Nhập mật khẩu"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <div class="d-grid mt-4">
                                <button
                                    type="submit"
                                    name="login"
                                    value="1"
                                    class="btn btn-success"
                                >
                                    Đăng nhập
                                </button>
                            </div>

                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">
                                Bạn chưa có tài khoản?
                            </span>

                            <a href="index.php?controller=auth&amp;action=register"
                               class="text-success text-decoration-none fw-semibold">
                                Đăng ký ngay
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>