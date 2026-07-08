<?php

namespace app\tenant;

use RuntimeException;

final class TenantContext
{
    private static ?array $tenant = null;

    public static function set(?array $tenant): void
    {
        self::$tenant = $tenant;
    }

    public static function get(): ?array
    {
        return self::$tenant;
    }

    public static function require(): array
    {
        if (!self::$tenant) {
            throw new RuntimeException('Tenant context is not initialized.');
        }

        return self::$tenant;
    }

    public static function id(): ?int
    {
        return isset(self::$tenant['id']) ? (int) self::$tenant['id'] : null;
    }

    public static function code(): ?string
    {
        return self::$tenant['code'] ?? null;
    }

    public static function clear(): void
    {
        self::$tenant = null;
    }

    public static function isPublicTable(string $baseTable): bool
    {
        $config = config('tenant', []);
        $publicTables = $config['public_tables'] ?? [];

        return in_array($baseTable, $publicTables, true);
    }

    public static function table(string $baseTable): string
    {
        if (self::isPublicTable($baseTable)) {
            return $baseTable;
        }

        $tenantId = self::id();
        if (!$tenantId) {
            return $baseTable;
        }

        $config = config('tenant', []);
        $sharding = $config['sharding'] ?? [];
        if (!($sharding['enabled'] ?? false)) {
            return $baseTable;
        }

        $modulo = max(1, (int) ($sharding['modulo'] ?? 16));
        $bucket = $tenantId % $modulo;
        $width = max(2, strlen((string) ($modulo - 1)));

        return sprintf('%s_%0' . $width . 'd', $baseTable, $bucket);
    }
}
