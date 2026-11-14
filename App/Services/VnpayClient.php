<?php
namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class VnpayClient
{
    private array $cfg;

    public function __construct()
    {
        $this->cfg = require __DIR__ . '/../Config/vnpay.php';
        foreach (['tmn_code', 'hash_secret', 'site_url'] as $key) {
            if ($this->cfg[$key] === '') {
                throw new RuntimeException('Thiếu cấu hình VNPAY: ' . $key);
            }
        }
        if (!filter_var($this->cfg['site_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('APP_PUBLIC_URL không hợp lệ.');
        }
    }

    private static function encoded(array $data): string
    {
        ksort($data, SORT_STRING);
        return http_build_query($data, '', '&', PHP_QUERY_RFC1738);
    }

    public function paymentUrl(string $ref, int $amountVnd, string $ip): string
    {
        if ($amountVnd < 5000 || $amountVnd > 10000000 || !preg_match('/^[A-Za-z0-9]+$/D', $ref)) {
            throw new RuntimeException('Số tiền hoặc mã thanh toán VNPAY không hợp lệ.');
        }
        $now = new DateTimeImmutable('now', new DateTimeZone($this->cfg['timezone']));
        $return = $this->cfg['site_url'] . '/index.php?controller=Payment&action=returnUrl';
        $data = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->cfg['tmn_code'],
            'vnp_Amount' => $amountVnd * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $ref,
            'vnp_OrderInfo' => 'Thanh toan cuoc van chuyen GiveTake ' . $ref,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $return,
            'vnp_IpAddr' => filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1',
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_ExpireDate' => $now->modify('+15 minutes')->format('YmdHis'),
        ];
        $encoded = self::encoded($data);
        $hash = hash_hmac('sha512', $encoded, $this->cfg['hash_secret']);
        return $this->cfg['payment_url'] . '?' . $encoded . '&vnp_SecureHash=' . $hash;
    }

    public function verify(array $query): bool
    {
        $signature = $query['vnp_SecureHash'] ?? null;
        if (!is_string($signature) || !preg_match('/^[a-f0-9]{128}$/iD', $signature)) return false;
        $data = [];
        foreach ($query as $key => $value) {
            if (str_starts_with((string)$key, 'vnp_') && !in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true)) {
                if (!is_string($value)) return false;
                $data[$key] = $value;
            }
        }
        return hash_equals(hash_hmac('sha512', self::encoded($data), $this->cfg['hash_secret']), strtolower($signature))
            && ($data['vnp_TmnCode'] ?? '') === $this->cfg['tmn_code'];
    }
}
