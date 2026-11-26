<?php

declare(strict_types=1);

return [
    'driver' => 'sqlsrv',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'RetailManagementDB',
    'username' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASS') ?: '',
    'trust_certificate' => filter_var(getenv('DB_TRUST_CERT') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'encrypt' => filter_var(getenv('DB_ENCRYPT') ?: 'true', FILTER_VALIDATE_BOOLEAN),
];
