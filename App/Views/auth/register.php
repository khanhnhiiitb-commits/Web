<?php
$error = $error ?? '';
?>

<main>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h2 class="text-success fw-bold">Đăng ký</h2>
                            <p class="text-muted">
                                Tham gia cộng đồng chia sẻ đồ tái sử dụng
                            </p>
                        </div>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <form action="index.php?controller=auth&amp;action=register"
                              method="POST">

                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    Họ và tên
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="full_name"
                                    name="full_name"
                                    placeholder="Nhập họ và tên"
                                    autocomplete="name"
                                    maxlength="100"
                                    required
                                >
                            </div>

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
                                    maxlength="150"
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
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    Xác nhận mật khẩu
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Nhập lại mật khẩu"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    Số điện thoại
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    placeholder="Nhập số điện thoại"
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    Địa chỉ
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    placeholder="Nhập địa chỉ của bạn"
                                    autocomplete="street-address"
                                >
                            </div>

                            <div class="d-grid mt-4">
                                <button
                                    type="submit"
                                    name="register"
                                    value="1"
                                    class="btn btn-success"
                                >
                                    Tạo tài khoản
                                </button>
                            </div>

                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">
                                Bạn đã có tài khoản?
                            </span>

                            <a href="index.php?controller=auth&amp;action=login"
                               class="text-success text-decoration-none fw-semibold">
                                Đăng nhập
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
