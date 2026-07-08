# Webman SaaS 基础框架底座

本项目提供面向后续多应用与插件化开发的 Webman SaaS 底座约定，默认使用 MySQL 作为数据存储。

## 租户识别

租户解析由 `app\\middleware\\TenantMiddleware` 注入到全局中间件，解析结果保存在 `app\\tenant\\TenantContext`。

解析优先级：

1. `TENANT_DOMAIN_ENABLED=true` 时优先根据当前 Host 查询 `tenant_domains` 公表。
2. 域名未命中或未启用域名识别时，读取 `X-Tenant-Code` 请求头。
3. 请求头不存在时读取 `tenant` query 参数。
4. 最后回落到 `TENANT_DEFAULT_CODE`，默认 `default`。

## 公表

`TENANT_PUBLIC_TABLES` 中配置的表被视为所有租户共享的公表，默认包括：

- `tenants`
- `tenant_domains`
- `system_settings`
- `plugins`
- `applications`
- `tenant_plugin_settings`
- `tenant_applications`

公表不会参与租户分表命名，也不会由 `TenantTable::query()` 自动追加 `tenant_id` 条件。

## 业务租户表

租户业务表建议保留 `tenant_id` 字段，并通过 `app\\support\\TenantTable::query($table)` 访问。该 helper 会：

1. 使用 `TenantContext::table($table)` 计算真实表名。
2. 对非公表自动追加当前租户的 `tenant_id` 过滤条件。

## 分表

`TENANT_SHARDING_ENABLED=false` 时所有业务表保持原始表名。启用后可通过 `TenantContext::table('orders')` 获取物理表名，例如租户 ID 为 `17` 且 `TENANT_SHARD_MODULO=16` 时返回 `orders_01`。

建议业务代码统一通过 `TenantContext::table($baseTable)` 或 `TenantTable::query($baseTable)` 访问租户业务表，以便后续无侵入切换是否分表。

## 多应用与插件

- `applications` 公表登记应用编码、名称与入口路径。
- `tenant_applications` 记录租户启用的应用与租户级配置。
- `plugins` 公表登记插件编码、版本、启用状态与 JSON 配置。
- `tenant_plugin_settings` 记录租户启用的插件与租户级配置。
- `config/app_loader.php` 和 `config/plugin_loader.php` 预留本地应用、插件目录扫描入口。

## 初始化数据库

执行 `database/migrations/001_saas_foundation.sql` 创建租户、公表、应用与插件基础表。

## 管理服务层

- `app\\service\\TenantService` 封装租户、域名增删改查。
- `app\\service\\ExtensionService` 封装应用、插件登记，以及租户级启用配置。
- `app\\support\\ApiResponse` 统一 API 响应格式。
- `app\\admin\\controller` 下提供可直接扩展的管理控制器。

## 分表 SQL 工具

执行 `php bin/create-shards.php orders 16` 可生成 `orders_00` 到 `orders_15` 的 `CREATE TABLE ... LIKE ...` SQL，用于配合 `TENANT_SHARDING_ENABLED=true`。

