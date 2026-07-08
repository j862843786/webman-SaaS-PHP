<?php

namespace app\support;

use Predis\Client;

final class Redis
{
    private static ?Client $client = null;

    public static function client(): Client
    {
        if (self::$client instanceof Client) {
            return self::$client;
        }

        $redisConfig = config('redis', []);
        $config = $redisConfig['default'] ?? [];
        self::$client = new Client([
            'scheme' => 'tcp',
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? 6379,
            'password' => $config['password'] ?: null,
            'database' => $config['database'] ?? 0,
            'timeout' => $config['timeout'] ?? 2.0,
        ], [
            'prefix' => $config['prefix'] ?? 'webman_saas:',
        ]);

        return self::$client;
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $client = self::client();
        $cached = $client->get($key);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $value = $callback();
        $client->setex($key, $ttl, json_encode($value, JSON_UNESCAPED_UNICODE));

        return $value;
    }

    public static function forget(string $key): void
    {
        self::client()->del([$key]);
    }
}
