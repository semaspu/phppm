<?php
namespace AirGuard;

use PDO;

final class ReadingRepository
{
    public function __construct(private PDO $pdo) {}

    public function insert(array $data): void
    {
        $sql = "INSERT INTO sensor_readings
            (station_id, pm25, pm10, temperature, humidity, noise, rssi, snr, raw_payload, received_at)
            VALUES
            (:station_id, :pm25, :pm10, :temperature, :humidity, :noise, :rssi, :snr, :raw_payload, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':station_id' => $data['station_id'] ?? 'unknown',
            ':pm25' => $data['pm25'] ?? null,
            ':pm10' => $data['pm10'] ?? null,
            ':temperature' => $data['temperature'] ?? null,
            ':humidity' => $data['humidity'] ?? null,
            ':noise' => $data['noise'] ?? null,
            ':rssi' => $data['rssi'] ?? null,
            ':snr' => $data['snr'] ?? null,
            ':raw_payload' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function latestByStation(): array
    {
        $sql = "SELECT sr.*
                FROM sensor_readings sr
                INNER JOIN (
                    SELECT station_id, MAX(id) AS max_id
                    FROM sensor_readings
                    GROUP BY station_id
                ) x ON sr.station_id = x.station_id AND sr.id = x.max_id
                ORDER BY sr.station_id ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function recent(string $stationId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sensor_readings WHERE station_id = :station_id ORDER BY received_at DESC LIMIT :limit");
        $stmt->bindValue(':station_id', $stationId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }
}
