<?php

return [
    '' => [
        app\middleware\TenantMiddleware::class,
    ],
    'admin' => [
        app\middleware\AdminTokenMiddleware::class,
    ],
];
