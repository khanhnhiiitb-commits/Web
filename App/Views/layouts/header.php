<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Give & Take - Chia sẻ đồ tái sử dụng' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🌱 Give & Take</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?controller=home&action=blog">Góc sống xanh</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="index.php?controller=item&action=create" class="btn btn-warning btn-sm fw-bold">🎁 Đăng tặng đồ</a>
                <a href="index.php?controller=auth&action=login" class="btn btn-outline-light btn-sm">Đăng nhập</a>
            </div>
        </div>
    </div>
</nav>

<div class="container min-vh-100">