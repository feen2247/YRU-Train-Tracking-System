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

## การ Deploy

เซิร์ฟเวอร์จริงเป็น shared hosting อัปโหลดไฟล์ผ่าน FTP (สคริปต์อยู่ใน `_scripts/deployment/`) ไฟล์ `index.php` และ `.htaccess` ที่ root ของโปรเจกต์จะส่งทุก request ต่อไปยังโฟลเดอร์ `public/` ทำให้วางทั้งโปรเจกต์ไว้ใน web root ได้โดยตรง

## เอกสารประกอบ

- พจนานุกรมข้อมูล: `Data_Dictionary_YRU_Train_Tracking_System.docx`
