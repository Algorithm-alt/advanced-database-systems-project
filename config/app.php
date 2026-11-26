<?php

declare(strict_types=1);

$environment = getenv('APP_ENV') ?: 'local';

return [
    'name' => 'Algorithm Hardware Plug',
    'env' => $environment,
    'debug' => filter_var(
        getenv('APP_DEBUG') !== false
            ? getenv('APP_DEBUG')
            : ($environment === 'local' ? 'true' : 'false'),
        FILTER_VALIDATE_BOOLEAN
    ),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Lagos',
    'base_url' => getenv('APP_BASE_URL') ?: '/RetailManagementSystem/public',
    'session_name' => 'AHPLSESS_060',
    'session_lifetime' => 7200,
    'login_max_attempts' => 5,
    'approval_phrase' => getenv('APPROVAL_PHRASE') ?: ($environment === 'local' ? 'ALP-APPROVE-060' : ''),
];
