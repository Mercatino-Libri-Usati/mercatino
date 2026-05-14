<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:4173', 'http://localhost:8000', 'http://localhost:8080', 'http://127.0.0.1:8080', 'https://nuovo.libriusaticrema.it', 'http://nuovo.libriusaticrema.it'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
