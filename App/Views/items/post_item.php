
<?php
function itemEscape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .post-item-page {
        background: #f5f8f5;
        min-height: 100vh;
        padding: 45px 16px 60px;
    }

    .post-item-wrapper {
        max-width: 1050px;
        margin: 0 auto;
    }

    .post-item-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .post-item-header h2 {
        color: #246b45;
        font-weight: 700;
        font-size: 30px;
        margin-bottom: 8px;
    }

    .post-item-header p {
        color: #6c757d;
        font-size: 15px;
        margin: 0;
    }

    .post-item-card {
        background: #fff;
        border: 1px solid #e5ece6;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 5px 20px rgba(0,0,0,.05);
    }

    .form-section-title {
        color: #246b45;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #edf1ed;
    }

    .post-item-card .form-label {
        font-weight: 600;
        font-size: 14px;
        color: #343a40;
        margin-bottom: 7px;
    }

    .post-item-card .form-control,
    .post-item-card .form-select {
        min-height: 44px;
        border-radius: 8px;
        font-size: 14px;
        border-color: #dce4dd;
    }

    .post-item-card textarea.form-control {
        min-height: 125px;
        resize: vertical;
    }

    .post-item-card .form-control:focus,
    .post-item-card .form-select:focus {
        border-color: #409568;
        box-shadow: 0 0 0 .2rem rgba(64,149,104,.13);
    }

    .required-mark {
        color: #dc3545;
    }

    .image-upload-box {
        border: 2px dashed #b8d6c2;
        background: #f8fcf9;
        border-radius: 12px;
        padding: 22px 16px;
        text-align: center;
    }

    .image-upload-box i {
        font-size: 38px;
        color: #39865a;
    }

    .image-upload-box p {
        font-size: 13px;
        color: #6c757d;
        margin: 8px 0 14px;
    }

    #imagePreview {
        display: none;
        width: 100%;
        max-height: 230px;
        object-fit: contain;
        border-radius: 10px;
        margin-top: 16px;
        background: #f1f3f1;
    }

    .form-note {
        font-size: 12px;
        color: #7b8580;
        margin-top: 6px;
    }

    .post-item-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #edf1ed;
    }

    .btn-post-item {
        background: #28794b;
        border: 1px solid #28794b;
        color: white;
        padding: 11px 28px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-post-item:hover {
        background: #20633d;
        border-color: #20633d;
        color: white;
    }

    .btn-back-item {
        padding: 11px 22px;
        border-radius: 8px;
        font-size: 14px;
    }

    @media (max-width: 767px) {
        .post-item-page {
            padding: 28px 12px 40px;
        }

        .post-item-card {
            padding: 22px 16px;
        }

        .post-item-header h2 {
            font-size: 24px;
        }

        .post-item-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .post-item-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="post-item-page">
    <div class="post-item-wrapper">

        <div class="post-item-header">
            <h2><i class="bi bi-box-seam me-2"></i>Chia sẻ món đồ của bạn</h2>
            <p>Trao đi món đồ không còn sử dụng, mang đến giá trị mới cho cộng đồng.</p>
        </div>

        <div class="post-item-card">

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= itemEscape($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= itemEscape($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST"
                  action="index.php?controller=Item&action=store"
                  enctype="multipart/form-data">

                <div class="row g-4">

                    <!-- Cột thông tin món đồ -->
                    <div class="col-md-7">

                        <h5 class="form-section-title">
                            <i class="bi bi-info-circle me-2"></i>Thông tin món đồ
                        </h5>

                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Tên món đồ <span class="required-mark">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="title"
                                name="title"
                                maxlength="200"
                                placeholder="Ví dụ: Bàn học gỗ còn tốt"
                                value="<?= itemEscape($_POST['title'] ?? '') ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                Danh mục <span class="required-mark">*</span>
                            </label>
                            <select
                                class="form-select"
                                id="category_id"
                                name="category_id"
                                required>

                                <option value="">-- Chọn danh mục --</option>

                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= itemEscape($category['id']) ?>"
                                        <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= itemEscape($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Mô tả món đồ <span class="required-mark">*</span>
                            </label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                placeholder="Mô tả đặc điểm, công dụng và thông tin cần thiết..."
                                required><?= itemEscape($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="condition_note" class="form-label">
                                Tình trạng món đồ <span class="required-mark">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="condition_note"
                                name="condition_note"
                                maxlength="100"
                                placeholder="Ví dụ: Đã qua sử dụng, còn hoạt động tốt"
                                value="<?= itemEscape($_POST['condition_note'] ?? '') ?>"
                                required>
                        </div>

                        <div class="mb-0">
                            <label for="pickup_location" class="form-label">
                                 Địa điểm nhận đồ trực tiếp
                                    <span class="required-mark">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="pickup_location"
                                name="pickup_location"
                                maxlength="255"
                                placeholder="Nhập địa chỉ hoặc khu vực nhận đồ"
                                value="<?= itemEscape($_POST['pickup_location'] ?? '') ?>"
                                required>
                            <div class="form-note">
                                Khu vực hẹn gặp nếu người nhận muốn tự đến lấy đồ.
                            </div>
                        </div>

                    </div>

                    <!-- Cột hình ảnh -->
                    <div class="col-md-5">

                        <h5 class="form-section-title">
                            <i class="bi bi-image me-2"></i>Hình ảnh món đồ
                        </h5>

                        <div class="image-upload-box">

                            <i class="bi bi-cloud-arrow-up"></i>

                            <p>
                                Chọn hình ảnh rõ nét về món đồ bạn muốn chia sẻ.
                            </p>

                            <input
                                type="file"
                                class="form-control"
                                id="image"
                                name="image"
                                accept="image/jpeg,image/png,image/webp"
                                required>

                            <div class="form-note">
                                Hỗ trợ JPG, PNG, WEBP. Dung lượng tối đa 5MB.
                            </div>

                            <img id="imagePreview" src="" alt="Xem trước hình ảnh">

                        </div>

                        <div class="alert alert-light border mt-4 mb-0">
                            <div class="d-flex gap-2">
                                <i class="bi bi-lightbulb text-success"></i>
                                <div>
                                    <strong class="small">Mẹo đăng bài</strong>
                                    <p class="small text-muted mb-0 mt-1">
                                        Chụp ảnh đủ sáng, thể hiện rõ tình trạng thực tế
                                        và cung cấp mô tả trung thực để người nhận dễ lựa chọn.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

<!-- Địa chỉ gửi hàng GHN -->
<div class="mt-4 pt-3 border-top">
    <h5 class="form-section-title">
        <i class="bi bi-geo-alt me-2"></i>
        Địa chỉ gửi hàng GHN
    </h5>

    <p class="text-muted small">
        Chọn địa chỉ nơi món đồ được gửi đi.
        Hệ thống sẽ sử dụng địa chỉ này để tính phí vận chuyển.
    </p>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="sender_province" class="form-label">
                Tỉnh/Thành phố <span class="required-mark">*</span>
            </label>
            <select id="sender_province"
                    name="ghn_province_id"
                    class="form-select" required>
                <option value="">Đang tải tỉnh/thành...</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="sender_district" class="form-label">
                Quận/Huyện <span class="required-mark">*</span>
            </label>
            <select id="sender_district"
                    name="ghn_district_id"
                    class="form-select" required disabled>
                <option value="">Chọn tỉnh/thành trước</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="sender_ward" class="form-label">
                Phường/Xã <span class="required-mark">*</span>
            </label>
            <select id="sender_ward"
                    name="ghn_ward_code"
                    class="form-select" required disabled>
                <option value="">Chọn quận/huyện trước</option>
            </select>
        </div>

        <div class="col-12">
            <label for="sender_address_detail" class="form-label">
                Số nhà, tên đường <span class="required-mark">*</span>
            </label>
            <input type="text"
                   id="sender_address_detail"
                   name="sender_address_detail"
                   class="form-control"
                   maxlength="255"
                   placeholder="Ví dụ: 123 Nguyễn Văn A"
                   required>
        </div>
    </div>

    <div id="sender_address_error"
         class="text-danger small mt-2"
         role="alert"></div>
</div>


                <!-- Nút thao tác -->
                <div class="post-item-actions">

                    <a href="index.php" class="btn btn-outline-secondary btn-back-item">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>

                    <button type="submit" class="btn btn-post-item">
                        <i class="bi bi-send me-2"></i>Đăng món đồ
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            alert('Vui lòng chọn ảnh JPG, PNG hoặc WEBP.');
            this.value = '';
            imagePreview.style.display = 'none';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Dung lượng ảnh không được vượt quá 5MB.');
            this.value = '';
            imagePreview.style.display = 'none';
            return;
        }

        imagePreview.src = URL.createObjectURL(file);
        imagePreview.style.display = 'block';
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const province = document.getElementById('sender_province');
    const district = document.getElementById('sender_district');
    const ward = document.getElementById('sender_ward');
    const errorBox = document.getElementById('sender_address_error');

    if (!province || !district || !ward) return;

    const endpoint = 'index.php?controller=Shipment&action=locations';

    function reset(select, label) {
        select.replaceChildren(new Option(label, ''));
        select.disabled = true;
    }

    async function getLocations(type, params = {}) {
        const url = new URL(endpoint, window.location.href);
        url.searchParams.set('type', type);

        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });

        const response = await fetch(url, {
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (!response.ok || !result.success ||
            !Array.isArray(result.data)) {
            throw new Error(result.message || 'Không thể tải địa chỉ GHN');
        }

        return result.data;
    }

    function populate(select, data, idField, nameField, label) {
        reset(select, label);

        data.forEach(item => {
            select.add(new Option(item[nameField], item[idField]));
        });

        select.disabled = false;
    }

    async function loadProvinces() {
        errorBox.textContent = '';

        try {
            const data = await getLocations('provinces');

            populate(
                province, data,
                'ProvinceID', 'ProvinceName',
                'Chọn tỉnh/thành'
            );
        } catch (error) {
            reset(province, 'Không tải được tỉnh/thành');
            errorBox.textContent = error.message;
        }
    }

    province.addEventListener('change', async () => {
        const selectedProvince = province.value;

        reset(district, 'Chọn quận/huyện');
        reset(ward, 'Chọn phường/xã');
        errorBox.textContent = '';

        if (!selectedProvince) return;

        reset(district, 'Đang tải quận/huyện...');

        try {
            const data = await getLocations('districts', {
                province_id: selectedProvince
            });

            if (province.value !== selectedProvince) return;

            populate(
                district, data,
                'DistrictID', 'DistrictName',
                'Chọn quận/huyện'
            );
        } catch (error) {
            if (province.value !== selectedProvince) return;

            reset(district, 'Không tải được quận/huyện');
            errorBox.textContent = error.message;
        }
    });

    district.addEventListener('change', async () => {
        const selectedDistrict = district.value;
        const selectedProvince = province.value;

        reset(ward, 'Chọn phường/xã');
        errorBox.textContent = '';

        if (!selectedDistrict) return;

        reset(ward, 'Đang tải phường/xã...');

        try {
            const data = await getLocations('wards', {
                district_id: selectedDistrict
            });

            if (
                district.value !== selectedDistrict ||
                province.value !== selectedProvince
            ) return;

            populate(
                ward, data,
                'WardCode', 'WardName',
                'Chọn phường/xã'
            );
        } catch (error) {
            if (district.value !== selectedDistrict) return;

            reset(ward, 'Không tải được phường/xã');
            errorBox.textContent = error.message;
        }
    });

    loadProvinces();
});
</script>

