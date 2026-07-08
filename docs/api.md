# 管理 API 草案

所有接口返回统一结构：

```json
{
  "code": 0,
  "message": "ok",
  "data": {}
}
```

## 租户

- `GET /admin/tenants`：租户列表。
- `POST /admin/tenants`：创建租户，参数：`code`、`name`、`enabled`、`plan_code`。
- `GET /admin/tenants/{id}`：租户详情。
- `PUT /admin/tenants/{id}`：更新租户，参数：`name`、`enabled`、`plan_code`。
- `DELETE /admin/tenants/{id}`：删除租户，并清理该租户域名、应用和插件配置。
- `GET /admin/tenants/{id}/domains`：租户域名列表。
- `POST /admin/tenants/{id}/domains`：添加租户域名，参数：`domain`。
- `DELETE /admin/tenants/{id}/domains/{domainId}`：删除租户域名。

## 应用

- `GET /admin/applications`：应用列表。
- `POST /admin/applications`：登记或更新应用，参数：`code`、`name`、`entry_path`、`enabled`。
- `POST /admin/tenants/{tenantId}/applications/{code}`：为租户启用应用，可传 `config`。

## 插件

- `GET /admin/plugins`：插件列表。
- `POST /admin/plugins`：登记或更新插件，参数：`code`、`name`、`version`、`enabled`、`config`。
- `POST /admin/tenants/{tenantId}/plugins/{code}`：为租户启用插件，可传 `config`。
