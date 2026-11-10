<?php

header('Content-Type: text/plain; charset=utf-8');

echo "SERVER_SOFTWARE: "
    . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";

echo "SERVER_PORT: "
    . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n";

echo "GHN_TOKEN: "
    . (getenv('GHN_TOKEN') ? 'Đã cấu hình' : 'Chưa cấu hình')
    . "\n";

echo "GHN_SHOP_ID: "
    . (getenv('GHN_SHOP_ID') ?: 'Chưa cấu hình')
    . "\n";