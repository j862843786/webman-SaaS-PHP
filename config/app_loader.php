<?php

return [
    'base_path' => base_path('app/application'),
    'applications' => [
        'admin' => [
            'name' => '管理后台',
            'path' => 'admin',
            'enabled' => true,
        ],
        'api' => [
            'name' => '开放接口',
            'path' => 'api',
            'enabled' => true,
        ],
    ],
];
