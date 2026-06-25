CREATE DATABASE IF NOT EXISTS airguard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE airguard;

CREATE TABLE IF NOT EXISTS sensor_readings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  station_id VARCHAR(50) NOT NULL,
  pm25 DECIMAL(8,2) NULL,
  pm10 DECIMAL(8,2) NULL,
  temperature DECIMAL(8,2) NULL,
  humidity DECIMAL(8,2) NULL,
  noise DECIMAL(8,2) NULL,
  rssi DECIMAL(8,2) NULL,
  snr DECIMAL(8,2) NULL,
  raw_payload JSON NULL,
  received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_station_time (station_id, received_at),
  INDEX idx_time (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
