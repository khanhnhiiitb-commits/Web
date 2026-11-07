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
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            🌱 Give & Take
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Trang chủ</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="index.php?controller=home&action=blog">
                        Góc sống xanh
                    </a>
                </li>
            </ul>

            <div class="d-flex gap-2">
                <?php if (!empty($_SESSION['user_id'])): ?>

                    <a href="index.php?controller=item&action=create"
                       class="btn btn-warning btn-sm fw-bold">
                        <i class="bi bi-gift me-1"></i> Đăng tặng đồ
                    </a>

                    <span class="navbar-text text-white">
                        Xin chào, <?= htmlspecialchars($_SESSION['username'] ?? 'bạn') ?>
                    </span>

                    <a href="index.php?controller=auth&action=logout"
                       class="btn btn-outline-light btn-sm">
                        Đăng xuất
                    </a>

                <?php else: ?>

                    <a href="index.php?controller=auth&action=login"
                       class="btn btn-outline-light btn-sm">
                        Đăng nhập
                    </a>

                    <a href="index.php?controller=auth&action=register"
                       class="btn btn-warning btn-sm fw-bold">
                        Đăng ký
                    </a>

                <?php endif; ?>
            </div>

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
