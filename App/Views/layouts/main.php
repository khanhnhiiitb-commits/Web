<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($title ?? 'Sống Xanh Sharing - Chia sẻ đồ tái sử dụng', ENT_QUOTES, 'UTF-8') ?>
    </title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS riêng -->
    <link rel="stylesheet" href="public/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f5f8f5;
        }

        .main-content {
            min-height: 75vh;
            padding-bottom: 40px;
        }

        .navbar {
            background-color: #198754;
            padding: 12px 0;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
        }

        .navbar .nav-link {
            color: white;
            margin: 0 5px;
        }

        .navbar .nav-link:hover {
            color: #ffc107;
        }
    </style>
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

    <!-- HEADER CHUNG -->
    <?php require __DIR__ . '/header.php'; ?>

    <!-- NỘI DUNG TỪNG TRANG -->
    <main class="<?= ($view ?? '') === 'home/index' ? 'main-content' : 'container main-content' ?>">
        <?= $content ?? '' ?>
    </main>

    <!-- FOOTER CHUNG -->
    <?php require __DIR__ . '/footer.php'; ?>

</body>
</html>