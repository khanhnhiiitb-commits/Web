
<?php /** @var array $data */ ?>
<div class="container mt-4">
    <h2 class="mb-4 text-success border-bottom pb-2">Góc Sống Xanh</h2>
<!-- Các dòng dưới giữ nguyên --><div class="container mt-4">
    <!-- Bộ lọc tìm kiếm -->
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm tên món đồ...">
        </div>
        <div class="col-md-4">
            <select id="categorySelect" class="form-select">
                <option value="">Tất cả danh mục</option>
                <?php foreach($data['categories'] as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <!-- Lưới hiển thị sản phẩm -->
    <div class="row" id="itemList">
        <?php foreach($data['items'] as $item): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <img src="<?= $item['image_url'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                    <p class="card-text text-success"><small><?= $item['category_name'] ?></small></p>
                    <p class="card-text"><?= substr(htmlspecialchars($item['description']), 0, 60) ?>...</p>
                    <a href="/item/detail/<?= $item['id'] ?>" class="btn btn-outline-success mt-auto">Xem chi tiết</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>