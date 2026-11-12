<?php

return [
    'base_url' => 'https://dev-online-gateway.ghn.vn',
    'token' => getenv('GHN_TOKEN') ?: '',
    'shop_id' => (int)(getenv('GHN_SHOP_ID') ?: 0),
    'timeout'  => 15,
    'from_district_id' => (int)(
    getenv('GHN_FROM_DISTRICT_ID') ?: 0
),
];