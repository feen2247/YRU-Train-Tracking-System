/*
 * YRU Train Tracking System - ESP32 GPS Tracker
 *
 * อ่านพิกัดจากโมดูล GPS (TinyGPS++) แล้ว:
 *   1) ส่งพิกัดขึ้นเว็บทุก SEND_INTERVAL_MS ผ่าน POST {SERVER_URL}/api/gps/report
 *      (เว็บดึงไปแสดงบนแผนที่จาก GET /api/gps/positions)
 *   2) เปิด API บนตัว ESP32 เอง: GET http://<IP ของ ESP32>/gps  -> JSON ตำแหน่งล่าสุด
 *      ใช้ทดสอบ/ดีบักในวง WiFi เดียวกันได้ เว็บจริงไม่ได้ดึงจากตรงนี้ เพราะ ESP32 อยู่บนรถ
 *      ไม่มี IP สาธารณะ และเว็บที่เป็น https เรียก http ภายในวงไม่ได้
 *
 * รหัสอุปกรณ์ (DEVICE_ID) สร้างจาก MAC ของ ESP32 อัตโนมัติ เช่น ESP32-A1B2C3
 * ดูได้จาก Serial Monitor ตอนเปิดเครื่อง แล้วไปผูกกับรถที่หน้าแอดมิน > อุปกรณ์ GPS
 *
 * ไลบรารีที่ต้องติดตั้ง (Arduino Library Manager):
 *   - TinyGPSPlus (Mikal Hart)
 *   - ArduinoJson (Benoit Blanchon) v7
 * บอร์ด: ESP32 Dev Module (Arduino-ESP32 core 2.x หรือ 3.x)
 */

#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <ArduinoJson.h>
#include <TinyGPS++.h>

// ======================= ตั้งค่า =======================
const char* WIFI_SSID     = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// URL ของเว็บ (ไม่ต้องมี / ปิดท้าย) เช่น "https://406665014.site.yru.ac.th"
// ทดสอบบนเครื่องตัวเอง: "http://<IP คอมพิวเตอร์>:8000" แล้วรัน php artisan serve --host=0.0.0.0
const char* SERVER_URL = "http://192.168.1.100:8000";

// ต้องตรงกับ GPS_DEVICE_KEY ในไฟล์ .env ของเว็บ
const char* GPS_DEVICE_KEY = "CHANGE_ME";

// ปล่อยว่างเพื่อใช้รหัสจาก MAC อัตโนมัติ หรือกำหนดเองได้ เช่น "GPS-01"
String DEVICE_ID = "";

const unsigned long SEND_INTERVAL_MS  = 5000;   // ส่งพิกัดขึ้นเว็บทุก 5 วินาที
const unsigned long PRINT_INTERVAL_MS = 10000;  // พิมพ์ลิงก์ Google Maps ทุก 10 วินาที
const unsigned long MAX_FIX_AGE_MS    = 5000;   // พิกัดเก่ากว่านี้ถือว่าไม่ใช้
// =======================================================

#define RXD2 16
#define TXD2 17
#define GPS_BAUD 115200  // ใช้ Baud Rate เดียวกับที่สแกนเจอ

TinyGPSPlus gps;
WebServer server(80);

unsigned long lastSendMs = 0;
unsigned long lastPrintMs = 0;
unsigned long lastWifiAttemptMs = 0;
int lastHttpCode = 0;
String lastHttpMessage = "ยังไม่เคยส่ง";

String makeDeviceId() {
  uint64_t mac = ESP.getEfuseMac();
  char buf[16];
  // 3 ไบต์ท้ายของ MAC
  snprintf(buf, sizeof(buf), "ESP32-%02X%02X%02X",
           (uint8_t)(mac >> 24), (uint8_t)(mac >> 32), (uint8_t)(mac >> 40));
  return String(buf);
}

bool hasFreshFix() {
  return gps.location.isValid() && gps.location.age() <= MAX_FIX_AGE_MS;
}

void connectWifi() {
  if (WiFi.status() == WL_CONNECTED) return;
  if (lastWifiAttemptMs != 0 && millis() - lastWifiAttemptMs < 15000) return;
  lastWifiAttemptMs = millis();
  Serial.printf("[WiFi] กำลังเชื่อมต่อ %s ...\n", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
}

// ---------- API บนตัว ESP32 ----------
void handleGpsJson() {
  JsonDocument doc;
  doc["device_id"] = DEVICE_ID;
  doc["valid"] = hasFreshFix();
  if (gps.location.isValid()) {
    doc["lat"] = serialized(String(gps.location.lat(), 6));
    doc["lng"] = serialized(String(gps.location.lng(), 6));
    doc["age_ms"] = gps.location.age();
    doc["google_maps"] = "https://maps.google.com/?q=" + String(gps.location.lat(), 6) + "," + String(gps.location.lng(), 6);
  } else {
    doc["lat"] = nullptr;
    doc["lng"] = nullptr;
  }
  doc["speed_kmh"] = gps.speed.isValid() ? gps.speed.kmph() : 0;
  doc["satellites"] = gps.satellites.isValid() ? (int)gps.satellites.value() : 0;
  doc["hdop"] = gps.hdop.isValid() ? gps.hdop.hdop() : 0;
  doc["chars_processed"] = gps.charsProcessed();
  doc["last_upload_http_code"] = lastHttpCode;
  doc["last_upload_message"] = lastHttpMessage;

  String out;
  serializeJson(doc, out);
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "application/json; charset=utf-8", out);
}

