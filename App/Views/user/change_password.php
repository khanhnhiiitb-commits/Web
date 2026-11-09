
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h3 class="mb-4">Đổi mật khẩu</h3>

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
                          action="index.php?controller=User&amp;action=postChangePassword">

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password"
                                   name="old_password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password"
                                   name="new_password"
                                   class="form-control"
                                   minlength="8"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password"
                                   name="confirm_password"
                                   class="form-control"
                                   minlength="8"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-success">
                            Xác nhận đổi mật khẩu
                        </button>

                        <a href="index.php?controller=User&amp;action=profile"
                           class="btn btn-secondary">
                            Quay lại
                        </a>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
