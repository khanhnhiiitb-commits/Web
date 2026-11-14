<?php
$title = 'Thiết lập giao nhận - Give & Take';

$e = static fn($value) => htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$id = (int)$delivery['request_id'];
$isGiver = (int)$delivery['giver_id'] === (int)$userId;
$isReceiver = (int)$delivery['requester_id'] === (int)$userId;

$status = (string)($delivery['status'] ?? '');
$method = (string)($delivery['delivery_method'] ?? '');

$canPropose = $isGiver
    && in_array($status, ['accepted', 'arranged'], true)
    && empty($delivery['tracking_number'])
    && empty($delivery['ghn_status']);
$canCreateShipment = $isGiver && $status === 'arranged'
    && $method === 'shipping'
    && !empty($delivery['agreed_at'])
    && empty($delivery['tracking_number'])
    && empty($delivery['ghn_status'])
    && (int)($delivery['to_province_id'] ?? 0) > 0
    && in_array((int)($delivery['payment_type_id'] ?? 0), [1, 2], true);

$canAgree = $isReceiver
    && $status === 'accepted'
    && !empty($delivery['proposed_at'])
    && empty($delivery['agreed_at']);

$meetingTime = !empty($delivery['meeting_time'])
    ? date('Y-m-d\TH:i', strtotime($delivery['meeting_time']))
    : '';
?>

