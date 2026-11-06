
<?php
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$username = $_SESSION['username'] ?? 'Thành viên';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng đồ chia sẻ - Sống Xanh Sharing</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f8f4;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #198754;
            padding: 15px 0;
        }

        .navbar-brand {
            color: white;
            font-size: 22px;
            font-weight: bold;
        }

        .form-container {
            max-width: 750px;
            margin: 45px auto;
        }

        .form-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px #00000012;
        }

        .form-header {
            background: #198754;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .form-body {
            padding: 30px;
        }

        .form-label {
            color: #198754;
            font-weight: bold;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 11px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem #19875420;
        }

        .btn-submit {
            background: #198754;
            color: white;
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
        }

        .btn-submit:hover {
            background: #146c43;
            color: white;
        }

        .preview-image {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            display: none;
            margin-top: 15px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg">
    <div class="container">

        <a class="navbar-brand text-decoration-none"
           href="index.php?controller=Home&action=index">

            <i class="bi bi-recycle"></i>
            Sống Xanh Sharing

        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="text-white">
                <i class="bi bi-person-circle"></i>
                <?= e($username) ?>
            </span>

            <a href="index.php?controller=Auth&action=logout"
               class="btn btn-light btn-sm text-success fw-bold">

                Đăng xuất

            </a>

        </div>

    </div>
</nav>


<!-- FORM ĐĂNG ĐỒ -->
<div class="container form-container">

    <div class="card form-card">

        <div class="form-header">

            <i class="bi bi-gift" style="font-size: 40px;"></i>

            <h3 class="fw-bold mt-2">
                Đăng món đồ chia sẻ
            </h3>

            <p class="mb-0">
                Trao đi món đồ cũ, lan tỏa yêu thương.
            </p>

        </div>


        <div class="form-body">

            <form action="index.php?controller=Item&action=store"
                  method="POST"
                  enctype="multipart/form-data">

                <!-- TIÊU ĐỀ -->
                <div class="mb-3">

                    <label class="form-label">
                        Tiêu đề món đồ *
                    </label>

                    <input type="text"
                           name="title"
                           class="form-control"
                           placeholder="Ví dụ: Tặng bàn học còn sử dụng tốt"
                           maxlength="255"
                           required>

                </div>


                <!-- DANH MỤC VÀ TÌNH TRẠNG -->
                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Danh mục *
                        </label>

                        <select name="category_id"
                                class="form-select"
                                required>

                            <option value="">
                                -- Chọn danh mục --
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option value="<?= (int)$category['id'] ?>">

                                    <?= e($category['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Tình trạng món đồ *
                        </label>

                        <input type="text"
                               name="condition_note"
                               class="form-control"
                               placeholder="Mới 90%, còn sử dụng tốt..."
                               required>

                    </div>

                </div>


                <!-- ĐỊA ĐIỂM -->
                <div class="mb-3">

                    <label class="form-label">
                        <i class="bi bi-geo-alt"></i>
                        Địa điểm giao nhận *
                    </label>

                    <input type="text"
                           name="pickup_location"
                           class="form-control"
                           placeholder="Ví dụ: Quận 1, TP. Hồ Chí Minh"
                           required>

                </div>


                <!-- HÌNH ẢNH -->
                <div class="mb-3">

                    <label class="form-label">
                        <i class="bi bi-image"></i>
                        Hình ảnh món đồ
                    </label>

                    <input type="file"
                           name="image"
                           id="image"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.webp"
                           required>

                    <small class="text-muted">
                        Chỉ chấp nhận JPG, JPEG, PNG, WEBP.
                        Dung lượng tối đa 2MB.
                    </small>

                    <img id="preview"
                         class="preview-image"
                         alt="Ảnh xem trước">

                </div>


                <!-- MÔ TẢ -->
                <div class="mb-4">

                    <label class="form-label">
                        Mô tả chi tiết *
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="5"
                              placeholder="Hãy mô tả chi tiết món đồ bạn muốn chia sẻ..."
                              required></textarea>

                </div>


                <!-- BUTTON -->
                <button type="submit"
                        class="btn btn-submit w-100">

                    <i class="bi bi-send"></i>

                    Đăng bài cùng Sống Xanh

                </button>


                <a href="index.php?controller=Home&action=index"
                   class="btn btn-outline-secondary w-100 mt-2">

                    Quay lại trang chủ

                </a>

            </form>

        </div>

    </div>

</div>


<script>
const imageInput = document.getElementById('image');
const preview = document.getElementById('preview');

imageInput.addEventListener('change', function() {

    const file = this.files[0];

    if (!file) {
        preview.style.display = 'none';
        return;
    }

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!allowedTypes.includes(file.type) ||
        file.size > 2 * 1024 * 1024) {

        alert('Vui lòng chọn ảnh JPG, PNG hoặc WEBP dưới 2MB.');

        this.value = '';
        preview.style.display = 'none';

        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
</script>

</body>
</html>
