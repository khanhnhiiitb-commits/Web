<?php

namespace App\Services;

use RuntimeException;

class GhnClient
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../Config/ghn.php';
    }

    private function request(string $method, string $endpoint, array $data = [], bool $allowNotFound = false): array
    {
        $token = trim((string)($this->config['token'] ?? ''));
        if ($token === '') throw new RuntimeException('Chưa cấu hình GHN_TOKEN.');
        if (!extension_loaded('curl')) throw new RuntimeException('PHP chưa bật cURL.');

        $url = rtrim((string)$this->config['base_url'], '/') . '/' . ltrim($endpoint, '/');
        $method = strtoupper($method);
        $headers = ['Token: ' . $token, 'Content-Type: application/json'];
        if ((int)($this->config['shop_id'] ?? 0) > 0) {
            $headers[] = 'ShopId: ' . (int)$this->config['shop_id'];
        }
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => (int)($this->config['timeout'] ?? 15),
        ];
        if ($method !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Không kết nối được GHN: ' . $error);
        }
        curl_close($ch);
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) throw new RuntimeException('GHN trả về dữ liệu không hợp lệ.');
        if ($httpCode < 200 || $httpCode >= 300 ||
            (isset($decoded['code']) && (int)$decoded['code'] !== 200)) {
            if ($allowNotFound && $httpCode === 400 &&
                ($decoded['code_message'] ?? '') === 'ORDER_NOT_FOUND') {
                return ['data' => null, 'not_found' => true];
            }
            $message = (string)($decoded['message'] ?? 'Unknown error');
            error_log('GHN API HTTP ' . $httpCode . ': ' . $message);
            throw new RuntimeException('GHN API thất bại (HTTP ' . $httpCode . ').');
        }
        return $decoded;
    }

    public function getShops(): array
    {
        return $this->request('GET', '/shiip/public-api/v2/shop/all');
    }

    public function getProvinces(): array
    {
        return $this->request('GET', '/shiip/public-api/master-data/province');
    }

    public function getDistricts(int $provinceId): array
    {
        return $this->request('POST', '/shiip/public-api/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    public function getWards(int $districtId): array
    {
        return $this->request('POST', '/shiip/public-api/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    public function calculateFee(array $payload): array
    {
        return $this->request('POST', '/shiip/public-api/v2/shipping-order/fee', $payload);
    }

    public function createOrder(array $payload): array
    {
        
        return $this->request('POST', '/shiip/public-api/v2/shipping-order/create', $payload);
    }

    public function getOrderDetail(string $orderCode): array
    {
        return $this->request('GET', '/shiip/public-api/v2/shipping-order/detail?order_code=' . rawurlencode($orderCode));
    }

    /** Chỉ coi là không có đơn khi GHN xác nhận ORDER_NOT_FOUND.
     *  Timeout hoặc các lỗi khác không được phép mở khóa tạo lại.
     */
    public function findOrderByClientCode(string $clientCode): array
    {
        return $this->request('GET',
            '/shiip/public-api/v2/shipping-order/detail-by-client-code?client_order_code=' . rawurlencode($clientCode),
            [], true);
    }

    public function getOrderByClientCode(string $clientCode): array
    {
        return $this->request('GET', '/shiip/public-api/v2/shipping-order/detail-by-client-code?client_order_code=' . rawurlencode($clientCode));
    }
}