<div class="container py-4" style="max-width:780px">
    <a href="index.php?controller=Message&amp;action=chat&amp;id=<?= $id ?>" class="text-decoration-none small">← Quay lại chat</a>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-3 mb-3">
        <div>
            <h3 class="fw-bold mb-1">Giao nhận món đồ</h3>
            <p class="text-muted small mb-0">#<?= $id ?> · <?= $e($delivery['item_title']) ?></p>
        </div>
        <?php
        $statusLabels = [
            'accepted' => 'Chờ thỏa thuận',
            'arranged' => 'Đã thỏa thuận',
            'in_progress' => 'Đang giao nhận',
            'awaiting_confirmation' => 'Chờ xác nhận',
            'completed' => 'Hoàn tất'
        ];
        ?>
        <span class="badge <?= $status === 'completed' ? 'bg-success' : 'bg-secondary' ?> py-2">
            <?= $e($statusLabels[$status] ?? $status) ?>
        </span>
    </div>

    <?php if (!empty($_SESSION['delivery_notice'])): ?>
        <div class="alert alert-info py-2 small" role="status">
            <?= $e($_SESSION['delivery_notice']) ?>
        </div>
        <?php unset($_SESSION['delivery_notice']); ?>
    <?php endif; ?>

    <?php if ($method): ?>
        <section class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Thông tin giao nhận</h5>
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <span class="text-muted">Phương thức</span>
                    <strong><?= $method === 'pickup' ? 'Nhận trực tiếp' : 'Giao hàng GHN' ?></strong>
                </div>
                <?php if ($method === 'pickup'): ?>
                    <p class="mb-1"><span class="text-muted">Địa điểm:</span> <?= $e($delivery['meeting_location']) ?></p>
                    <p class="mb-0"><span class="text-muted">Thời gian:</span> <?= $e($delivery['meeting_time']) ?></p>
                <?php else: ?>
                    <div class="d-flex justify-content-between gap-3 mb-2">
                        <span class="text-muted">Phí vận chuyển</span>
                        <strong><?= number_format((int)($delivery['shipping_fee_vnd'] ?? 0), 0, ',', '.') ?>đ</strong>
                    </div>
                    <?php if (!empty($delivery['tracking_number'])): ?>
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <span class="text-muted">Mã vận đơn</span>
                            <strong><?= $e($delivery['tracking_number']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <span class="text-muted">Trạng thái GHN</span>
                            <span><?= $e($delivery['ghn_status'] ?: 'Chưa đồng bộ') ?></span>
                        </div>
                        <form method="post" action="index.php?controller=Delivery&amp;action=refreshShipmentStatus&amp;id=<?= $id ?>">
                            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i>Cập nhật GHN
                            </button>
                        </form>
                    <?php elseif ($isGiver && in_array((string)($delivery['ghn_status'] ?? ''), ['needs_review', 'creating'], true)): ?>
                        <form method="post" action="index.php?controller=Delivery&amp;action=reconcileShipment&amp;id=<?= $id ?>">
                            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                            <button type="submit" class="btn btn-outline-warning btn-sm">Đối soát vận đơn GHN</button>
                        </form>
                        <?php if ((string)($delivery['ghn_status'] ?? '') === 'needs_review'): ?>
                            <form method="post" action="index.php?controller=Delivery&amp;action=createShipment&amp;id=<?= $id ?>" class="mt-2">
                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="retry_shipment" value="1">
                                <div class="form-check small mb-2">
                                    <input class="form-check-input" type="checkbox" id="confirmRetryGhn" name="confirm_retry" value="1" required>
                                    <label class="form-check-label" for="confirmRetryGhn">
                                        Tôi muốn thử tạo lại. Hệ thống sẽ kiểm tra mã GNT-<?= $id ?> trước và chỉ gửi lại nếu GHN xác nhận không có đơn.
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-arrow-repeat me-1"></i>Thử tạo lại vận đơn
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($delivery['tracking_number'])): ?>
                        <?php
                        $ghnLabels = [
                            'created' => 'Đã tạo vận đơn',
                            'ready_to_pick' => 'Chờ lấy hàng',
                            'picking' => 'Đang lấy hàng',
                            'picked' => 'Đã lấy hàng',
                            'storing' => 'Đang lưu kho',
                            'transporting' => 'Đang vận chuyển',
                            'sorting' => 'Đang phân loại',
                            'delivering' => 'Đang giao hàng',
                            'delivered' => 'Giao hàng thành công',
                            'delivery_fail' => 'Giao hàng thất bại',
                            'waiting_to_return' => 'Chờ hoàn hàng',
                            'return' => 'Đang hoàn hàng',
                            'returned' => 'Đã hoàn hàng',
                            'cancel' => 'Đã hủy',
                        ];
                        ?>
                        <details class="mt-3" <?= !empty($ghnEvents) ? 'open' : '' ?>>
                            <summary class="fw-semibold small">Lịch sử cập nhật GHN (<?= count($ghnEvents ?? []) ?>)</summary>
                            <?php if (empty($ghnEvents)): ?>
                                <p class="small text-muted mt-2 mb-0">Chưa có sự kiện được lưu. Có thể bấm Cập nhật GHN để đồng bộ.</p>
                            <?php else: ?>
                                <ol class="list-group list-group-numbered mt-2">
                                    <?php foreach ($ghnEvents as $event): ?>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between gap-2">
                                            <div>
                                                <div class="fw-semibold small"><?= $e($ghnLabels[$event['ghn_status']] ?? $event['ghn_status']) ?></div>
                                                <div class="text-muted small"><?= $e($event['event_type']) ?> · <?= $event['source'] === 'webhook' ? 'GHN Webhook' : 'Đối soát API' ?></div>
                                            </div>
                                            <div class="text-muted small"><?= $e($event['event_time']) ?> UTC</div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </details>
                    <?php endif; ?>
                    <details class="mt-3">
                        <summary class="small text-muted" style="cursor:pointer">Xem địa chỉ và chi tiết giao hàng</summary>
                        <div class="small mt-2">
                            <p class="mb-1"><strong>Địa chỉ:</strong> <?= $e($delivery['shipping_address']) ?></p>
                            <p class="mb-1"><strong>Bên trả phí:</strong>
                                <?= (int)($delivery['payment_type_id'] ?? 0) === 1 ? 'Người tặng' : ((int)($delivery['payment_type_id'] ?? 0) === 2 ? 'Người nhận' : 'Chưa chọn') ?>
                            </p>
                            <?php if (!empty($delivery['delivery_note'])): ?>
                                <p class="mb-0"><strong>Ghi chú:</strong> <?= nl2br($e($delivery['delivery_note'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($canPropose): ?>
    <details class="card card-body mb-3">
        <summary class="fw-semibold" style="cursor:pointer">Thiết lập / chỉnh sửa phương thức giao nhận</summary>

        <form
            id="deliveryProposeForm"
            method="post"
            action="index.php?controller=Delivery&amp;action=propose&amp;id=<?= $id ?>"
            class="card card-body mb-4"
        >

            <h5 class="mt-3">Chọn phương thức giao nhận</h5>

            <input
                type="hidden"
                name="csrf_token"
                value="<?= $e($csrfToken) ?>"
            >

            <label class="form-label" for="method">
                Phương thức
            </label>

            <select
                name="method"
                id="method"
                class="form-select mb-3"
                required
            >
                <option
                    value="pickup"
                    <?= $method === 'pickup' ? 'selected' : '' ?>
                >
                    Người nhận đến lấy
                </option>

                <option
                    value="shipping"
                    <?= $method === 'shipping' ? 'selected' : '' ?>
                >
                    Người tặng gửi vận chuyển (GHN)
                </option>
            </select>

            <!-- NHẬN TRỰC TIẾP -->
            <div id="pickup-fields">

                <label
                    class="form-label"
                    for="meeting_location"
                >
                    Địa điểm hẹn
                </label>

                <input
                    id="meeting_location"
                    class="form-control mb-2"
                    name="meeting_location"
                    maxlength="255"
                    value="<?= $e($delivery['meeting_location']) ?>"
                >

                <label
                    class="form-label"
                    for="meeting_time"
                >
                    Ngày giờ hẹn
                </label>

                <input
                    id="meeting_time"
                    class="form-control mb-3"
                    name="meeting_time"
                    type="datetime-local"
                    value="<?= $e($meetingTime) ?>"
                >

            </div>

            <!-- GIAO HÀNG GHN -->
            <div id="shipping-fields" hidden>

                <input
                    type="hidden"
                    name="carrier"
                    value="GHN"
                >

                <div class="alert alert-light border">
                    <strong>
                        <i class="bi bi-truck me-1"></i>
                        Giao Hàng Nhanh (GHN)
                    </strong>

                    <p class="small text-muted mb-0">
                        Chọn tỉnh/thành, quận/huyện và phường/xã
                        để lấy báo giá vận chuyển dự kiến.
                    </p>
                </div>

                <label
                    class="form-label"
                    for="shipping_address"
                >
                    Địa chỉ giao hàng
                </label>

                <textarea
                    id="shipping_address"
                    class="form-control mb-3"
                    name="shipping_address"
                    maxlength="500"
                    rows="2"
                    placeholder="Ví dụ: 234 Ba Hạt, số nhà, tên đường..."
                ><?= $e($delivery['shipping_address']) ?></textarea>

                <div class="row g-3">

                    <div class="col-12">
                        <label
                            class="form-label"
                            for="to_province_id"
                        >
                            Tỉnh / Thành phố nhận
                        </label>

                        <select
                            id="to_province_id"
                            name="to_province_id"
                            class="form-select"
                            required
                            disabled
                        >
                            <option value="">
                                Đang tải tỉnh/thành...
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="to_district_id"
                        >
                            Quận / Huyện nhận
                        </label>

                        <select
                            id="to_district_id"
                            name="to_district_id"
                            class="form-select"
                            required
                            disabled
                        >
                            <option value="">
                                Chọn tỉnh/thành trước
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="to_ward_code"
                        >
                            Phường / Xã nhận
                        </label>

                        <select
                            id="to_ward_code"
                            name="to_ward_code"
                            class="form-select"
                            required
                            disabled
                        >
                            <option value="">
                                Chọn quận/huyện trước
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div
                            id="ghnAddressStatus"
                            class="small text-muted"
                            role="status"
                            aria-live="polite"
                        ></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="weight">
                            Trọng lượng (gram)
                        </label>

                        <input
                            id="weight"
                            type="number"
                            name="weight"
                            class="form-control"
                            value="500"
                            min="1"
                            max="50000"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="service_type_id"
                        >
                            Loại dịch vụ
                        </label>

                        <select
                            id="service_type_id"
                            name="service_type_id"
                            class="form-select"
                            required
                        >
                            <option value="2">Hàng nhẹ</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="length">
                            Dài (cm)
                        </label>

                        <input
                            id="length"
                            type="number"
                            name="length"
                            class="form-control"
                            value="20"
                            min="1"
                            max="200"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="width">
                            Rộng (cm)
                        </label>

                        <input
                            id="width"
                            type="number"
                            name="width"
                            class="form-control"
                            value="15"
                            min="1"
                            max="200"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="height">
                            Cao (cm)
                        </label>

                        <input
                            id="height"
                            type="number"
                            name="height"
                            class="form-control"
                            value="10"
                            min="1"
                            max="200"
                            step="1"
                            required
                        >
                    </div>

                </div>

                <div class="mt-3">
                    <label class="form-label" for="payment_type_id">Bên chịu phí vận chuyển GHN</label>
                    <select id="payment_type_id" name="payment_type_id" class="form-select" required>
                        <option value="">-- Chọn bên trả phí --</option>
                        <option value="1">Người tặng trả phí</option>
                        <option value="2">Người nhận trả phí</option>
                    </select>
                    <div class="form-text">Bên nhận cần đồng ý lại sau khi người tặng gửi đề xuất. Đây không phải thanh toán trực tuyến.</div>
                </div>

                <button
                    type="button"
                    id="calculateGhnFee"
                    class="btn btn-outline-success mt-3"
                >
                    <i class="bi bi-calculator me-1"></i>
                    Tính phí GHN
                </button>

                <div
                    id="ghnFeeResult"
                    class="alert alert-secondary mt-3"
                    role="status"
                    aria-live="polite"
                >
                    Chưa có báo giá GHN.
                </div>

            </div>

            <label
                class="form-label"
                for="delivery_note"
            >
                Ghi chú
            </label>

            <textarea
                id="delivery_note"
                class="form-control mb-3"
                name="note"
                maxlength="2000"
                rows="2"
            ><?= $e($delivery['delivery_note']) ?></textarea>

            <button
                class="btn btn-success"
                type="submit"
            >
                Gửi / cập nhật đề xuất
            </button>

            <small class="text-muted mt-2">
                Cập nhật đề xuất sẽ yêu cầu người nhận đồng ý lại.
            </small>

        </form>

    </details>
    <?php endif; ?>

    <?php if ($canAgree): ?>

        <form
            method="post"
            action="index.php?controller=Delivery&amp;action=agree&amp;id=<?= $id ?>"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= $e($csrfToken) ?>"
            >

            <button
                type="submit"
                class="btn btn-success"
                onclick="return confirm('Bạn đồng ý phương thức và mức phí vận chuyển được đề xuất? Đây chưa phải thanh toán.');"
            >
                Đồng ý phương thức giao nhận
            </button>
        </form>

    <?php endif; ?>

    <?php if ($canCreateShipment): ?>
        <!-- [GHN] Chỉ người tặng được tạo vận đơn khi người nhận đã đồng ý -->
        <form method="post"
              action="index.php?controller=Delivery&amp;action=createShipment&amp;id=<?= $id ?>"
              class="card card-body mb-3"
              onsubmit="return confirm('Bạn xác nhận tạo vận đơn giao hàng cho món đồ này? Vui lòng kiểm tra địa chỉ, số điện thoại và bên trả phí trước khi tiếp tục.');">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <h5>Tạo vận đơn GHN Staging</h5>
            <p class="small text-muted">Chỉ thực hiện sau khi đã xác nhận đầy đủ thông tin và bên trả phí.</p>
            <button type="submit" class="btn btn-success">Tạo vận đơn GHN</button>
        </form>
    <?php endif; ?>

    <?php
        $giverConfirmed = !empty($delivery['giver_confirmed_at']);
        $receiverConfirmed = !empty($delivery['receiver_confirmed_at']);
        $hasConfirmed = $isGiver ? $giverConfirmed : $receiverConfirmed;
        $canConfirmHandover = in_array($status, ['arranged', 'in_progress', 'awaiting_confirmation'], true)
            && !empty($delivery['agreed_at'])
            && ($isGiver || $isReceiver)
            && !$hasConfirmed
            && ($method === 'pickup' || ($method === 'shipping' && !empty($delivery['tracking_number'])));
    ?>

    <?php if ($status === 'completed'): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>Trao tặng hoàn tất!</div>
    <?php elseif (in_array($status, ['arranged', 'in_progress', 'awaiting_confirmation'], true)): ?>
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Xác nhận giao nhận</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Người tặng</span>
                    <span class="<?= $giverConfirmed ? 'text-success' : 'text-muted' ?>">
                        <?= $giverConfirmed ? '✓ Đã xác nhận' : 'Chưa xác nhận' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Người nhận</span>
                    <span class="<?= $receiverConfirmed ? 'text-success' : 'text-muted' ?>">
                        <?= $receiverConfirmed ? '✓ Đã xác nhận' : 'Chưa xác nhận' ?>
                    </span>
                </div>
                <?php if ($canConfirmHandover): ?>
                    <form method="post" action="index.php?controller=Delivery&amp;action=confirmHandover&amp;id=<?= $id ?>"
                          onsubmit="return confirm('Bạn xác nhận đã <?= $isGiver ? 'bàn giao' : 'nhận' ?> món đồ thực tế?');">
                        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                        <label class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="handover_attested" value="1" required>
                            <span class="form-check-label small">Tôi xác nhận đã thực sự <?= $isGiver ? 'giao' : 'nhận' ?> món đồ.</span>
                        </label>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i>
                            <?= $isGiver ? 'Xác nhận đã giao đồ' : 'Xác nhận đã nhận đồ' ?>
                        </button>
                    </form>
                <?php elseif ($hasConfirmed): ?>
                    <p class="text-success small mb-0">Bạn đã xác nhận. Đang chờ bên còn lại.</p>
                <?php elseif ($method === 'shipping' && empty($delivery['tracking_number'])): ?>
                    <p class="text-muted small mb-0">Chờ tạo vận đơn để có thể xác nhận.</p>
                <?php endif; ?>
                <?php if ($method === 'shipping' && ($delivery['ghn_status'] ?? '') !== 'delivered'): ?>
                    <p class="text-muted small mt-2 mb-0">Xác nhận trên Give &amp; Take là xác nhận của người dùng, không phải xác nhận từ GHN. Chỉ xác nhận khi đã giao nhận thực tế.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('deliveryProposeForm');
    if (!form) return;

    const method = document.getElementById('method');
    const pickupFields = document.getElementById('pickup-fields');
    const shippingFields = document.getElementById('shipping-fields');

    const province = document.getElementById('to_province_id');
    const district = document.getElementById('to_district_id');
    const ward = document.getElementById('to_ward_code');

    const addressStatus = document.getElementById('ghnAddressStatus');
    const feeResult = document.getElementById('ghnFeeResult');
    const feeButton = document.getElementById('calculateGhnFee');

    const requestId = <?= json_encode($id) ?>;

    let quoteReady = false;
    let provinceRequest = 0;
    let districtRequest = 0;
    let calculating = false;

    function setAddressStatus(message, isError = false) {
        addressStatus.textContent = message;
        addressStatus.className = isError
            ? 'small text-danger'
            : 'small text-muted';
    }

    function resetQuote() {
        quoteReady = false;

        feeResult.className = 'alert alert-secondary mt-3';
        feeResult.textContent = 'Chưa có báo giá GHN.';
    }

    function resetSelect(select, placeholder) {
        select.replaceChildren(new Option(placeholder, ''));
        select.disabled = true;
    }

    function toggleMethod() {
        const isShipping = method.value === 'shipping';

        shippingFields.hidden = !isShipping;
        pickupFields.hidden = isShipping;

        pickupFields.querySelectorAll('input').forEach(input => {
            input.required = !isShipping;
            input.disabled = isShipping;
        });

        shippingFields.querySelectorAll(
            'input:not([type="hidden"]), textarea, select'
        ).forEach(input => {
            if (input === province || input === district || input === ward) {
                input.required = isShipping;
            } else {
                input.required = isShipping;
                input.disabled = !isShipping;
            }
        });

        if (isShipping) {
            province.disabled = province.options.length <= 1;
            district.disabled = !province.value || district.options.length <= 1;
            ward.disabled = !district.value || ward.options.length <= 1;
        } else {
            province.disabled = true;
            district.disabled = true;
            ward.disabled = true;
        }

        resetQuote();
    }

    async function loadLocations(type, id = null) {
        const params = new URLSearchParams({
            controller: 'Shipment',
            action: 'locations',
            type: type
        });

        if (type === 'districts') {
            params.set('province_id', String(id));
        }

        if (type === 'wards') {
            params.set('district_id', String(id));
        }

        const response = await fetch(
            'index.php?' + params.toString(),
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        const raw = await response.text();

        let json;
        try {
            json = JSON.parse(raw);
        } catch (error) {
            throw new Error(
                'Máy chủ trả dữ liệu không phải JSON (HTTP '
                + response.status
                + '). Kiểm tra URL hoặc router.'
            );
        }

        if (!response.ok || !json.success) {
            throw new Error(
                json.message || (
                    'Không tải được địa chỉ GHN. HTTP '
                    + response.status
                )
            );
        }

        if (!Array.isArray(json.data)) {
            throw new Error('GHN trả danh sách địa chỉ không hợp lệ.');
        }

        return json.data;
    }

    function populateSelect(
        select,
        items,
        valueKey,
        labelKey,
        placeholder
    ) {
        select.replaceChildren(new Option(placeholder, ''));

        items.forEach(item => {
            if (
                item[valueKey] !== undefined
                && item[valueKey] !== null
                && item[labelKey] !== undefined
            ) {
                select.add(new Option(
                    String(item[labelKey]),
                    String(item[valueKey])
                ));
            }
        });

        select.disabled =
            method.value !== 'shipping' || select.options.length <= 1;
    }

    async function loadProvinces() {
        resetSelect(province, 'Đang tải tỉnh/thành...');
        setAddressStatus('Đang tải danh sách tỉnh/thành...');

        try {
            const data = await loadLocations('provinces');

            populateSelect(
                province,
                data,
                'ProvinceID',
                'ProvinceName',
                'Chọn tỉnh/thành phố'
            );

            if (data.length === 0) {
                setAddressStatus(
                    'GHN không trả về tỉnh/thành nào.',
                    true
                );
            } else {
                setAddressStatus('');
            }

        } catch (error) {
            resetSelect(province, 'Không tải được tỉnh/thành');
            setAddressStatus(error.message, true);
        }
    }

    province.addEventListener('change', async () => {
        const requestNumber = ++provinceRequest;
        ++districtRequest;

        resetQuote();

        resetSelect(district, 'Chọn tỉnh/thành trước');
        resetSelect(ward, 'Chọn quận/huyện trước');

        if (!province.value) {
            setAddressStatus('');
            return;
        }

        resetSelect(district, 'Đang tải quận/huyện...');
        setAddressStatus('Đang tải quận/huyện...');

        try {
            const data = await loadLocations(
                'districts',
                province.value
            );

            if (requestNumber !== provinceRequest) return;

            populateSelect(
                district,
                data,
                'DistrictID',
                'DistrictName',
                'Chọn quận/huyện'
            );

            setAddressStatus(
                data.length ? '' : 'Không có quận/huyện.',
                data.length === 0
            );

        } catch (error) {
            if (requestNumber !== provinceRequest) return;

            resetSelect(district, 'Không tải được quận/huyện');
            setAddressStatus(error.message, true);
        }
    });

    district.addEventListener('change', async () => {
        const requestNumber = ++districtRequest;

        resetQuote();
        resetSelect(ward, 'Chọn quận/huyện trước');

        if (!district.value) {
            setAddressStatus('');
            return;
        }

        resetSelect(ward, 'Đang tải phường/xã...');
        setAddressStatus('Đang tải phường/xã...');

        try {
            const data = await loadLocations(
                'wards',
                district.value
            );

            if (requestNumber !== districtRequest) return;

            populateSelect(
                ward,
                data,
                'WardCode',
                'WardName',
                'Chọn phường/xã'
            );

            setAddressStatus(
                data.length ? '' : 'Không có phường/xã.',
                data.length === 0
            );

        } catch (error) {
            if (requestNumber !== districtRequest) return;

            resetSelect(ward, 'Không tải được phường/xã');
            setAddressStatus(error.message, true);
        }
    });

    method.addEventListener('change', toggleMethod);

    form.querySelectorAll(
        '#shipping-fields input, #shipping-fields textarea, #shipping-fields select'
    ).forEach(input => {
        input.addEventListener('input', resetQuote);
        input.addEventListener('change', resetQuote);
    });

    feeButton.addEventListener('click', async () => {
        if (calculating) return;

        if (!province.value || !district.value || !ward.value) {
            feeResult.className = 'alert alert-warning mt-3';
            feeResult.textContent =
                'Vui lòng chọn đầy đủ tỉnh/thành, quận/huyện và phường/xã.';
            return;
        }

        const shippingAddress =
            document.getElementById('shipping_address');

        if (!shippingAddress.value.trim()) {
            feeResult.className = 'alert alert-warning mt-3';
            feeResult.textContent =
                'Vui lòng nhập địa chỉ giao hàng.';
            return;
        }

        const requiredFields = [
            'weight',
            'length',
            'width',
            'height',
            'service_type_id'
        ];

        for (const fieldId of requiredFields) {
            const input = document.getElementById(fieldId);

            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }
        }

        resetQuote();
        calculating = true;
        feeButton.disabled = true;

        feeResult.className = 'alert alert-info mt-3';
        feeResult.textContent = 'Đang tính phí GHN...';

        try {
            const data = new FormData(form);
            data.set('request_id', String(requestId));

            const response = await fetch(
                'index.php?controller=Shipment&action=calculateFee',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data,
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const raw = await response.text();

            let json;
            try {
                json = JSON.parse(raw);
            } catch (error) {
                throw new Error(
                    'Máy chủ không trả JSON hợp lệ (HTTP '
                    + response.status + ').'
                );
            }

            if (!response.ok || !json.success) {
                throw new Error(
                    json.message || 'Không thể tính phí GHN.'
                );
            }

            quoteReady = true;

            feeResult.className = 'alert alert-success mt-3';
            feeResult.textContent =
                'Phí vận chuyển dự kiến: '
                + json.formatted_fee;

        } catch (error) {
            resetQuote();

            feeResult.className = 'alert alert-danger mt-3';
            feeResult.textContent = error.message;

        } finally {
            calculating = false;
            feeButton.disabled = false;
        }
    });

    form.addEventListener('submit', event => {
        if (method.value === 'shipping' && !quoteReady) {
            event.preventDefault();

            feeResult.className = 'alert alert-warning mt-3';
            feeResult.textContent =
                'Vui lòng tính phí GHN thành công trước khi gửi đề xuất.';

            feeResult.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });

    toggleMethod();
    loadProvinces();
});
</script>