void handleRoot() {
  server.send(200, "text/plain; charset=utf-8",
              "YRU GPS Tracker " + DEVICE_ID + "\nGET /gps -> JSON ตำแหน่งล่าสุด\n");
}

// ---------- ส่งพิกัดขึ้นเว็บ ----------
void uploadPosition() {
  if (WiFi.status() != WL_CONNECTED) {
    lastHttpMessage = "WiFi ยังไม่เชื่อมต่อ";
    return;
  }
  if (!hasFreshFix()) {
    lastHttpMessage = "ยังไม่มีสัญญาณ GPS";
    return;
  }

  JsonDocument doc;
  doc["device_id"] = DEVICE_ID;
  doc["lat"] = serialized(String(gps.location.lat(), 7));
  doc["lng"] = serialized(String(gps.location.lng(), 7));
  if (gps.speed.isValid())      doc["speed_kmh"] = gps.speed.kmph();
  if (gps.satellites.isValid()) doc["satellites"] = gps.satellites.value();
  if (gps.hdop.isValid())       doc["hdop"] = gps.hdop.hdop();
  String body;
  serializeJson(doc, body);

  String url = String(SERVER_URL) + "/api/gps/report";
  HTTPClient http;
  WiFiClientSecure secureClient;
  WiFiClient plainClient;

  bool began;
  if (url.startsWith("https://")) {
    // ไม่ตรวจใบรับรอง SSL เพื่อให้ใช้ง่าย ถ้าต้องการความปลอดภัยสูงขึ้นให้ใช้ setCACert() แทน
    secureClient.setInsecure();
    began = http.begin(secureClient, url);
  } else {
    began = http.begin(plainClient, url);
  }
  if (!began) {
    lastHttpMessage = "เริ่มการเชื่อมต่อไม่ได้";
    return;
  }

  http.setTimeout(5000);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.addHeader("X-GPS-Key", GPS_DEVICE_KEY);

  lastHttpCode = http.POST(body);
  if (lastHttpCode == 200) {
    JsonDocument res;
    if (!deserializeJson(res, http.getString()) && !res["vehicle_id"].isNull()) {
      lastHttpMessage = String("ส่งสำเร็จ (รถ ") + res["vehicle_id"].as<const char*>() + ")";
    } else {
      lastHttpMessage = "ส่งสำเร็จ (ยังไม่ได้ผูกกับรถในหน้าแอดมิน)";
    }
  } else if (lastHttpCode > 0) {
    lastHttpMessage = "เซิร์ฟเวอร์ตอบ " + String(lastHttpCode) + ": " + http.getString().substring(0, 120);
  } else {
    lastHttpMessage = "ส่งไม่สำเร็จ: " + http.errorToString(lastHttpCode);
  }
  http.end();
  Serial.printf("[Upload] %d %s\n", lastHttpCode, lastHttpMessage.c_str());
}

void setup() {
  Serial.begin(115200);
  Serial2.setRxBufferSize(2048);  // กันข้อมูล NMEA ล้นบัฟเฟอร์ระหว่างที่รอเว็บตอบ
  Serial2.begin(GPS_BAUD, SERIAL_8N1, RXD2, TXD2);

  if (DEVICE_ID.length() == 0) DEVICE_ID = makeDeviceId();
  Serial.println();
  Serial.println("==============================");
  Serial.println(" YRU GPS Tracker");
  Serial.println(" DEVICE_ID: " + DEVICE_ID);
  Serial.println(" (นำรหัสนี้ไปผูกกับรถที่หน้าแอดมิน > อุปกรณ์ GPS)");
  Serial.println("==============================");

  connectWifi();

  server.on("/", handleRoot);
  server.on("/gps", handleGpsJson);
  server.begin();
}

void loop() {
  // อ่าน GPS ตลอดเวลา ห้ามใส่ delay() ยาว ๆ ใน loop เพราะจะทำให้ข้อมูล GPS ค้าง/หาย
  while (Serial2.available() > 0) {
    gps.encode(Serial2.read());
  }

  static bool wasConnected = false;
  bool connected = WiFi.status() == WL_CONNECTED;
  if (connected && !wasConnected) {
    Serial.print("[WiFi] เชื่อมต่อแล้ว IP: ");
    Serial.println(WiFi.localIP());
    Serial.println("[API]  http://" + WiFi.localIP().toString() + "/gps");
  }
  wasConnected = connected;
  if (!connected) connectWifi();

  server.handleClient();

  unsigned long now = millis();

  if (now - lastPrintMs >= PRINT_INTERVAL_MS) {
    lastPrintMs = now;
    if (gps.location.isValid()) {
      Serial.print("Google Maps Link: https://maps.google.com/?q=");
      Serial.print(gps.location.lat(), 6);
      Serial.print(",");
      Serial.println(gps.location.lng(), 6);
    } else {
      Serial.printf("[GPS] ยังไม่มีสัญญาณ (อ่านข้อมูลแล้ว %lu ตัวอักษร)\n", gps.charsProcessed());
    }
  }

  if (now - lastSendMs >= SEND_INTERVAL_MS) {
    lastSendMs = now;
    uploadPosition();
  }
}
