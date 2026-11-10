<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$requestId = (int)($requestId ?? $_GET['request_id'] ?? 0);
?>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3">
            Tính phí vận chuyển GHN
        </h5>

        <form id="ghnFeeForm">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(
                       $_SESSION['csrf_token'],
                       ENT_QUOTES,
                       'UTF-8'
                   ) ?>">

            <input type="hidden" name="request_id"
                   value="<?= $requestId ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Mã quận/huyện nhận
                    </label>
                    <input type="number"
                           name="to_district_id"
                           class="form-control"
                           min="1"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Mã phường/xã nhận
                    </label>
                    <input type="text"
                           name="to_ward_code"
                           class="form-control"
                           maxlength="30"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Trọng lượng (gram)
                    </label>
                    <input type="number"
                           name="weight"
                           class="form-control"
                           min="1"
                           value="500"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Loại dịch vụ
                    </label>
                    <select name="service_type_id"
                            class="form-select">
                        <option value="2">
                            Hàng nhẹ
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Dài (cm)
                    </label>
                    <input type="number" name="length"
                           class="form-control"
                           min="1" value="20" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Rộng (cm)
                    </label>
                    <input type="number" name="width"
                           class="form-control"
                           min="1" value="15" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Cao (cm)
                    </label>
                    <input type="number" name="height"
                           class="form-control"
                           min="1" value="10" required>
                </div>
            </div>

            <button type="submit"
                    class="btn btn-success mt-3"
                    id="calculateFeeBtn">
                <i class="bi bi-calculator"></i>
                Tính phí vận chuyển
            </button>
        </form>

        <div id="ghnFeeResult"
             class="alert mt-3 d-none"
             role="status"></div>
    </div>
</div>

<script>
document.getElementById('ghnFeeForm')
    .addEventListener('submit', async function (event) {
        event.preventDefault();

        const result = document.getElementById('ghnFeeResult');
        const button = document.getElementById('calculateFeeBtn');

        result.className = 'alert alert-info mt-3';
        result.textContent = 'Đang tính phí vận chuyển...';
        button.disabled = true;

        try {
            const response = await fetch(
                'index.php?controller=Shipment&action=calculateFee',
                {
                    method: 'POST',
                    body: new FormData(this),
                    credentials: 'same-origin'
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Không thể tính phí.'
                );
            }

            result.className = 'alert alert-success mt-3';
            result.textContent =
                'Phí vận chuyển dự kiến: ' +
                data.formatted_fee;

        } catch (error) {
            result.className = 'alert alert-danger mt-3';
            result.textContent = error.message;

        } finally {
            button.disabled = false;
        }
    });
</script>