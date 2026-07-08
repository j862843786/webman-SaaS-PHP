# 应用与插件开发约定

底座只负责租户、域名、公表、分表、管理 API 和扩展注册契约；具体业务应用与插件可以独立开发。

## 应用 Provider

应用 Provider 需要实现 `app\\contract\\ApplicationProviderInterface`：

```php
<?php

namespace app\\application\\admin;

use app\\contract\\ApplicationProviderInterface;

final class AdminApplication implements ApplicationProviderInterface
{
    public function code(): string
    {
        return 'admin';
    }

    public function name(): string
    {
        return '管理后台';
    }

    public function boot(): void
    {
        // 在这里注册路由、事件、菜单或服务。
    }
}
```

然后在 `config/app_loader.php` 中配置：

```php
'admin' => [
    'name' => '管理后台',
    'path' => 'admin',
    'enabled' => true,
    'provider' => app\\application\\admin\\AdminApplication::class,
],
```

## 插件 Provider

插件 Provider 需要实现 `app\\contract\\PluginProviderInterface`：

```php
<?php

namespace app\\plugin\\demo;

use app\\contract\\PluginProviderInterface;

final class DemoPlugin implements PluginProviderInterface
{
    public function code(): string
    {
        return 'demo';
    }

    public function name(): string
    {
        return '演示插件';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function boot(array $config = []): void
    {
        // 在这里注册插件能力。
    }
}
```

然后在 `config/plugin_loader.php` 中配置：

```php
'demo' => [
    'name' => '演示插件',
    'enabled' => true,
    'provider' => app\\plugin\\demo\\DemoPlugin::class,
    'config' => [],
],
```

## 注册中心

`app\\support\\ExtensionRegistry` 会读取 `config/app_loader.php` 和 `config/plugin_loader.php`，校验 Provider 是否实现对应契约，并执行 `boot()`。

## 租户数据访问

业务表建议包含 `tenant_id` 字段，业务代码通过 `app\\support\\TenantTable::query('orders')` 访问。启用分表时，底座会根据当前租户 ID 自动计算物理表名；未启用分表时仍然自动追加 `tenant_id` 条件。
