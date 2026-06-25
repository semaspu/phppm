<?php
require __DIR__ . '/../vendor/autoload.php';

use AirGuard\Config;
use AirGuard\Database;
use AirGuard\ReadingRepository;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$config = Config::load(dirname(__DIR__));
$repo = new ReadingRepository(Database::connect($config['db']));

$action = $_GET['action'] ?? 'latest';

try {
    if ($action === 'latest') {
        echo json_encode(['ok' => true, 'data' => $repo->latestByStation()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'recent') {
        $station = $_GET['station'] ?? '';
        if ($station === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'station is required']);
            exit;
        }
        echo json_encode(['ok' => true, 'data' => $repo->recent($station, 50)], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'unknown action']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
