<?php
$isLoggedIn = !empty($_SESSION['user_id']);

$username = $_SESSION['username']
    ?? $_SESSION['full_name']
    ?? 'Tài khoản';
?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-recycle me-1"></i>
            Sống Xanh Sharing
        </a>

        <!-- Menu mobile -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Mở menu">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <!-- Menu bên trái -->
            <ul class="navbar-nav me-auto ms-lg-4">

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
                    <a class="nav-link" href="index.php?controller=Item&action=search">
                        Đồ chia sẻ
                    </a>
                </li>

                <!-- NÚT quan tri -->
               <li class="nav-item">
    <a class="nav-link" href="index.php?controller=Admin&action=dashboard">
        <i class="bi bi-speedometer2"></i> Dashboard Admin
    </a>
</li>

                <?php if ($isLoggedIn): ?>

                    <li class="nav-item">
                        <a class="nav-link text-warning fw-semibold"
                           href="index.php?controller=Request&action=myRequests">

                            <i class="bi bi-bag-check-fill me-1"></i>
                            Đồ đã đăng ký nhận

                        </a>
                    </li>
                    
                <?php endif; ?>

            </ul>

            <!-- Menu bên phải -->
            <div class="d-flex align-items-center gap-2 flex-wrap">

                <?php if ($isLoggedIn): ?>

                    <!-- Đăng đồ tặng -->
                    <a href="index.php?controller=Item&action=create"
                       class="btn btn-warning btn-sm fw-bold">

                        <i class="bi bi-gift-fill me-1"></i>
                        Đăng đồ tặng

                    </a>

                    <!-- Dropdown tài khoản -->
                    <div class="dropdown">

                        <button class="btn btn-outline-light btn-sm dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">

                            <i class="bi bi-person-circle me-1"></i>

                            <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow">

                            <li>
                                <a class="dropdown-item"
                                   href="index.php?controller=User&action=profile">

                                    <i class="bi bi-person me-2"></i>
                                    Tài khoản của tôi

                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item"
                                   href="index.php?controller=Item&action=my_items">

                                    <i class="bi bi-box-seam me-2"></i>
                                    Kho đồ của tôi

                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item"
                                   href="index.php?controller=Request&action=myRequests">

                                    <i class="bi bi-bag-check me-2"></i>
                                    Đồ đã đăng ký nhận

                                </a>
                            </li>
                            <!-- ← THÊM MỚI: bắt đầu -->
                            <li>
                                <a class="dropdown-item"
                                    href="index.php?controller=Request&action=manageMyItems">

                                    <i class="bi bi-inbox me-2"></i>
                                     Yêu cầu nhận đồ của tôi

                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                href="index.php?controller=Request&action=history">
                                <i class="bi bi-clock-history me-2"></i>
                                 Lịch sử giao dịch
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger"
                                   href="index.php?controller=Auth&action=logout">

                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Đăng xuất

                                </a>
                            </li>

                        </ul>

                    </div>

                <?php else: ?>

                    <!-- Chưa đăng nhập -->
                    <a href="index.php?controller=Auth&action=login"
                       class="btn btn-outline-light btn-sm">

                        Đăng nhập

                    </a>

                    <a href="index.php?controller=Auth&action=register"
                       class="btn btn-light text-success fw-bold btn-sm">

                        Đăng ký

                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>