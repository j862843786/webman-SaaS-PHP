<?php

namespace app\service;

use InvalidArgumentException;
use support\Db;

final class TenantService
{
    public function list(): array
    {
        return array_map('get_object_vars', Db::table('tenants')->orderByDesc('id')->get()->all());
    }

    public function find(int $id): ?array
    {
        $tenant = Db::table('tenants')->where('id', $id)->first();

        return $tenant ? (array) $tenant : null;
    }

    public function create(array $payload): int
    {
        $this->assertRequired($payload, ['code', 'name']);

        return (int) Db::table('tenants')->insertGetId([
            'code' => trim((string) $payload['code']),
            'name' => trim((string) $payload['name']),
            'enabled' => (int) ($payload['enabled'] ?? 1),
            'plan_code' => $payload['plan_code'] ?? null,
        ]);
    }

    public function update(int $id, array $payload): void
    {
        $data = array_intersect_key($payload, array_flip(['name', 'enabled', 'plan_code']));

        if (!$data) {
            return;
        }

        if (isset($data['enabled'])) {
            $data['enabled'] = (int) $data['enabled'];
        }

        Db::table('tenants')->where('id', $id)->update($data);
    }

    public function delete(int $id): void
    {
        Db::table('tenant_domains')->where('tenant_id', $id)->delete();
        Db::table('tenant_plugin_settings')->where('tenant_id', $id)->delete();
        Db::table('tenant_applications')->where('tenant_id', $id)->delete();
        Db::table('tenants')->where('id', $id)->delete();
    }

    public function domains(int $tenantId): array
    {
        return array_map('get_object_vars', Db::table('tenant_domains')->where('tenant_id', $tenantId)->get()->all());
    }

    public function addDomain(int $tenantId, string $domain): int
    {
        $domain = strtolower(trim($domain));
        if ($domain === '') {
            throw new InvalidArgumentException('domain is required');
        }

        return (int) Db::table('tenant_domains')->insertGetId([
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'enabled' => 1,
        ]);
    }

    public function removeDomain(int $tenantId, int $domainId): void
    {
        Db::table('tenant_domains')
            ->where('tenant_id', $tenantId)
            ->where('id', $domainId)
            ->delete();
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
