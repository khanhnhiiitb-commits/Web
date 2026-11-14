<?php
return [
    'tmn_code' => trim((string)(getenv('VNPAY_TMN_CODE') ?: '')),
    'hash_secret' => trim((string)(getenv('VNPAY_HASH_SECRET') ?: '')),
    'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'site_url' => rtrim((string)(getenv('APP_PUBLIC_URL') ?: ''), '/'),
    'timezone' => 'Asia/Ho_Chi_Minh',
];
