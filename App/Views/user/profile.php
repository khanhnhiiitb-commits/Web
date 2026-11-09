<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h3 class="mb-4">Hồ sơ cá nhân</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST"
                          action="index.php?controller=User&amp;action=updateProfile">

                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text"
                                   name="full_name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel"
                                   name="phone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address"
                                      class="form-control"
                                      rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            Lưu thay đổi
                        </button>

                        <a href="index.php?controller=User&amp;action=changePassword"
                           class="btn btn-outline-primary">
                            Đổi mật khẩu
                        </a>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
