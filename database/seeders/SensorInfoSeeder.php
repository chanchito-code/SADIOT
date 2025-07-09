<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensorInfo;

class SensorInfoSeeder extends Seeder
{
    public function run()
    {
        SensorInfo::truncate();

        $sensores = [
            [
                'name' => 'DHT11',
                'description' => 'Sensor digital para medir temperatura y humedad.',
                'wiring' => 'https://cdn-learn.adafruit.com/assets/assets/000/002/309/medium800/dht11-pinout.png',
                'code_snippet' => <<<'EOD'
// Código igual al anterior, omitido por espacio
EOD
            ],
            [
                'name' => 'Sensor PIR (Movimiento)',
                'description' => 'Detecta movimiento mediante infrarrojos.',
                'wiring' => 'https://static.wixstatic.com/media/9b186d_2c295182702f4d89ae0c7df53cc9b61f~mv2.jpg',
                'code_snippet' => <<<'EOD'
// Código igual al anterior, omitido por espacio
EOD
            ],
            [
                'name' => 'Sensor Ultrasónico HC-SR04',
                'description' => 'Mide distancia por ultrasonido.',
                'wiring' => 'https://lastminuteengineers.com/wp-content/uploads/ultrasonic-sensor-hc-sr04-pinout-768x382.png',
                'code_snippet' => <<<'EOD'
// Código igual al anterior, omitido por espacio
EOD
            ],
            [
                'name' => 'Potenciómetro',
                'description' => 'Sensor analógico que mide posición de un eje rotativo.',
                'wiring' => 'https://www.learningaboutelectronics.com/images/Potentiometer-pinout.png',
                'code_snippet' => <<<'EOD'
#define POT_PIN 34
const char* esp32_id = "{{esp32_id}}";
const char* serverURL = "{{serverURL}}";

void setup() {
  Serial.begin(115200);
}

void loop() {
  int value = analogRead(POT_PIN);
  float voltage = (value * 3.3) / 4095.0;
  sendSensor("pot-001", "analog", voltage);
  delay(5000);
}

void sendSensor(const char* uid, const char* tipo, float valor) {
  WiFiClient client;
  HTTPClient http;
  StaticJsonDocument<256> doc;

  doc["esp32_id"] = esp32_id;
  doc["sensor_uid"] = uid;
  doc["tipo"] = tipo;
  doc["valor"] = valor;
  doc["timestamp"] = "2025-07-03T12:00:00+00:00";

  String payload;
  serializeJson(doc, payload);

  http.begin(serverURL);
  http.addHeader("Content-Type", "application/json");
  http.POST(payload);
  http.end();
}
EOD
            ],
            [
                'name' => 'Sensor de Humedad de Suelo',
                'description' => 'Mide humedad en tierra para plantas.',
                'wiring' => 'https://www.electronicwings.com/public/images/user_images/images/Soil%20Moisture%20Sensor%20Module%20Interfacing%20with%20Arduino/soil-moisture-sensor.jpg',
                'code_snippet' => <<<'EOD'
#define SOIL_PIN 34
// Similar al potenciómetro, solo cambia el tipo a "soil"
EOD
            ],
            [
                'name' => 'Sensor LDR (Luz)',
                'description' => 'Mide luz ambiente.',
                'wiring' => 'https://www.etechnophiles.com/wp-content/uploads/2021/08/LDR-pinout.png',
                'code_snippet' => <<<'EOD'
#define LDR_PIN 35
// Código similar al potenciómetro
EOD
            ],
            [
                'name' => 'Sensor de Gas MQ-2',
                'description' => 'Detecta gases inflamables como butano, LPG y humo.',
                'wiring' => 'https://lastminuteengineers.com/wp-content/uploads/mq2-pinout-768x768.png',
                'code_snippet' => <<<'EOD'
#define MQ2_PIN 36
// Código similar al potenciómetro
EOD
            ],
            [
                'name' => 'Sensor de Flama (Infrarrojo)',
                'description' => 'Detecta la presencia de flamas o fuego.',
                'wiring' => 'https://lastminuteengineers.com/wp-content/uploads/flame-sensor-pinout-768x577.png',
                'code_snippet' => <<<'EOD'
#define FLAME_PIN 14
void loop() {
  int flame = digitalRead(FLAME_PIN);
  sendSensor("flame-001", "fire", flame);
  delay(3000);
}
EOD
            ],
            [
                'name' => 'Sensor de Sonido KY-038',
                'description' => 'Detecta ruidos o sonidos fuertes.',
                'wiring' => 'https://components101.com/sites/default/files/component_pin/Sound-Sensor-KY-038-Pinout.png',
                'code_snippet' => <<<'EOD'
#define SOUND_PIN 27
// Código igual: digitalRead y envío
EOD
            ],
            [
                'name' => 'Sensor de Nivel de Agua',
                'description' => 'Mide el nivel de agua (tanque o recipiente).',
                'wiring' => 'https://lastminuteengineers.com/wp-content/uploads/water-level-sensor-pinout-768x362.png',
                'code_snippet' => <<<'EOD'
#define WATER_PIN 32
// Código similar al potenciómetro
EOD
            ],
        ];

        foreach ($sensores as $sensor) {
            SensorInfo::create($sensor);
        }
    }
}
