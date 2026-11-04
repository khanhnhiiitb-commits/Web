<?php
$user = $user ?? [];
$error = $error ?? '';
$success = $success ?? '';

$fullName = $user['full_name'] ?? 'Người dùng';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';
?>

<main>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-success">Thông tin cá nhân</h2>
                <p class="text-muted">
                    Quản lý thông tin liên hệ và địa chỉ nhận đồ của bạn.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Cột trái: Avatar và thông tin người dùng -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div
                            class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 110px; height: 110px; font-size: 42px;"
                            aria-label="Avatar người dùng"
                        >
                            
                        </div>

                        <h4 class="fw-bold mb-2">
                            <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>
                        </h4>

                        <p class="text-muted mb-3">
                            <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <span class="badge bg-success-subtle text-success">
                            Thành viên Give &amp; Take
                        </span>

                        <hr>

                        <p class="text-muted small mb-0">
                            Hãy cập nhật thông tin liên hệ để thuận tiện
                            khi trao đổi và nhận đồ.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Form cập nhật thông tin -->
            <div class="col-12 col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Cập nhật thông tin</h4>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <?php if (!empty($success)) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <form
                            action="index.php?controller=user&amp;action=profile"
                            method="POST"
                        >
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold">
                                    Số điện thoại
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Nhập số điện thoại"
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="mb-4">
                                <label for="address" class="form-label fw-semibold">
                                    Địa chỉ nhận đồ
                                </label>

                                <textarea
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    rows="4"
                                    placeholder="Nhập địa chỉ nhận đồ"
                                    autocomplete="street-address"
                                ><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success px-4">
                                    Lưu thay đổi
                                </button>

                                <a
                                    href="index.php"
                                    class="btn btn-outline-secondary px-4"
                                >
                                    Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>