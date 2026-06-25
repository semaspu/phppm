<?php
namespace AirGuard;

final class Config
{
    public static function load(string $basePath): array
    {
        $envPath = $basePath . '/.env';
        if (file_exists($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $_ENV[trim($key)] = $value;
            }
        }

        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

        return [
            'app_name' => $_ENV['APP_NAME'] ?? 'AirGuard Construction',
            'db' => [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'name' => $_ENV['DB_NAME'] ?? 'airguard',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'pass' => $_ENV['DB_PASS'] ?? '',
            ],
            'mqtt' => [
                'host' => $_ENV['MQTT_HOST'] ?? '127.0.0.1',
                'port' => (int)($_ENV['MQTT_PORT'] ?? 1883),
                'client_id' => $_ENV['MQTT_CLIENT_ID'] ?? 'airguard_php_worker',
                'username' => $_ENV['MQTT_USERNAME'] ?? null,
                'password' => $_ENV['MQTT_PASSWORD'] ?? null,
                'topic' => $_ENV['MQTT_TOPIC'] ?? 'airguard/+/data',
                'use_tls' => filter_var($_ENV['MQTT_USE_TLS'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ],
        ];
    }
}
