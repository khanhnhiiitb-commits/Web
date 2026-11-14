<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DeliveryModel;
use App\Models\PaymentModel;
use App\Models\GhnEventModel;
use App\Models\DeliveryDemoModel;
use App\Services\GhnClient;
use RuntimeException;
use Throwable;

class DeliveryController extends Controller
{
    private DeliveryModel $delivery;

    public function __construct()
    {
        $this->delivery = new DeliveryModel();
    }

    private function uid(): int
    {
        return (int) ($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
    }

    private function id(): int
    {
        return max(0, (int) ($_GET['id'] ?? 0));
    }

    private function redirectToTracking(int $id): void
    {
        header('Location: index.php?controller=Delivery&action=tracking&id=' . $id);
        exit;
    }

    private function reject(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit;
    }

    private function verifyPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->reject('Chỉ hỗ trợ POST.', 405);
        }

        $token = $_POST['csrf_token'] ?? null;
        if (
            !is_string($token) ||
            empty($_SESSION['delivery_csrf']) ||
            !hash_equals((string) $_SESSION['delivery_csrf'], $token)
        ) {
            $this->reject('CSRF không hợp lệ. Tải lại trang.', 403);
        }
    }

    // [DEMO] Phải bật tường minh, chạy localhost VÀ đúng gateway GHN Staging.
    private function demoEnabled(): bool
    {
        $cfg = require __DIR__ . '/../Config/ghn.php';
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        return getenv('GHN_DEMO_MODE') === '1'
            && in_array($host, ['localhost:3000', '127.0.0.1:3000', 'localhost', '127.0.0.1'], true)
            && rtrim((string)($cfg['base_url'] ?? ''), '/') === 'https://dev-online-gateway.ghn.vn';
    }

    public function tracking(): void
    {
        $uid = $this->uid();
        if (!$uid) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $delivery = $this->delivery->getForParticipant($this->id(), $uid);
        if (!$delivery) {
            $this->reject('Không tìm thấy giao dịch hoặc không có quyền.', 403);
        }

        if (empty($_SESSION['delivery_csrf'])) {
            $_SESSION['delivery_csrf'] = bin2hex(random_bytes(32));
        }

        $demoEnabled = $this->demoEnabled()
            && ($delivery['delivery_method'] ?? '') === 'shipping'
            && !empty($delivery['tracking_number']);
        $demoState = $demoEnabled ? (new DeliveryDemoModel())->getState($this->id()) : null;

        $this->view('delivery/tracking', [
            'delivery' => $delivery,
            'userId' => $uid,
            'csrfToken' => $_SESSION['delivery_csrf'],
            'demoEnabled' => $demoEnabled,
            'demoState' => $demoState,
            'payment' => ($delivery['delivery_method'] ?? '') === 'shipping' ? (new PaymentModel())->summary($this->id()) : null,
            'ghnEvents' => ($delivery['delivery_method'] ?? '') === 'shipping'
                ? (new GhnEventModel())->history($this->id(), $uid) : [],
        ]);
    }

    public function propose(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if (!$uid || $id <= 0) {
            $this->reject('Không hợp lệ.', 403);
        }

        $method = (string) ($_POST['method'] ?? '');
        $note = trim((string) ($_POST['note'] ?? ''));
        if (!in_array($method, ['pickup', 'shipping'], true) || mb_strlen($note) > 2000) {
            $this->reject('Phương thức hoặc ghi chú không hợp lệ.', 422);
        }

      
        $data = [
            'method' => $method,
            'meeting_time' => null,
            'meeting_location' => null,
            'carrier' => null,
            'address' => null,
            'note' => $note,
            'fee' => 0,
            'item_id' => null,
            'from_district_id' => null,
            'from_ward_code' => null,
            'to_province_id' => null,
            'to_district_id' => null,
            'to_ward_code' => null,
            'package_weight' => null,
            'package_length' => null,
            'package_width' => null,
            'package_height' => null,
            'service_type_id' => null,
            'payment_type_id' => null,
        ];

        if ($method === 'pickup') {
            $meetingLocation = trim((string) ($_POST['meeting_location'] ?? ''));
            $raw = trim((string) ($_POST['meeting_time'] ?? ''));
            $dt = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $raw);
            if (
                !$dt || $dt->format('Y-m-d\TH:i') !== $raw ||
                $dt <= new \DateTimeImmutable() || $meetingLocation === '' ||
                mb_strlen($meetingLocation) > 255
            ) {
                $this->reject('Cần nhập địa điểm và thời gian hẹn hợp lệ trong tương lai.', 422);
            }

            $data['meeting_time'] = $dt->format('Y-m-d H:i:s');
            $data['meeting_location'] = $meetingLocation;
        } else {
            $address = trim((string) ($_POST['shipping_address'] ?? ''));
            $quote = $_SESSION['ghn_quotes'][$id] ?? null;

            $quoteKeys = [
                'created_at', 'fee', 'giver_id', 'item_id',
                'from_district_id', 'from_ward_code',
                'to_province_id', 'to_district_id', 'to_ward_code',
                'weight', 'length', 'width', 'height', 'service_type_id', 'address',
            ];
            if (!is_array($quote)) {
                $this->reject('Chưa có báo giá GHN. Vui lòng tính phí lại.', 422);
            }
            foreach ($quoteKeys as $field) {
                if (!array_key_exists($field, $quote)) {
                    $this->reject('Báo giá GHN thiếu dữ liệu. Vui lòng tính phí lại.', 422);
                }
            }

            $createdAt = (int) $quote['created_at'];
            if (
                $createdAt <= 0 || $createdAt > time() ||
                time() - $createdAt > 900 ||
                (int) $quote['giver_id'] !== $uid ||
                (int) $quote['item_id'] <= 0 ||
                (int) $quote['from_district_id'] <= 0 ||
                (string) $quote['from_ward_code'] === ''
            ) {
                $this->reject('Báo giá GHN chưa hợp lệ hoặc đã hết hạn. Vui lòng tính phí lại.', 422);
            }

            if (
                $address === '' || mb_strlen($address) > 500 ||
                !hash_equals((string) $quote['address'], $address)
            ) {
                $this->reject('Địa chỉ nhận đã thay đổi. Vui lòng tính phí lại.', 422);
            }

            foreach (['to_province_id', 'to_district_id', 'weight', 'length', 'width', 'height', 'service_type_id'] as $field) {
                if ((string) ($_POST[$field] ?? '') !== (string) $quote[$field]) {
                    $this->reject('Thông tin kiện hàng đã thay đổi. Vui lòng tính phí lại.', 422);
                }
            }
            if ((string) ($_POST['to_ward_code'] ?? '') !== (string) $quote['to_ward_code']) {
                $this->reject('Phường/xã nhận đã thay đổi. Vui lòng tính phí lại.', 422);
            }

            $fee = (int) $quote['fee'];
            if (
                $fee < 0 || $fee > 10000000 ||
                (int) $quote['to_province_id'] <= 0 ||
                (int) $quote['to_district_id'] <= 0 ||
                (string) $quote['to_ward_code'] === ''
            ) {
                $this->reject('Báo giá GHN không hợp lệ.', 422);
            }

            $data['carrier'] = 'GHN';
            $data['address'] = $address;
            $data['fee'] = $fee;
            $data['item_id'] = (int) $quote['item_id'];
            $data['from_district_id'] = (int) $quote['from_district_id'];
            $data['from_ward_code'] = (string) $quote['from_ward_code'];
            $data['to_province_id'] = (int) $quote['to_province_id'];
            $data['to_district_id'] = (int) $quote['to_district_id'];
            $data['to_ward_code'] = (string) $quote['to_ward_code'];
            $data['package_weight'] = (int) $quote['weight'];
            $data['package_length'] = (int) $quote['length'];
            $data['package_width'] = (int) $quote['width'];
            $data['package_height'] = (int) $quote['height'];
            $data['service_type_id'] = (int) $quote['service_type_id'];
            $payment = filter_var($_POST['payment_type_id'] ?? null, FILTER_VALIDATE_INT);
            if (!in_array($payment, [1, 2], true)) {
                $this->reject('Vui lòng chọn bên chịu phí GHN.', 422);
            }
            $data['payment_type_id'] = (int) $payment;
        }

        $ok = $this->delivery->propose($id, $uid, $data);

        if ($ok) {
            unset($_SESSION['ghn_quotes'][$id]);
        }

        $_SESSION['delivery_notice'] = $ok
            ? 'Đã gửi đề xuất. Chờ người nhận đồng ý.'
            : 'Không thể đề xuất ở trạng thái hiện tại.';
        $this->redirectToTracking($id);
    }

    public function agree(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if (!$uid || $id <= 0) {
            $this->reject('Không hợp lệ.', 403);
        }

        $ok = $this->delivery->agree($id, $uid);
        $_SESSION['delivery_notice'] = $ok
            ? 'Đã đồng ý phương thức giao nhận. Nếu chọn giao hàng, vui lòng chờ tạo vận đơn GHN.'
            : 'Không thể đồng ý. Kiểm tra lại trạng thái.';
        $this->redirectToTracking($id);
    }
    /** [GHN] Tạo vận đơn sau khi hai bên đồng ý. Chỉ Staging. */
    public function createShipment(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if ($uid <= 0 || $id <= 0) $this->reject('Không hợp lệ.', 403);

        if (!(new PaymentModel())->isPaid($id)) {
            $_SESSION['delivery_notice'] = 'Chưa có thanh toán VNPAY được xác nhận. Bên chịu phí cần hoàn tất thanh toán trước khi tạo vận đơn.';
            $this->redirectToTracking($id);
        }

        $config = require __DIR__ . '/../Config/ghn.php';
        if (rtrim((string)($config['base_url'] ?? ''), '/') !== 'https://dev-online-gateway.ghn.vn') {
            $this->reject('Chức năng demo chỉ được phép gọi GHN Staging.', 403);
        }
        if ((int)($config['shop_id'] ?? 0) <= 0) $this->reject('Chưa cấu hình GHN_SHOP_ID.', 422);

        $retry = (string)($_POST['retry_shipment'] ?? '') === '1';
        if ($retry) {
            if (($_POST['confirm_retry'] ?? '') !== '1' ||
                !$this->delivery->canReconcileShipment($id, $uid)) {
                $this->reject('Không đủ điều kiện thử tạo lại vận đơn.', 403);
            }
            // Không tạo lại khi GHN đã có đơn; không mở khóa nếu API tra cứu lỗi/timeout.
            try {
                $existing = (new GhnClient())->findOrderByClientCode('GNT-' . $id);
                $existingCode = trim((string)($existing['data']['order_code'] ?? ''));
                if ($existingCode !== '') {
                    if ($this->delivery->finishReviewedShipment($id, $uid, $existingCode)) {
                        $_SESSION['delivery_notice'] = 'Đã tìm thấy và khôi phục vận đơn GHN: ' . $existingCode;
                    } else {
                        $_SESSION['delivery_notice'] = 'Đã tìm thấy vận đơn nhưng không thể lưu. Vui lòng liên hệ quản trị.';
                    }
                    $this->redirectToTracking($id);
                }
                if (($existing['not_found'] ?? false) !== true) {
                    throw new RuntimeException('GHN chưa xác nhận vận đơn không tồn tại.');
                }
                error_log('GHN retry verified ORDER_NOT_FOUND request=' . $id);
            } catch (Throwable $e) {
                error_log('GHN retry verification request=' . $id . ': ' . $e->getMessage());
                $_SESSION['delivery_notice'] = 'Không thể xác nhận vận đơn cũ chưa tồn tại trên GHN. Chưa gửi yêu cầu tạo lại. Hãy thử đối soát sau.';
                $this->redirectToTracking($id);
            }
        }

        // Kiểm tra cấu hình shop/kho trước khi claim: lỗi cấu hình không được chuyển sang needs_review.
        // GHN có thể vẫn yêu cầu thông tin kho mặc định dù payload ghi đè địa chỉ người gửi.
        try {
            $shopResult = (new GhnClient())->getShops();
            $shopList = $shopResult['data']['shops'] ?? null;
            if (!is_array($shopList)) {
                throw new RuntimeException('GHN không trả về danh sách shop hợp lệ.');
            }
            $configuredShop = null;
            foreach ($shopList as $shop) {
                if (is_array($shop) && (int)($shop['_id'] ?? 0) === (int)$config['shop_id']) {
                    $configuredShop = $shop;
                    break;
                }
            }
            if ($configuredShop === null) {
                throw new RuntimeException('GHN_SHOP_ID không thuộc danh sách shop của GHN_TOKEN.');
            }
            if ((int)($configuredShop['status'] ?? 0) !== 1) {
                throw new RuntimeException('Shop GHN chưa hoạt động. Kiểm tra trên GHN Developer.');
            }
            // Cho phép thử địa chỉ gửi/trả hàng riêng trên từng vận đơn.
            // Chỉ kiểm tra ShopId/Token và trạng thái shop ở preflight; việc thiếu
            // district_id/ward_code của shop KHÔNG tự động phủ định payload ghi đè.
            // GHN vẫn có thể từ chối tạo đơn nếu backend cần kho mặc định.
            $hasLegacyAddress = (int)($configuredShop['district_id'] ?? 0) > 0
                && trim((string)($configuredShop['ward_code'] ?? '')) !== '';
            if (!$hasLegacyAddress) {
                error_log('GHN shop preflight request=' . $id .
                    ': shop legacy pickup address missing; attempting explicit from/return overrides');
            }
        } catch (Throwable $e) {
            error_log('GHN shop preflight request=' . $id . ': ' . $e->getMessage());
            $_SESSION['delivery_notice'] = 'Chưa thể tạo vận đơn: ' . $e->getMessage();
            $this->redirectToTracking($id);
        }

        // Claim trong DB để tránh hai request đồng thời tạo trùng.
        $row = $this->delivery->claimShipment($id, $uid, $retry);
        if (!$row) {
            $_SESSION['delivery_notice'] = 'Không thể tạo vận đơn: trạng thái không phù hợp, thông tin GHN chưa đủ hoặc yêu cầu khác đang xử lý.';
            $this->redirectToTracking($id);
        }

        try {
            foreach (['from_phone', 'to_phone'] as $phoneKey) {
                if (!preg_match('/^0[0-9]{9,10}$/D', preg_replace('/\s+/', '', (string)$row[$phoneKey]))) {
                    throw new RuntimeException('Số điện thoại người gửi/nhận không hợp lệ.');
                }
            }
            $ghn = new GhnClient();
            $provinces = $ghn->getProvinces()['data'] ?? [];
            $provinceName = static function (array $provinces, int $id): string {
                foreach ($provinces as $p) {
                    if ((int)($p['ProvinceID'] ?? 0) === $id) return (string)$p['ProvinceName'];
                }
                throw new RuntimeException('Mã tỉnh/thành GHN không còn hợp lệ.');
            };
            $lookup = static function (array $items, string $idKey, string $nameKey, string $id): string {
                foreach ($items as $item) {
                    if ((string)($item[$idKey] ?? '') === $id) return (string)$item[$nameKey];
                }
                throw new RuntimeException('Mã huyện/xã GHN không khớp với địa chỉ đã lưu.');
            };
            $fromProvince = $provinceName($provinces, (int)$row['ghn_province_id']);
            $toProvince = $provinceName($provinces, (int)$row['to_province_id']);
            $fromDistrict = $lookup(
                $ghn->getDistricts((int)$row['ghn_province_id'])['data'] ?? [],
                'DistrictID', 'DistrictName', (string)$row['ghn_district_id']
            );
            $toDistrict = $lookup(
                $ghn->getDistricts((int)$row['to_province_id'])['data'] ?? [],
                'DistrictID', 'DistrictName', (string)$row['to_district_id']
            );
            $fromWard = $lookup(
                $ghn->getWards((int)$row['ghn_district_id'])['data'] ?? [],
                'WardCode', 'WardName', (string)$row['ghn_ward_code']
            );
            $toWard = $lookup(
                $ghn->getWards((int)$row['to_district_id'])['data'] ?? [],
                'WardCode', 'WardName', (string)$row['to_ward_code']
            );
            if (trim((string)$row['sender_address_detail']) === '' || trim((string)$row['shipping_address']) === '') {
                throw new RuntimeException('Thiếu địa chỉ gửi hoặc nhận chi tiết.');
            }
            // Mã đơn nội bộ cố định để GHN Staging tránh tạo đơn trùng khi retry.
            $clientCode = 'GNT-' . $id;
            $payload = [
                'client_order_code' => $clientCode,
                'payment_type_id' => 1, // GHN thu cước từ shop/người gửi; VNPAY đã thu từ bên chịu phí trên website.
                'required_note' => 'CHOXEMHANGKHONGTHU',
                'from_name' => (string)$row['from_name'],
                'from_phone' => preg_replace('/\s+/', '', (string)$row['from_phone']),
                'from_address' => implode(', ', [(string)$row['sender_address_detail'], $fromWard, $fromDistrict, $fromProvince]),
                'from_ward_name' => $fromWard,
                'from_district_name' => $fromDistrict,
                'from_province_name' => $fromProvince,
                // Dùng cùng địa chỉ với người tặng, không phụ thuộc địa chỉ trả mặc định của shop.
                'return_name' => (string)$row['from_name'],
                'return_phone' => preg_replace('/\s+/', '', (string)$row['from_phone']),
                'return_address' => implode(', ', [(string)$row['sender_address_detail'], $fromWard, $fromDistrict, $fromProvince]),
                'return_ward_name' => $fromWard,
                'return_district_name' => $fromDistrict,
                'return_province_name' => $fromProvince,
                'is_new_from_address' => false,
                'is_new_return_address' => false,
                'is_new_to_address' => false,
                'to_name' => (string)$row['to_name'],
                'to_phone' => preg_replace('/\s+/', '', (string)$row['to_phone']),
                'to_address' => implode(', ', [(string)$row['shipping_address'], $toWard, $toDistrict, $toProvince]),
                'to_ward_name' => $toWard,
                'to_district_name' => $toDistrict,
                'to_province_name' => $toProvince,
                'weight' => (int)$row['package_weight'],
                'length' => (int)$row['package_length'],
                'width' => (int)$row['package_width'],
                'height' => (int)$row['package_height'],
                'service_type_id' => (int)$row['service_type_id'],
                'content' => mb_substr((string)$row['item_title'], 0, 500),
                'insurance_value' => 0,
                'cod_amount' => 0,
            ];
            if ($payload['service_type_id'] === 5) {
                $payload['items'] = [[
                    'name' => mb_substr((string)$row['item_title'], 0, 500),
                    'quantity' => 1,
                    'weight' => $payload['weight'],
                    'length' => $payload['length'],
                    'width' => $payload['width'],
                    'height' => $payload['height'],
                ]];
            }
            // Ghi log mã và tên địa giới GHN để chẩn đoán lỗi tra cứu phường/xã.
            // Không ghi tên người dùng, số điện thoại, địa chỉ chi tiết hay GHN_TOKEN.
            error_log('GHN ADDRESS DEBUG request=' . $id . ' ' . json_encode([
                'address_mode' => [
                    'from' => $payload['is_new_from_address'],
                    'to' => $payload['is_new_to_address'],
                    'return' => $payload['is_new_return_address'],
                ],
                'from' => [
                    'province_id' => $row['ghn_province_id'],
                    'district_id' => $row['ghn_district_id'],
                    'ward_code' => $row['ghn_ward_code'],
                    'province_name' => $fromProvince,
                    'district_name' => $fromDistrict,
                    'ward_name' => $fromWard,
                ],
                'to' => [
                    'province_id' => $row['to_province_id'],
                    'district_id' => $row['to_district_id'],
                    'ward_code' => $row['to_ward_code'],
                    'province_name' => $toProvince,
                    'district_name' => $toDistrict,
                    'ward_name' => $toWard,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $result = $ghn->createOrder($payload);
            $orderCode = trim((string)($result['data']['order_code'] ?? ''));
            if ($orderCode === '') throw new RuntimeException('GHN không trả mã vận đơn.');

            if (!$this->delivery->finishShipment($id, $uid, $orderCode)) {
                throw new RuntimeException('GHN đã tạo đơn nhưng lưu mã vào CSDL thất bại. Liên hệ quản trị để đối soát mã ' . $orderCode);
            }
            $_SESSION['delivery_notice'] = 'Tạo vận đơn GHN Staging thành công: ' . $orderCode;
        } catch (Throwable $e) {
            $this->delivery->shipmentNeedsReview($id, $uid);
            error_log('GHN createShipment request=' . $id . ': ' . $e->getMessage());
            $_SESSION['delivery_notice'] = 'Tạo vận đơn chưa xác nhận thành công. Vui lòng kiểm tra GHN Developer và đối soát theo mã GNT-' . $id . ' trước khi thử lại.';
        }
        $this->redirectToTracking($id);
    }

    public function reconcileShipment(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if ($uid <= 0 || $id <= 0) $this->reject('Không hợp lệ.', 403);
        $config = require __DIR__ . '/../Config/ghn.php';
        if (rtrim((string)($config['base_url'] ?? ''), '/') !== 'https://dev-online-gateway.ghn.vn') {
            $this->reject('Chỉ hỗ trợ GHN Staging.', 403);
        }
        if (!$this->delivery->canReconcileShipment($id, $uid)) {
            $this->reject('Không có quyền đối soát vận đơn này.', 403);
        }
        try {
            $result = (new GhnClient())->getOrderByClientCode('GNT-' . $id);
            $orderCode = trim((string)($result['data']['order_code'] ?? ''));
            if ($orderCode === '') throw new RuntimeException('Chưa tìm thấy mã vận đơn từ GHN.');
            if (!$this->delivery->finishReviewedShipment($id, $uid, $orderCode)) {
                throw new RuntimeException('Lỗi ghi mã vận đơn vào DB.');
            }
            $_SESSION['delivery_notice'] = 'Đã đối soát GHN Staging thành công: ' . $orderCode;
        } catch (Throwable $e) {
            error_log('GHN reconcileShipment request=' . $id . ': ' . $e->getMessage());
            $_SESSION['delivery_notice'] = 'Chưa đối soát được vận đơn. KHÔNG tạo lại; kiểm tra GHN Developer với mã GNT-' . $id . ' và liên hệ quản trị.';
        }
        $this->redirectToTracking($id);
    }


    public function refreshShipmentStatus(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if ($uid <= 0 || $id <= 0) {
            $this->reject('Yêu cầu không hợp lệ.', 403);
        }

        // Chỉ truy vấn môi trường thử nghiệm đã sử dụng khi tạo vận đơn.
        $config = require __DIR__ . '/../Config/ghn.php';
        if (rtrim((string) ($config['base_url'] ?? ''), '/') !==
            'https://dev-online-gateway.ghn.vn') {
            $this->reject('Chỉ hỗ trợ GHN Staging.', 403);
        }

        // Chỉ người tặng hoặc người nhận của giao dịch mới được xem/cập nhật.
        $delivery = $this->delivery->getForParticipant($id, $uid);
        if (!$delivery || ($delivery['delivery_method'] ?? '') !== 'shipping' ||
            empty($delivery['tracking_number'])) {
            $this->reject('Không tìm thấy vận đơn hoặc không có quyền.', 403);
        }

        $code = trim((string) $delivery['tracking_number']);
        try {
            $detail = (new GhnClient())->getOrderDetail($code);
            $data = $detail['data'] ?? null;
            if (!is_array($data) ||
                !hash_equals($code, (string) ($data['order_code'] ?? ''))) {
                throw new RuntimeException('Mã vận đơn GHN không khớp.');
            }

            $ghnStatus = trim((string) ($data['status'] ?? ''));
            if ($ghnStatus === '' || strlen($ghnStatus) > 100 ||
                !preg_match('/^[a-z0-9_-]+$/D', $ghnStatus)) {
                throw new RuntimeException('GHN trả trạng thái không hợp lệ.');
            }

            if (!(new GhnEventModel())->acceptPolling($id, $code, $ghnStatus)) {
                throw new RuntimeException('Không lưu được trạng thái GHN.');
            }
            $_SESSION['delivery_notice'] =
                'Đã cập nhật trạng thái vận đơn GHN: ' . $ghnStatus;
        } catch (Throwable $e) {
            error_log('GHN refreshShipmentStatus request=' . $id . ': ' . $e->getMessage());
            $_SESSION['delivery_notice'] =
                'Chưa cập nhật được trạng thái từ GHN. Vui lòng thử lại sau.';
        }
        $this->redirectToTracking($id);
    }


    public function confirmHandover(): void
    {
        $this->verifyPost();
        $id = $this->id();
        $uid = $this->uid();
        if ($uid <= 0 || $id <= 0) {
            $this->reject('Yêu cầu không hợp lệ.', 403);
        }
        if (($_POST['handover_attested'] ?? '') !== '1') {
            $this->reject('Bạn cần xác nhận đã thực sự giao/nhận hàng.', 422);
        }
        $result = $this->delivery->confirmHandover($id, $uid);
        $messages = [
            'awaiting_confirmation' => 'Đã ghi nhận xác nhận của bạn. Đang chờ bên còn lại xác nhận.',
            'completed' => 'Hai bên đã xác nhận. Trao tặng hoàn tất!',
            'already_confirmed' => 'Bạn đã xác nhận trước đó.',
            'not_delivered' => 'Chưa có mã vận đơn GHN.',
            'invalid_state' => 'Yêu cầu chưa đủ điều kiện hoàn tất.',
            'forbidden' => 'Bạn không có quyền xác nhận giao dịch này.',
            'error' => 'Không thể lưu xác nhận. Vui lòng thử lại sau.',
        ];
        $_SESSION['delivery_notice'] = $messages[$result] ?? 'Giao dịch đã hoàn tất.';
        $this->redirectToTracking($id);
    }

    /** [DEMO] Cập nhật tiến độ giao hàng giả lập, KHÔNG gọi GHN. */
    public function demoAdvance(): void
    {
        $this->verifyPost();
        if (!$this->demoEnabled()) $this->reject('Chế độ demo chưa được bật.', 403);
        $id = $this->id(); $uid = $this->uid();
        if ($id <= 0 || $uid <= 0) $this->reject('Không hợp lệ.', 403);
        $result = (new DeliveryDemoModel())->advance($id, $uid);
        $labels = [
            'ready_to_pick' => 'DEMO: Đơn đang chờ lấy hàng.',
            'picking' => 'DEMO: Đang lấy hàng.',
            'delivering' => 'DEMO: Đang giao hàng.',
            'delivered' => 'DEMO: Đã giao hàng (mô phỏng, KHÔNG phải GHN thực).',
            'finished' => 'DEMO đã ở bước cuối.',
            'forbidden' => 'Bạn không có quyền điều khiển tiến độ demo.',
            'error' => 'Không cập nhật được tiến độ demo. Kiểm tra bảng delivery_demo_progress.'
        ];
        $_SESSION['delivery_notice'] = $labels[$result] ?? 'Không cập nhật được demo.';
        $this->redirectToTracking($id);
    }

    public function demoConfirm(): void
    {
        $this->verifyPost();
        if (!$this->demoEnabled()) $this->reject('Chế độ demo chưa được bật.', 403);
        $id = $this->id(); $uid = $this->uid();
        if ($id <= 0 || $uid <= 0) $this->reject('Không hợp lệ.', 403);
        $result = (new DeliveryDemoModel())->confirm($id, $uid);
        $labels = [
            'awaiting_confirmation' => 'DEMO: Đã xác nhận, chờ bên còn lại.',
            'completed' => 'DEMO hoàn tất! Giao dịch thật vẫn giữ nguyên.',
            'already_confirmed' => 'Bạn đã xác nhận trong demo.',
            'not_delivered' => 'DEMO chưa chuyển đến trạng thái đã giao.',
            'forbidden' => 'Bạn không có quyền xác nhận demo.',
            'error' => 'Không thể ghi nhận xác nhận demo.',
        ];
        $_SESSION['delivery_notice'] = $labels[$result] ?? 'DEMO đã hoàn tất.';
        $this->redirectToTracking($id);
    }

}
