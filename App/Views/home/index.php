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
<div class="gt-home-page">
<!-- BANNER -->
<section class="hero text-center gt-hero">

    <div class="container gt-hero-inner">

        <div class="gt-eyebrow">🌱 Cộng đồng chia sẻ đồ dùng</div>
        <h1>Trao đi món đồ cũ, nhận lại niềm vui mới!</h1>

        <p class="mt-3">
            Give & Take - Nơi kết nối cộng đồng,
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
<section class="container gt-home-section mt-5" id="categories">

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
                <div class="col-6 col-lg-3">
                    <a class="text-decoration-none text-dark"
                       href="index.php?controller=Home&action=index&category_id=<?= e($category['id']) ?>">
                        <div class="category-card">
                            <?php
                                $categoryName = mb_strtolower((string)($category['name'] ?? ''), 'UTF-8');
                                $categoryIcon = 'bi-box-seam';
                                if (str_contains($categoryName, 'điện')) $categoryIcon = 'bi-phone';
                                elseif (str_contains($categoryName, 'nội thất')) $categoryIcon = 'bi-house-heart';
                                elseif (str_contains($categoryName, 'quần áo')) $categoryIcon = 'bi-bag-heart';
                                elseif (str_contains($categoryName, 'sách')) $categoryIcon = 'bi-book';
                            ?>
                            <span class="gt-category-icon"><i class="bi <?= $categoryIcon ?>"></i></span>
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
<section class="container gt-home-section mt-5" id="products">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
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
<section class="container gt-home-section mt-5">
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
</div>
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

