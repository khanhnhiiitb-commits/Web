<?php
namespace App\Controllers;

use App\Core\Database;
use App\Services\GhnClient;
use PDO;
use Throwable;

class ShipmentController
{
    private function respond(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function locations(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            $this->respond(405, ['success' => false, 'message' => 'Chỉ hỗ trợ GET.']);
            return;
        }
        $uid = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($uid <= 0) {
            $this->respond(401, ['success' => false, 'message' => 'Vui lòng đăng nhập.']);
            return;
        }
        try {
            $ghn = new GhnClient();
            switch ((string)($_GET['type'] ?? '')) {
                case 'provinces':
                    $result = $ghn->getProvinces();
                    break;
                case 'districts':
                    $province = filter_var($_GET['province_id'] ?? null, FILTER_VALIDATE_INT);
                    if (!$province || $province <= 0) {
                        $this->respond(422, ['success' => false, 'message' => 'Mã tỉnh/thành không hợp lệ.']);
                        return;
                    }
                    $result = $ghn->getDistricts((int)$province);
                    break;
                case 'wards':
                    $district = filter_var($_GET['district_id'] ?? null, FILTER_VALIDATE_INT);
                    if (!$district || $district <= 0) {
                        $this->respond(422, ['success' => false, 'message' => 'Mã huyện không hợp lệ.']);
                        return;
                    }
                    $result = $ghn->getWards((int)$district);
                    break;
                default:
                    $this->respond(422, ['success' => false, 'message' => 'Danh mục không hợp lệ.']);
                    return;
            }
            if (!isset($result['data']) || !is_array($result['data'])) {
                throw new \RuntimeException('GHN trả danh sách địa chỉ không hợp lệ.');
            }
            $this->respond(200, ['success' => true, 'data' => $result['data']]);
        } catch (Throwable $e) {
            error_log('GHN locations: ' . $e->getMessage());
            $this->respond(502, ['success' => false, 'message' => 'Không thể tải địa chỉ GHN.']);
        }
    }

    /** [GHN] Tính phí theo địa chỉ gửi từng món đồ. */
    public function calculateFee(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->respond(405, ['success' => false, 'message' => 'Chỉ hỗ trợ POST.']);
            return;
        }
        $uid = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($uid <= 0) {
            $this->respond(401, ['success' => false, 'message' => 'Vui lòng đăng nhập.']);
            return;
        }
        $csrf = $_POST['csrf_token'] ?? null;
        if (!is_string($csrf) || empty($_SESSION['delivery_csrf']) ||
            !hash_equals((string)$_SESSION['delivery_csrf'], $csrf)) {
            $this->respond(403, ['success' => false, 'message' => 'CSRF không hợp lệ. Tải lại trang.']);
            return;
        }
        $int = static function(string $key): int {
            $n = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
            return $n === false || $n === null ? 0 : (int)$n;
        };
        $requestId = $int('request_id');
        $weight = $int('weight');
        $length = $int('length');
        $width = $int('width');
        $height = $int('height');
        $toProvinceId = $int('to_province_id');
        $toDistrictId = $int('to_district_id');
        $serviceTypeId = $int('service_type_id');
        $toWardCode = trim((string)($_POST['to_ward_code'] ?? ''));
        $address = trim((string)($_POST['shipping_address'] ?? ''));
        if ($requestId <= 0 || $toProvinceId <= 0 || $toDistrictId <= 0 ||
            $weight < 1 || $weight > 50000 || $length < 1 || $length > 200 ||
            $width < 1 || $width > 200 || $height < 1 || $height > 200 ||
            !in_array($serviceTypeId, [2,5], true) ||
            !preg_match('/^[0-9A-Za-z_-]{1,30}$/D', $toWardCode) ||
            $address === '' || mb_strlen($address) > 500) {
            $this->respond(422, ['success' => false, 'message' => 'Thông tin địa chỉ hoặc kiện hàng không hợp lệ.']);
            return;
        }
        unset($_SESSION['ghn_quotes'][$requestId]);
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT ir.id, i.id AS item_id, i.ghn_district_id, i.ghn_ward_code
                 FROM item_requests ir JOIN items i ON i.id = ir.item_id
                 LEFT JOIN delivery_details d ON d.request_id = ir.id
                 WHERE ir.id = :id AND ir.status IN ('accepted','arranged')
                   AND i.status = 'reserved' AND i.user_id = :uid
                   AND d.tracking_number IS NULL AND d.ghn_status IS NULL LIMIT 1"
            );
            $stmt->execute([':id' => $requestId, ':uid' => $uid]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $this->respond(403, ['success' => false, 'message' => 'Không có quyền hoặc yêu cầu đã có vận đơn.']);
                return;
            }
            $fromDistrictId = (int)($item['ghn_district_id'] ?? 0);
            $fromWardCode = trim((string)($item['ghn_ward_code'] ?? ''));
            if ($fromDistrictId <= 0 || $fromWardCode === '') {
                $this->respond(422, ['success' => false, 'message' => 'Món đồ chưa có địa chỉ gửi GHN.']);
                return;
            }
            $ghn = new GhnClient();
            $districts = $ghn->getDistricts($toProvinceId)['data'] ?? [];
            $districtValid = false;
            foreach ($districts as $d) {
                if ((int)($d['DistrictID'] ?? 0) === $toDistrictId) { $districtValid = true; break; }
            }
            if (!$districtValid) {
                $this->respond(422, ['success'=>false, 'message'=>'Huyện nhận không thuộc tỉnh đã chọn.']);
                return;
            }
            $payload = [
                'from_district_id' => $fromDistrictId,
                'from_ward_code' => $fromWardCode,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'service_type_id' => $serviceTypeId,
                'weight' => $weight, 'length' => $length,
                'width' => $width, 'height' => $height,
                'insurance_value' => 0,
            ];
            $result = $ghn->calculateFee($payload);
            $fee = $result['data']['total'] ?? null;
            if (!is_numeric($fee) || (float)$fee < 0 || (float)$fee > 10000000 ||
                (float)$fee != (int)$fee) {
                throw new \RuntimeException('GHN không trả về phí hợp lệ.');
            }
            $fee = (int)$fee;
            $_SESSION['ghn_quotes'][$requestId] = [
                'fee' => $fee, 'giver_id' => $uid,
                'item_id' => (int)$item['item_id'], 'address' => $address,
                'from_district_id' => $fromDistrictId,
                'from_ward_code' => $fromWardCode,
                'to_province_id' => $toProvinceId,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'weight' => $weight, 'length' => $length,
                'width' => $width, 'height' => $height,
                'service_type_id' => $serviceTypeId,
                'created_at' => time(),
            ];
            $this->respond(200, ['success'=>true, 'fee'=>$fee,
                'formatted_fee'=>number_format($fee,0,',','.') . 'đ']);
        } catch (Throwable $e) {
            error_log('GHN calculateFee: ' . $e->getMessage());
            $this->respond(502, ['success'=>false,'message'=>'Không thể tính phí GHN. Vui lòng kiểm tra lại dữ liệu.']);
        }
    }
}
