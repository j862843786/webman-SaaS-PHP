# webman SaaS PHP

基于 Webman 的 SaaS 基础框架底座，内置 MySQL 租户解析约定，支持按配置启用域名识别、公表与租户分表能力，并预留多应用及插件化扩展入口。

## 快速开始

```bash
cp .env.example .env
composer install
mysql < database/migrations/001_saas_foundation.sql
mysql < database/seeds/001_default_data.sql
php start.php start
```

访问：

- `GET /` 查看当前租户上下文和扩展配置。
- `GET /health` 查看健康状态。
- `GET /admin/tenants` 管理租户。
- `GET /admin/applications` 管理应用。
- `GET /admin/plugins` 管理插件。

## 核心能力

- 租户解析：域名、请求头、query 参数、默认租户逐级回退。
- 公表配置：`TENANT_PUBLIC_TABLES` 统一声明共享表。
- 分表配置：`TENANT_SHARDING_ENABLED` 控制租户业务表是否按桶后缀路由。
- 租户查询：`app\\support\\TenantTable::query()` 对非公表自动追加 `tenant_id` 条件。
- 租户管理：提供租户、租户域名、租户应用、租户插件启用接口。
- 扩展预留：`applications`、`tenant_applications`、`plugins` 与 `tenant_plugin_settings` 表用于后续多应用和插件管理。

## 分表 SQL 生成

启用 `TENANT_SHARDING_ENABLED=true` 后，可使用脚本批量生成分表 SQL：

```bash
php bin/create-shards.php orders 16
```

默认输出 `CREATE TABLE orders_00 LIKE orders;` 到 `orders_15`。如果需要自定义 schema，可传入第三个参数，并用 `{table}` 占位真实表名。

## 文档

- SaaS 底座设计：[docs/saas-foundation.md](docs/saas-foundation.md)
- 管理 API 草案：[docs/api.md](docs/api.md)
