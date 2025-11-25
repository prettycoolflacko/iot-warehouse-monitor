#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h> // WAJIB INSTALL LIBRARY: ArduinoJson
#include <DHT.h>

// --- KREDENSIAL WIFI ---
const char* ssid = "Gembong Center";
const char* password = "Pawpatrol#321";

// --- KONFIGURASI SERVER ---
// Ganti dengan IP VPS atau Domain kamu
// Contoh: "http://192.168.1.10/api.php" atau "http://rakhaproject.com/api.php"
const char* serverName = "http://47.237.15.145//api.php";

// --- Definisi Pin ---
const int RELAY_PIN = 19;     // GPIO Relai/Fan
const int DHT_PIN = 23;       // GPIO  DHT11
const int MQ6_DO_PIN = 18;    // GPIO  MQ-6 Digital
const int MQ6_AO_PIN = 34;     //

// --- Konfigurasi Sensor ---
#define DHTTYPE DHT11
DHT dht(DHT_PIN, DHTTYPE);

// --- Logika Relai ---
// Sesuaikan dengan modul relai (LOW = ON atau HIGH = ON)
const int RELAY_ON = LOW;  
const int RELAY_OFF = HIGH;

// Batas Safety (Jika suhu > 35, otomatis matikan fan atau nyalakan safety protocol)
// Di sini logicnya: Jika Web bilang ON, kita ON. Tapi jika suhu PANAS SEKALI, kita bisa force ON fan pendingin.
// Untuk kode ini, kita ikuti perintah WEB sepenuhnya, kecuali sensor error.

void setup() {
  Serial.begin(115200);

  // Init Pin
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF); // Default mati
  pinMode(MQ6_DO_PIN, INPUT);

  // Init Sensor
  dht.begin();

  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nTerhubung ke WiFi!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Tunggu sebentar antar pembacaan
  delay(2000);

  // 1. BACA SENSOR
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  int gas_analog = analogRead(MQ6_AO_PIN);
  int gas_digital = digitalRead(MQ6_DO_PIN);

  // Cek Error DHT
  if (isnan(t) || isnan(h)) {
    Serial.println("Gagal membaca DHT!");
    return;
  }

  // Tentukan Status Gas (String)
  String gasStatus = (gas_digital == LOW) ? "DANGER" : "SAFE";

  // 2. SIAPKAN DATA JSON UNTUK DIKIRIM
  // Kita cek koneksi WiFi dulu
  if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/json");

    // Buat JSON Payload
    // Format: {"temperature": 25.5, "humidity": 60, "gas_level": 120, "gas_status": "SAFE"}
    StaticJsonDocument<200> doc;
    doc["temperature"] = t;
    doc["humidity"] = h;
    doc["gas_level"] = gas_analog;
    doc["gas_status"] = gasStatus;

    String requestBody;
    serializeJson(doc, requestBody);

    // Kirim POST Request
    Serial.print("Mengirim data: ");
    Serial.println(requestBody);
    
    int httpResponseCode = http.POST(requestBody);

    // 3. BACA RESPON DARI SERVER (UNTUK KONTROL RELAY)
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.print("Respon Server: ");
      Serial.println(response);

      // Parsing JSON Respon dari Server
      // Server membalas: {"fan_status": true/false, ...}
      StaticJsonDocument<512> docResponse;
      DeserializationError error = deserializeJson(docResponse, response);

      if (!error) {
        bool serverFanStatus = docResponse["fan_status"]; // true atau false

        // LOGIKA KONTROL RELAY BERDASARKAN WEB
        if (serverFanStatus == true) {
          digitalWrite(RELAY_PIN, RELAY_ON);
          Serial.println("-> Perintah Server: FAN NYALA");
        } else {
          digitalWrite(RELAY_PIN, RELAY_OFF);
          Serial.println("-> Perintah Server: FAN MATI");
        }
      } else {
        Serial.print("Gagal parse JSON server: ");
        Serial.println(error.c_str());
      }
    } else {
      Serial.print("Error saat mengirim POST: ");
      Serial.println(httpResponseCode);
    }
    
    http.end(); // Tutup koneksi
  } else {
    Serial.println("WiFi Terputus!");
  }
}