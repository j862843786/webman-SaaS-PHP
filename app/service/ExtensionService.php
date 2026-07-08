<?php

namespace app\service;

use InvalidArgumentException;
use support\Db;

final class ExtensionService
{
    public function applications(): array
    {
        return array_map('get_object_vars', Db::table('applications')->orderBy('code')->get()->all());
    }

    public function plugins(): array
    {
        return array_map('get_object_vars', Db::table('plugins')->orderBy('code')->get()->all());
    }

    public function saveApplication(array $payload): void
    {
        $this->assertRequired($payload, ['code', 'name', 'entry_path']);
        Db::table('applications')->updateOrInsert(
            ['code' => trim((string) $payload['code'])],
            [
                'name' => trim((string) $payload['name']),
                'entry_path' => trim((string) $payload['entry_path']),
                'enabled' => (int) ($payload['enabled'] ?? 1),
            ]
        );
    }

    public function savePlugin(array $payload): void
    {
        $this->assertRequired($payload, ['code', 'name']);
        Db::table('plugins')->updateOrInsert(
            ['code' => trim((string) $payload['code'])],
            [
                'name' => trim((string) $payload['name']),
                'version' => (string) ($payload['version'] ?? '0.1.0'),
                'enabled' => (int) ($payload['enabled'] ?? 1),
                'config' => isset($payload['config']) ? json_encode($payload['config'], JSON_UNESCAPED_UNICODE) : null,
            ]
        );
    }

    public function enableTenantApplication(int $tenantId, string $code, array $config = []): void
    {
        Db::table('tenant_applications')->updateOrInsert(
            ['tenant_id' => $tenantId, 'application_code' => $code],
            ['enabled' => 1, 'config' => json_encode($config, JSON_UNESCAPED_UNICODE)]
        );
    }

    public function enableTenantPlugin(int $tenantId, string $code, array $config = []): void
    {
        Db::table('tenant_plugin_settings')->updateOrInsert(
            ['tenant_id' => $tenantId, 'plugin_code' => $code],
            ['enabled' => 1, 'config' => json_encode($config, JSON_UNESCAPED_UNICODE)]
        );
    }

    private function assertRequired(array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($payload[$field]) || trim((string) $payload[$field]) === '') {
                throw new InvalidArgumentException(sprintf('%s is required', $field));
            }
        }
    }
}
