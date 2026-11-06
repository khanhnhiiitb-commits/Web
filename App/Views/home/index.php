
<?php
// Hàm bảo vệ dữ liệu khi hiển thị
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$isLoggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Tài khoản';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sống Xanh Sharing - Chia sẻ để yêu thương</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: #f7faf7;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #198754;
            padding: 15px 0;
        }

        .navbar-brand {
            font-size: 23px;
            font-weight: bold;
            color: white !important;
        }

        .navbar .nav-link {
            color: white !important;
            margin: 0 8px;
        }

        .hero {
            background: linear-gradient(120deg, #198754, #56ab72);
            color: white;
            padding: 75px 20px;
            border-radius: 0 0 35px 35px;
        }

        .hero h1 {
            font-size: 42px;
            font-weight: bold;
        }

        .hero p {
            font-size: 17px;
        }

        .search-box {
            background: white;
            padding: 12px;
            border-radius: 12px;
            max-width: 750px;
            margin: 30px auto 0;
        }

        .category-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e5eee5;
            height: 100%;
            transition: .2s;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 18px #00000012;
        }

        .category-card i {
            font-size: 30px;
            color: #198754;
        }

        .product-card {
            border: none;
            border-radius: 13px;
            overflow: hidden;
            transition: .2s;
            box-shadow: 0 3px 12px #0000000d;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px #00000018;
        }

        .product-image {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #edf5ed;
        }

        .section-title {
            color: #198754;
            font-weight: bold;
        }

        .btn-green {
            background: #198754;
            color: white;
        }

        .btn-green:hover {
            background: #146c43;
            color: white;
        }

        footer {
            background: #145c38;
            color: white;
            margin-top: 70px;
            padding: 40px 0 20px;
        }

        footer a {
            color: white;
            text-decoration: none;
        }

        .empty-box {
            background: white;
            border-radius: 12px;
            padding: 50px 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <i class="bi bi-recycle"></i>
            Sống Xanh Sharing
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu">
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
                    <a class="nav-link" href="#categories">
                        Danh mục
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#products">
                        Đồ chia sẻ
                    </a>
                </li>

                <?php if ($isLoggedIn): ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="index.php?controller=Item&action=postItem">
                            <i class="bi bi-plus-circle"></i>
                            Đăng đồ tặng
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown">

                            <i class="bi bi-person-circle"></i>
                            <?= e($username) ?>

                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item"
                                   href="index.php?controller=User&action=profile">
                                    Tài khoản của tôi
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger"
                                   href="index.php?controller=Auth&action=logout">
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
                        <a class="btn btn-light text-success fw-bold ms-lg-2"
                           href="index.php?controller=Auth&action=register">
                            Đăng ký
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>


<!-- BANNER -->
<section class="hero text-center">

    <div class="container">

        <h1>Trao đi món đồ cũ, nhận lại niềm vui mới!</h1>

        <p class="mt-3">
            Sống Xanh Sharing - Nơi kết nối cộng đồng,
            chia sẻ đồ dùng và lan tỏa lối sống xanh.
        </p>

        <form action="index.php" method="GET" class="search-box">

            <input type="hidden" name="controller" value="Home">
            <input type="hidden" name="action" value="index">

            <div class="row g-2">

                <div class="col-md-6">
                    <input type="text"
                           name="keyword"
                           class="form-control form-control-lg"
                           placeholder="Bạn đang tìm món đồ gì?"
                           value="<?= e($keyword ?? '') ?>">
                </div>

                <div class="col-md-4">

                    <select name="category_id"
                            class="form-select form-select-lg">

                        <option value="">Tất cả danh mục</option>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?= e($category['id']) ?>"
                                <?= (string)($categoryId ?? '') ===
                                    (string)$category['id']
                                    ? 'selected' : '' ?>>

                                <?= e($category['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-2">
                    <button type="submit"
                            class="btn btn-green btn-lg w-100">

                        <i class="bi bi-search"></i>
                        Tìm

                    </button>
                </div>

            </div>

        </form>

    </div>
</section>


<!-- DANH MỤC -->
<section class="container mt-5" id="categories">

    <div class="mb-4">
        <h3 class="section-title">
            <i class="bi bi-grid"></i>
            Danh mục đồ dùng
        </h3>

        <p class="text-muted">
            Khám phá những món đồ đang được cộng đồng chia sẻ.
        </p>
    </div>

    <div class="row g-3">

        <?php if (!empty($categories)): ?>

            <?php foreach ($categories as $category): ?>

                <div class="col-6 col-md-3 col-lg-2">

                    <a class="text-decoration-none text-dark"
                       href="index.php?controller=Home&action=index&category_id=<?= e($category['id']) ?>">

                        <div class="category-card">

                            <i class="bi bi-box-seam"></i>

                            <h6 class="mt-3 mb-0">
                                <?= e($category['name']) ?>
                            </h6>

                        </div>

                    </a>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="text-muted">Chưa có danh mục nào.</p>

        <?php endif; ?>

    </div>

</section>


<!-- DANH SÁCH SẢN PHẨM -->
<section class="container mt-5" id="products">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="section-title">
                <i class="bi bi-gift"></i>
                <?= !empty($keyword) || !empty($categoryId)
                    ? 'Kết quả tìm kiếm'
                    : 'Đồ dùng mới được chia sẻ' ?>
            </h3>

            <p class="text-muted mb-0">
                Những món đồ đang chờ chủ nhân mới.
            </p>
        </div>

        <?php if ($isLoggedIn): ?>

            <a href="index.php?controller=Item&action=create"
               class="btn btn-green">

                <i class="bi bi-plus-lg"></i>
                Đăng đồ tặng

            </a>

        <?php else: ?>

            <a href="index.php?controller=Auth&action=login"
               class="btn btn-green">

                <i class="bi bi-plus-lg"></i>
                Đăng đồ tặng

            </a>

        <?php endif; ?>

    </div>


    <div class="row g-4">

        <?php if (!empty($items)): ?>

            <?php foreach ($items as $item): ?>

                <div class="col-6 col-md-4 col-lg-3">

                    <div class="card product-card h-100">

                        <?php
                        $image = trim($item['image_url'] ?? '');
                        $imagePath = $image !== ''
                            ? 'uploads/' . $image
                            : '';
                        ?>

                        <img
                            src="<?= e($imagePath) ?>"
                            class="product-image"
                            alt="<?= e($item['title']) ?>"
                            onerror="this.onerror=null;this.src='https://placehold.co/400x300/eaf5ea/198754?text=Song+Xanh';"
                        >

                        <div class="card-body d-flex flex-column">

                            <span class="badge bg-success mb-2 align-self-start">

                                <?= e($item['category_name']) ?>

                            </span>

                            <h5 class="card-title fw-bold">

                                <?= e($item['title']) ?>

                            </h5>

                            <p class="text-muted small">

                                <i class="bi bi-geo-alt"></i>

                                <?= e($item['pickup_location']) ?>

                            </p>

                            <p class="text-muted small">

                                <i class="bi bi-heart"></i>
                                Món đồ đang chờ người nhận.

                            </p>

                            <a href="index.php?controller=Item&action=detail&id=<?= (int)$item['id'] ?>"
                               class="btn btn-outline-success mt-auto">

                                Xem chi tiết

                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="empty-box">

                    <i class="bi bi-search text-success"
                       style="font-size: 50px;"></i>

                    <h5 class="mt-3">
                        Không tìm thấy món đồ phù hợp
                    </h5>

                    <p class="text-muted">
                        Bạn hãy thử từ khóa hoặc danh mục khác nhé.
                    </p>

                    <a href="index.php"
                       class="btn btn-green">
                        Quay lại trang chủ
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- GIỚI THIỆU -->
<section class="container mt-5">

    <div class="row align-items-center bg-white p-4 rounded-4 shadow-sm">

        <div class="col-md-7">

            <h3 class="section-title">
                Vì một cộng đồng sống xanh hơn
            </h3>

            <p class="text-muted mt-3">
                Một món đồ bạn không còn sử dụng có thể trở thành
                món quà ý nghĩa đối với người khác.
                Hãy cùng Sống Xanh Sharing chia sẻ, tái sử dụng
                và góp phần giảm thiểu rác thải.
            </p>

            <a href="<?= $isLoggedIn
                ? 'index.php?controller=Item&action=postItem'
                : 'index.php?controller=Auth&action=register' ?>"
               class="btn btn-green">

                Tham gia ngay
                <i class="bi bi-arrow-right"></i>

            </a>

        </div>

        <div class="col-md-5 text-center mt-4 mt-md-0">

            <i class="bi bi-recycle text-success"
               style="font-size: 120px;"></i>

        </div>

    </div>

</section>


<!-- FOOTER -->
<footer>

    <div class="container">

        <div class="row">

            <div class="col-md-5 mb-4">

                <h4 class="fw-bold">
                    <i class="bi bi-recycle"></i>
                    Sống Xanh Sharing
                </h4>

                <p class="mt-3">
                    Chia sẻ yêu thương, kết nối cộng đồng,
                    lan tỏa lối sống xanh.
                </p>

            </div>

            <div class="col-md-3 mb-4">

                <h5>Liên kết</h5>

                <p><a href="index.php">Trang chủ</a></p>
                <p><a href="#categories">Danh mục</a></p>
                <p><a href="#products">Đồ chia sẻ</a></p>

            </div>

            <div class="col-md-4 mb-4">

                <h5>Tài khoản</h5>

                <p>
                    <a href="index.php?controller=Auth&action=login">
                        Đăng nhập
                    </a>
                </p>

                <p>
                    <a href="index.php?controller=Auth&action=register">
                        Đăng ký
                    </a>
                </p>

            </div>

        </div>

        <hr>

        <p class="text-center mb-0">
            © <?= date('Y') ?> Sống Xanh Sharing.
            All Rights Reserved.
        </p>

    </div>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
