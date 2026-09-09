<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Solo el origen del frontend — NUNCA usar '*' con credentials
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5174',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 h — el browser cachea el preflight

    // false para token Bearer (true solo para cookies de sesión)
    'supports_credentials' => false,
];
