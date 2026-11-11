<?php

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'reuse_sharing_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Website URL
define(
    'BASE_URL',
    rtrim(
        getenv('BASE_URL') ?: 'http://26.225.165.255/doan/',
        '/'
    ) . '/'
);