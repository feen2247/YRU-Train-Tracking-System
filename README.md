# YRU Train Tracking System

ระบบติดตามและบริหารจัดการรถไฟฟ้า (EV) ภายในมหาวิทยาลัยราชภัฏยะลา พัฒนาด้วย Laravel 12 (PHP 8.2+) และ Blade

## ความต้องการของระบบ

- PHP 8.2 ขึ้นไป (เปิด extension `pdo_mysql` หรือ `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`)
- Composer (ใช้ `php composer.phar` ที่อยู่ในโปรเจกต์แทนได้)
- Node.js 18 ขึ้นไป และ npm
- MySQL/MariaDB (แนะนำ) หรือ SQLite

บน Windows ติดตั้งทุกอย่างได้ง่ายที่สุดผ่าน [Laragon](https://laragon.org) หรือ XAMPP

## วิธีรันแอปบนเครื่อง (Local)

### 1. ติดตั้ง dependencies

```bash
composer install        # หรือ: php composer.phar install
npm install
```

### 2. ตั้งค่าไฟล์ `.env`

```bash
cp .env.example .env          # Windows (cmd): copy .env.example .env
php artisan key:generate
```

จากนั้นแก้ค่าฐานข้อมูลใน `.env`

**แบบ MySQL (แนะนำ ตรงกับเซิร์ฟเวอร์จริง)** สร้างฐานข้อมูลเปล่าก่อน เช่น `yru_train` แล้วตั้งค่า:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yru_train
DB_USERNAME=root
DB_PASSWORD=
```

**แบบ SQLite (ไม่ต้องติดตั้ง MySQL)**

```env
DB_CONNECTION=sqlite
# ลบหรือคอมเมนต์บรรทัด DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD ออก
```

แล้วสร้างไฟล์ว่าง `database/database.sqlite`

### 3. สร้างตารางและข้อมูลตัวอย่าง

```bash
php artisan migrate --seed
```

ต้องรันขั้นตอนนี้ก่อนเปิดเว็บ เพราะระบบเก็บ session และ cache ไว้ในฐานข้อมูล (`SESSION_DRIVER=database`, `CACHE_STORE=database`)

### 4. รันเซิร์ฟเวอร์

```bash
composer dev
```

คำสั่งนี้รัน `php artisan serve`, queue worker, log viewer (pail) และ Vite พร้อมกัน แล้วเปิด http://localhost:8000

หรือรันเฉพาะเว็บเซิร์ฟเวอร์ก็ได้:

```bash
php artisan serve
```

> หน้าเว็บส่วนใหญ่โหลด Tailwind, Leaflet, Chart.js และ SweetAlert2 จาก CDN จึงต้องต่ออินเทอร์เน็ตขณะใช้งาน

### ติดตั้งแบบคำสั่งเดียว

หลังแก้ค่า DB ใน `.env` แล้ว (หรือใช้ค่าเริ่มต้น) รันได้เลย:

```bash
composer setup   # install, สร้าง .env, key:generate, migrate, npm install, npm run build
php artisan db:seed
composer dev
```

## บัญชีทดสอบ (จาก `UserSeeder`)

เข้าสู่ระบบได้ด้วย username, อีเมล หรือรหัสพนักงาน รหัสผ่านเริ่มต้นคือ **รหัสพนักงาน**

| บทบาท | Username | รหัสผ่าน | หน้าที่เข้าถึง |
|---|---|---|---|
| ผู้ดูแลระบบ (Administrator) | `muhammad` | `69001` | `/admin-view` |
| ผู้บริหาร (Executive) | `somchai` | `69002` | `/executive-view` |
| พนักงานขับรถ (Driver) | `asmee` | `69003` | `/tracking` |
| ช่าง (Mechanic) | `prasan` | `69013` | `/maintenance-system` |
| หัวหน้างานยานพาหนะ | `suthin` | `69014` | `/vehicle-head` |
| นักศึกษา (Student) | `406665014` | `406665014` | `/home` |

ดูรายชื่อทั้งหมดได้ที่ [database/seeders/UserSeeder.php](database/seeders/UserSeeder.php)

## หน้าหลักของระบบ

| URL | ผู้ใช้งาน |
|---|---|
| `/` | หน้าเข้าสู่ระบบ / สมัครสมาชิก |
| `/home` | ผู้โดยสาร / นักศึกษา ดูแผนที่และตำแหน่งรถ เรียกรถ |
| `/tracking` | พนักงานขับรถ |
| `/admin-view` | ผู้ดูแลระบบ จัดการรถ เส้นทาง ผู้ใช้ รายงาน |
| `/executive-view` | ผู้บริหาร ดูสถิติและอนุมัติงานซ่อม |
| `/maintenance-system` | ช่างซ่อมบำรุง |
| `/vehicle-head` | หัวหน้างานยานพาหนะ |

## คำสั่งที่ใช้บ่อย

```bash
composer test                                   # รันเทสต์ทั้งหมด
php artisan test --filter=ExampleTest           # รันเทสต์เดียว
vendor/bin/pint                                 # จัดรูปแบบโค้ด
npm run build                                   # build assets สำหรับ production
php artisan migrate:fresh --seed                # ล้างฐานข้อมูลแล้วสร้างใหม่พร้อมข้อมูลตัวอย่าง
php artisan optimize:clear                      # ล้าง cache ของ config/route/view
```

ถ้าข้อมูลรถหรือสถานะคนขับค้าง ให้เปิด `/clear-all-caches` เพื่อรีเซ็ต cache ที่ใช้ซิงก์ข้อมูลระหว่างผู้ใช้

## GPS ติดรถ (ESP32)

รถแต่ละคันติดอุปกรณ์ GPS (ESP32 + โมดูล GPS) ได้ 1 ตัว ESP32 จะส่งพิกัดขึ้นเว็บทุก 5 วินาที แล้วแผนที่หน้า `/home` จะแสดงตำแหน่งจริงของรถคันนั้นแทนตำแหน่งจำลอง

**1. ตั้งค่าเว็บ** ใส่รหัสลับใน `.env` (ต้องตรงกับที่ใส่ใน ESP32) แล้วรัน migration

```env
GPS_DEVICE_KEY=ตั้งรหัสลับยาวๆ
GPS_STALE_SECONDS=120   # ถ้าไม่ได้รับพิกัดเกินกี่วินาที ให้ถือว่า GPS ออฟไลน์
```

```bash
php artisan migrate
```

**2. แฟลชโค้ดลง ESP32** เปิด [firmware/esp32_gps_tracker/esp32_gps_tracker.ino](firmware/esp32_gps_tracker/esp32_gps_tracker.ino) ด้วย Arduino IDE
- ติดตั้งไลบรารี **TinyGPSPlus** และ **ArduinoJson** (v7) จาก Library Manager
- แก้ `WIFI_SSID`, `WIFI_PASSWORD`, `SERVER_URL` และ `GPS_DEVICE_KEY` ที่ส่วนบนของไฟล์
- ถ้าทดสอบกับเครื่องตัวเอง ให้รัน `php artisan serve --host=0.0.0.0` แล้วตั้ง `SERVER_URL` เป็น `http://<IP คอมพิวเตอร์>:8000` (ESP32 กับคอมต้องอยู่ WiFi เดียวกัน)
- เปิด Serial Monitor (115200) จะเห็น `DEVICE_ID` เช่น `ESP32-A1B2C3` และ IP ของ ESP32

**3. ผูก GPS กับรถ** ในหน้าแอดมิน ไปที่เมนู **อุปกรณ์ GPS** อุปกรณ์จะขึ้นในตารางเองหลังส่งพิกัดครั้งแรก แล้วเลือกรถในช่อง "ติดตั้งบนรถ" (1 GPS ต่อ 1 คัน รถที่มี GPS แล้วจะเลือกซ้ำไม่ได้)

**API ที่เกี่ยวข้อง**

| Method | URL | ใช้ทำอะไร |
|---|---|---|
| POST | `/api/gps/report` | ESP32 ส่งพิกัด (ต้องมี header `X-GPS-Key`) |
| GET | `/api/gps/positions` | ตำแหน่งล่าสุดของรถที่ผูก GPS แล้ว (หน้าแผนที่ใช้) |
| GET/POST | `/api/gps/devices` | รายการ / เพิ่มอุปกรณ์ (แอดมินเท่านั้น) |
| POST | `/api/gps/devices/{id}/assign` | ผูกหรือยกเลิกการผูกกับรถ (แอดมินเท่านั้น) |
| DELETE | `/api/gps/devices/{id}` | ลบอุปกรณ์ (แอดมินเท่านั้น) |

ตัว ESP32 เองก็มี API `GET http://<IP ของ ESP32>/gps` คืนค่า JSON ตำแหน่งล่าสุด ไว้ทดสอบในวง WiFi เดียวกัน เว็บจริงไม่ได้ดึงจากตรงนี้ เพราะ ESP32 บนรถไม่มี IP สาธารณะ และเว็บ https เรียก http ในวงแลนไม่ได้

## การ Deploy

เซิร์ฟเวอร์จริงเป็น shared hosting อัปโหลดไฟล์ผ่าน FTP (สคริปต์อยู่ใน `_scripts/deployment/`) ไฟล์ `index.php` และ `.htaccess` ที่ root ของโปรเจกต์จะส่งทุก request ต่อไปยังโฟลเดอร์ `public/` ทำให้วางทั้งโปรเจกต์ไว้ใน web root ได้โดยตรง

## เอกสารประกอบ

- พจนานุกรมข้อมูล: `Data_Dictionary_YRU_Train_Tracking_System.docx`
