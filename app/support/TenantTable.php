<?php

namespace app\support;

use app\tenant\TenantContext;
use support\Db;

final class TenantTable
{
    // 可选白名单：列出允许的“base table”逻辑名（根据你的项目表名调整）
    private const ALLOWED_TABLES = [
        // 'users', 'invoices', 'orders', ...
    ];

    public static function name(string $baseTable): string
    {
        // 基础校验：只允许字母数字和下划线
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $baseTable)) {
            throw new \InvalidArgumentException('Invalid table name');
        }

        // 可选：强烈建议配置/使用白名单以避免任意表名
        if (!empty(self::ALLOWED_TABLES) && !in_array($baseTable, self::ALLOWED_TABLES, true)) {
            throw new \InvalidArgumentException('Table is not allowed');
        }

        // TenantContext::table 应该返回实际表名（并且自身也应做校验）
        return TenantContext::table($baseTable);
    }

    public static function query(string $baseTable)
    {
        $tenantId = TenantContext::id();
        $tableName = self::name($baseTable);
        $query = Db::table($tableName);

        // 非公开表必须有 tenant 上下文 — 明确检查 null（不要用 truthy）
        if (!TenantContext::isPublicTable($baseTable)) {
            if ($tenantId === null) {
                // 拒绝访问或抛异常，避免未绑定 tenant 的查询
                throw new \RuntimeException('Missing tenant context for tenant-scoped table');
            }
            // 强制转为整型以避免类型绕过
            $tenantId = (int) $tenantId;
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
