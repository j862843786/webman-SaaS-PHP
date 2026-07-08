<?php

return [
    // Resolve tenants by host/domain when enabled; otherwise use header/query/default tenant code.
    'domain_enabled' => filter_var(env('TENANT_DOMAIN_ENABLED', true), FILTER_VALIDATE_BOOL),
    'header' => env('TENANT_HEADER', 'X-Tenant-Code'),
    'default_code' => env('TENANT_DEFAULT_CODE', 'default'),

    // Tables that are shared by all tenants and never receive tenant suffixes or tenant_id filters.
    'public_tables' => array_filter(array_map('trim', explode(',', env(
        'TENANT_PUBLIC_TABLES',
        'tenants,tenant_domains,system_settings,plugins,applications,tenant_plugin_settings,tenant_applications'
    )))),

    // Physical table sharding toggle. When enabled, tenant tables are suffixed with _{bucket}.
    'sharding' => [
        'enabled' => filter_var(env('TENANT_SHARDING_ENABLED', false), FILTER_VALIDATE_BOOL),
        'modulo' => max(1, (int) env('TENANT_SHARD_MODULO', 16)),
    ],
];
