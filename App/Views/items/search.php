<div class="container py-5">

    <h2 class="mb-4">Tìm kiếm món đồ chia sẻ</h2>

    <form
        action="index.php"
        method="GET"
        class="row g-3 mb-4"
    >
        <input type="hidden" name="controller" value="Search">
        <input type="hidden" name="action" value="index">

        <div class="col-md-4">
            <input
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Nhập tên hoặc mô tả món đồ..."
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="0">Tất cả danh mục</option>

                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= (int)$category['id'] ?>"
                        <?= $selectedCategory == $category['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input
                type="text"
                name="location"
                class="form-control"
                placeholder="Địa điểm nhận đồ"
                value="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">
                Tìm kiếm
            </button>
        </div>
    </form>

    <h5 class="mb-3">
        Tìm thấy <?= count($items) ?> món đồ
    </h5>

    <div class="row g-4">

        <?php if (empty($items)): ?>

            <div class="col-12">
                <div class="alert alert-info">
                    Không tìm thấy món đồ phù hợp.
                </div>
            </div>

        <?php else: ?>

            <?php foreach ($items as $item): ?>

                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">

                        <img
                            src="<?= htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                            class="card-img-top"
                            alt="Ảnh món đồ"
                            style="height:220px; object-fit:cover;"
                        >

                        <div class="card-body">
                            <h5>
                                <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h5>

                            <p class="text-success">
                                <?= htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <?= htmlspecialchars($item['condition_note'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p class="text-muted">
                                <?= htmlspecialchars($item['pickup_location'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <a
                                href="index.php?controller=Item&action=detail&id=<?= (int)$item['id'] ?>"
                                class="btn btn-outline-success w-100"
                            >
                                Xem chi tiết
                            </a>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</div>