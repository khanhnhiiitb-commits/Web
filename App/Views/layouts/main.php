<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Give & Take - Chia sẻ đồ tái sử dụng') ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f5f8f5;
        }

        .main-content {
            min-height: 75vh;
            padding-bottom: 40px;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-recycle me-1"></i>
            Sống Xanh Sharing
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Trang chủ
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php#categories">
                        Danh mục
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php#products">
                        Đồ chia sẻ
                    </a>
                </li>

                <?php if ($isLoggedIn): ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="index.php?controller=Item&action=create">
                            <i class="bi bi-plus-circle me-1"></i>
                            Đăng đồ tặng
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">

                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>

                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item"
                                   href="index.php?controller=User&action=profile">
                                    <i class="bi bi-person me-2"></i>
                                    Tài khoản của tôi
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger"
                                   href="index.php?controller=Auth&action=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Đăng xuất
                                </a>
                            </li>

                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="index.php?controller=Auth&action=login">
                            Đăng nhập
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-light text-success fw-bold ms-lg-2 px-3"
                           href="index.php?controller=Auth&action=register">
                            Đăng ký
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- Nội dung từng trang -->
<main class="container main-content">
    <?= $content ?>
</main>

<!-- Footer -->
<footer class="bg-success text-white text-center py-3 mt-4">
    <div class="container">
        <small>
            © <?= date('Y') ?> Give & Take - Lan tỏa giá trị tái sử dụng.
        </small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
