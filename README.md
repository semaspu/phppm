# AirGuard MQTT PHP Dashboard

## TH: แนวคิด
เว็บนี้รับข้อมูลจาก MQTT Broker ด้วย `mqtt_worker.php` แล้วบันทึกลง MySQL จากนั้นหน้า `public/index.php` จะดึงข้อมูลผ่าน `public/api.php` ทุก 5 วินาทีเพื่อแสดง Dashboard

## EN: Concept
This app receives data from an MQTT broker using `mqtt_worker.php`, saves it to MySQL, then `public/index.php` polls `public/api.php` every 5 seconds to display a dashboard.

## Expected MQTT topic
`airguard/{station_id}/data`

Example:
`airguard/N01/data`

## Expected MQTT JSON payload
```json
{
  "station_id": "N01",
  "pm25": 18.5,
  "pm10": 41.2,
  "temperature": 30.1,
  "humidity": 65.2,
  "noise": 72.4,
  "rssi": -82,
  "snr": 9.5
}
```

## Installation
```bash
cd airguard_php
cp .env.example .env
composer install
mysql -u root -p < sql/schema.sql
php -S localhost:8000 -t public
```

Open:
`http://localhost:8000`

## Run MQTT worker
Open another terminal:
```bash
php mqtt_worker.php
```

For production, run the worker with Supervisor or systemd.

## Test publish with Mosquitto
```bash
mosquitto_pub -h 127.0.0.1 -t airguard/N01/data -m '{"pm25":18.5,"pm10":41.2,"temperature":30.1,"humidity":65.2,"noise":72.4}'
```
