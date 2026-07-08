<?php

namespace app\tenant;

use support\Db;
use Webman\Http\Request;

final class TenantResolver
{
    public function resolve(Request $request): ?array
    {
        $config = config('tenant', []);

        if (($config['domain_enabled'] ?? true) === true) {
            $tenant = $this->resolveByDomain($this->normalizeHost($request->host(true)));
            if ($tenant) {
                return $tenant;
            }
        }

        $header = $config['header'] ?? 'X-Tenant-Code';
        $code = $request->header($header) ?: $request->get('tenant') ?: ($config['default_code'] ?? null);

        return $code ? $this->resolveByCode((string) $code) : null;
    }

    private function resolveByDomain(string $host): ?array
    {
        $domain = Db::table('tenant_domains')
            ->where('domain', $host)
            ->where('enabled', 1)
            ->first();

        if (!$domain) {
            return null;
        }

        return $this->resolveById((int) $domain->tenant_id);
    }

    private function resolveByCode(string $code): ?array
    {
        $tenant = Db::table('tenants')
            ->where('code', $code)
            ->where('enabled', 1)
            ->first();

        return $tenant ? (array) $tenant : null;
    }

    private function resolveById(int $id): ?array
    {
        $tenant = Db::table('tenants')
            ->where('id', $id)
            ->where('enabled', 1)
            ->first();

        return $tenant ? (array) $tenant : null;
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));

        if (str_starts_with($host, '[')) {
            return trim($host, '[]');
        }

        return explode(':', $host, 2)[0];
    }
}
