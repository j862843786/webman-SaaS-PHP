INSERT INTO tenants (code, name, enabled, plan_code)
VALUES ('default', '默认租户', 1, 'basic')
ON DUPLICATE KEY UPDATE name = VALUES(name), enabled = VALUES(enabled), plan_code = VALUES(plan_code);

INSERT INTO applications (code, name, entry_path, enabled)
VALUES
    ('admin', '管理后台', 'app/application/admin', 1),
    ('api', '开放接口', 'app/application/api', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), entry_path = VALUES(entry_path), enabled = VALUES(enabled);

INSERT INTO plugins (code, name, version, enabled, config)
VALUES ('example', '示例插件', '0.1.0', 0, JSON_OBJECT())
ON DUPLICATE KEY UPDATE name = VALUES(name), version = VALUES(version), enabled = VALUES(enabled), config = VALUES(config);
