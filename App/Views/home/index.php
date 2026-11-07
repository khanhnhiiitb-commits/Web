<?php
// Hàm bảo vệ dữ liệu khi hiển thị
if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Chuẩn hóa đường dẫn ảnh theo image_url lưu trong CSDL.
if (!function_exists('itemImageUrl')) {
    function itemImageUrl($image): string {
        $image = trim((string)($image ?? ''));
        if ($image === '') {
            return '';
        }
        // Cho phép URL ảnh tuyệt đối nếu hệ thống có dùng CDN.
        if (preg_match('~^https?://~i', $image)) {
            return $image;
        }
        $image = ltrim(str_replace('\\', '/', $image), '/');
        // Tương thích cả dữ liệu cũ chỉ lưu tên file và dữ liệu mới lưu public/uploads/items/....
        if (strpos($image, 'public/') === 0) {
            return $image;
        }
        if (strpos($image, 'uploads/items/') === 0) {
            return 'public/' . $image;
        }
        return 'public/uploads/items/' . basename($image);
    }
}

$isLoggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'Tài khoản';
$categories = is_array($categories ?? null) ? $categories : [];
$items = is_array($items ?? null) ? $items : [];
$keyword = trim((string)($keyword ?? ''));
$categoryId = (int)($categoryId ?? 0);
$location = trim((string)($location ?? ''));
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

<!-- BANNER -->
<section class="hero text-center">

    <div class="container">

        <h1>Trao đi món đồ cũ, nhận lại niềm vui mới!</h1>

        <p class="mt-3">
            Sống Xanh Sharing - Nơi kết nối cộng đồng,
            chia sẻ đồ dùng và lan tỏa lối sống xanh.
        </p>

  <!-- FORM TÌM KIẾM: chỉ giữ một form duy nhất -->
        <form action="index.php"
              method="GET"
              class="search-box"
              id="searchForm">

            <input type="hidden" name="controller" value="Home">
            <input type="hidden" name="action" value="index">

            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text"
                           name="keyword"
                           id="keyword"
                           class="form-control form-control-lg"
                           placeholder="Bạn đang tìm món đồ gì?"
                           value="<?= e($keyword) ?>">
                </div>

                <div class="col-md-3">
                    <select name="category_id"
                            id="category_id"
                            class="form-select form-select-lg">
                        <option value="">Tất cả danh mục</option>

                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['id']) ?>"
                                <?= $categoryId === (int)$category['id']
                                    ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="text"
                           name="location"
                           id="location"
                           class="form-control form-control-lg"
                           placeholder="Địa điểm"
                           value="<?= e($location) ?>">
                </div>

                <div class="col-md-2">
                    <button type="submit"
                            id="searchButton"
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
            <h3 class="section-title" id="productHeading">
    <i class="bi bi-gift"></i>
    <?= !empty($keyword) || !empty($categoryId) || !empty($location)
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
 <div
    class="row g-4"
    id="productList"
    aria-live="polite"
>
    <?php require __DIR__ . '/_item_list.php'; ?>
</div>
<div id="pagination">
    <?php require __DIR__ . '/_pagination.php'; ?>
</div>

<div id="searchStatus"
     class="text-center text-muted mt-3"
     style="display: none;">
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
                ? 'index.php?controller=Item&action=create'
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
    <script>
document.addEventListener('DOMContentLoaded', function () {

    const searchForm = document.getElementById('searchForm');
    const productList = document.getElementById('productList');
    const pagination = document.getElementById('pagination');
    const searchStatus = document.getElementById('searchStatus');
    const searchButton = document.getElementById('searchButton');
    const productHeading = document.getElementById('productHeading');

    const keywordInput = document.getElementById('keyword');
    const categoryInput = document.getElementById('category_id');
    const locationInput = document.getElementById('location');

    if (!searchForm || !productList || !pagination ||
        !searchStatus || !searchButton || !productHeading ||
        !keywordInput || !categoryInput || !locationInput) {
        console.error('Không tìm thấy các thành phần phân trang AJAX.');
        return;
    }

    let debounceTimer = null;
    let currentRequest = null;
    let requestNumber = 0;

    async function searchProducts(page = 1) {

        const keyword = keywordInput.value.trim();
        const categoryId = categoryInput.value;
        const location = locationInput.value.trim();

        const params = new URLSearchParams({
            keyword: keyword,
            category_id: categoryId,
            location: location,
            page: page
        });

        if (currentRequest) {
            currentRequest.abort();
        }

        const controller = new AbortController();
        currentRequest = controller;

        const thisRequest = ++requestNumber;

        searchStatus.style.display = 'block';
        searchStatus.textContent = 'Đang tải sản phẩm...';

        searchButton.disabled = true;

        try {

            const response = await fetch(
                'index.php?controller=Home&action=searchAjax&' +
                params.toString(),
                {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: controller.signal
                }
            );

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Không thể tải sản phẩm.');
            }

            if (thisRequest !== requestNumber) return;

            // Cập nhật danh sách sản phẩm.
            productList.innerHTML = data.html;

            // Cập nhật thanh phân trang.
            pagination.innerHTML = data.pagination;

            productHeading.innerHTML =
                '<i class="bi bi-gift"></i> ' +
                (keyword || categoryId || location
                    ? 'Kết quả tìm kiếm'
                    : 'Đồ dùng mới được chia sẻ');

            searchStatus.textContent =
                'Tìm thấy ' + data.count + ' sản phẩm.';

            // Cập nhật URL hiện tại.
            const pageParams = new URLSearchParams({
                controller: 'Home',
                action: 'index'
            });

            if (keyword) pageParams.set('keyword', keyword);
            if (categoryId) pageParams.set('category_id', categoryId);
            if (location) pageParams.set('location', location);

            if (data.totalPages > 0) {
                pageParams.set('page', data.currentPage);
            }

            history.replaceState(
                {},
                '',
                'index.php?' + pageParams.toString()
            );

        } catch (error) {

            if (error.name === 'AbortError') return;

            console.error('Pagination AJAX error:', error);

            if (thisRequest === requestNumber) {
                searchStatus.textContent =
                    'Lỗi tải sản phẩm. Vui lòng thử lại.';
            }

        } finally {

            if (thisRequest === requestNumber) {
                searchButton.disabled = false;
                currentRequest = null;
            }
        }
    }

    // Submit tìm kiếm: luôn bắt đầu từ trang 1.
    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        clearTimeout(debounceTimer);
        searchProducts(1);
    });

    // Tự động tìm từ khóa.
    keywordInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            searchProducts(1);
        }, 400);
    });

    // Lọc danh mục.
    categoryInput.addEventListener('change', function () {
        clearTimeout(debounceTimer);
        searchProducts(1);
    });

    // Lọc địa điểm.
    locationInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            searchProducts(1);
        }, 400);
    });

    // Bắt sự kiện phân trang bằng event delegation.
    pagination.addEventListener('click', function (event) {

        const button = event.target.closest('button[data-page]');

        if (!button || button.disabled) return;

        const page = parseInt(button.dataset.page, 10);

        if (!Number.isInteger(page) || page < 1) return;

        searchProducts(page);

        // Cuộn về đầu danh sách sản phẩm.
        document.getElementById('products').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

});
</script>
</body>
</html>
