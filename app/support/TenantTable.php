<?php

namespace app\support;

use app\tenant\TenantContext;
use support\Db;

final class TenantTable
{
    public static function name(string $baseTable): string
    {
        return TenantContext::table($baseTable);
    }

    public static function query(string $baseTable)
    {
        $tenantId = TenantContext::id();
        $query = Db::table(self::name($baseTable));

        if ($tenantId && !TenantContext::isPublicTable($baseTable)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
