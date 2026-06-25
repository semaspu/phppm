<?php
require __DIR__ . '/vendor/autoload.php';

use AirGuard\Config;
use AirGuard\Database;
use AirGuard\ReadingRepository;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$config = Config::load(__DIR__);
$pdo = Database::connect($config['db']);
$repo = new ReadingRepository($pdo);
$mqttConfig = $config['mqtt'];

$settings = (new ConnectionSettings())
    ->setUsername($mqttConfig['username'] ?: null)
    ->setPassword($mqttConfig['password'] ?: null)
    ->setUseTls($mqttConfig['use_tls']);

$mqtt = new MqttClient($mqttConfig['host'], $mqttConfig['port'], $mqttConfig['client_id']);
$mqtt->connect($settings, true);

echo "Connected to MQTT broker {$mqttConfig['host']}:{$mqttConfig['port']}\n";
echo "Subscribed topic: {$mqttConfig['topic']}\n";

$mqtt->subscribe($mqttConfig['topic'], function (string $topic, string $message) use ($repo) {
    echo date('Y-m-d H:i:s') . " | {$topic} | {$message}\n";

    $data = json_decode($message, true);
    if (!is_array($data)) {
        echo "Invalid JSON, skipped.\n";
        return;
    }

    // If station_id is not sent, extract from topic airguard/{station_id}/data
    if (empty($data['station_id'])) {
        $parts = explode('/', $topic);
        $data['station_id'] = $parts[1] ?? 'unknown';
    }

    // Accept both common names and device short names.
    $data['pm25'] = $data['pm25'] ?? $data['pm2_5'] ?? $data['PM25'] ?? null;
    $data['pm10'] = $data['pm10'] ?? $data['PM10'] ?? null;
    $data['temperature'] = $data['temperature'] ?? $data['temp'] ?? $data['T'] ?? null;
    $data['humidity'] = $data['humidity'] ?? $data['hum'] ?? $data['H'] ?? null;
    $data['noise'] = $data['noise'] ?? $data['db'] ?? $data['dB'] ?? null;

    $repo->insert($data);
}, 0);

$mqtt->loop(true);
$mqtt->disconnect();
