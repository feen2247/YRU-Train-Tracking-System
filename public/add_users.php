<?php
/**
 * add_users.php - เพิ่มผู้ใช้ทั้งหมดจากหน้า Admin เข้าฐานข้อมูลบนเซิร์ฟเวอร์
 * รหัสผ่าน = รหัสประจำตัว (employee_id) เช่น 69001, 69002, ...
 * Upload ไปที่ public/  แล้วเรียก http://406665014.student.yru.ac.th/add_users.php
 */

// --- DB Config ---
$host = '127.0.0.1';
$dbname = 'S406665014_db';
$dbuser = 'S406665014_db';
$dbpass = '406665014';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

// ตรวจสอบคอลัมน์ที่มีอยู่จริง
$cols = [];
$res = $pdo->query("DESCRIBE users");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $col) {
    $cols[] = $col['Field'];
}

// รหัสผ่านของแต่ละคน = รหัสประจำตัว (employee_id)

// รายชื่อผู้ใช้ทั้งหมดจากหน้า Admin
$users = [
    // พนักงานขับรถ + ผู้ดูระบบ (รหัสผ่าน = รหัสประจำตัว)
    ['user_id'=>'USR-69001','employee_id'=>'69001','prefix'=>'นาย','first_name'=>'มูฮัมมัด','last_name'=>'ซอและ',   'username'=>'muhammad',  'email'=>'muhammad@yru.ac.th',  'user_role'=>'Administrator'],
    ['user_id'=>'USR-69002','employee_id'=>'69002','prefix'=>'ดร.', 'first_name'=>'สมชาย',  'last_name'=>'เรียนดี', 'username'=>'somchai',   'email'=>'somchai@yru.ac.th',   'user_role'=>'Administrator'],
    ['user_id'=>'USR-69003','employee_id'=>'69003','prefix'=>'นาย','first_name'=>'อัสมี',   'last_name'=>'มูเล็ง',  'username'=>'asmee',     'email'=>'asmee@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69004','employee_id'=>'69004','prefix'=>'นาย','first_name'=>'อิรฟาน', 'last_name'=>'มะระ',    'username'=>'arfan',     'email'=>'arfan@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69005','employee_id'=>'69005','prefix'=>'นาย','first_name'=>'ซูฟิยาน','last_name'=>'มะโละ',   'username'=>'sufiyan',   'email'=>'sufiyan@yru.ac.th',   'user_role'=>'Driver'],
    ['user_id'=>'USR-69006','employee_id'=>'69006','prefix'=>'นาย','first_name'=>'อุสมาน', 'last_name'=>'สาแล',    'username'=>'usman',     'email'=>'usman@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69007','employee_id'=>'69007','prefix'=>'นาย','first_name'=>'บัดรี',   'last_name'=>'สาแล',    'username'=>'badri',     'email'=>'badri@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69008','employee_id'=>'69008','prefix'=>'นาย','first_name'=>'ดอรีก',  'last_name'=>'ล้อแมะ',  'username'=>'torik',     'email'=>'torik@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69009','employee_id'=>'69009','prefix'=>'นาย','first_name'=>'สมหวัง', 'last_name'=>'ใจดี',    'username'=>'somwang',   'email'=>'somwang@yru.ac.th',   'user_role'=>'Driver'],
    ['user_id'=>'USR-69010','employee_id'=>'69010','prefix'=>'นาย','first_name'=>'สมใจ',   'last_name'=>'ใจดี',    'username'=>'somjal',    'email'=>'somjal@yru.ac.th',    'user_role'=>'Driver'],
    ['user_id'=>'USR-69011','employee_id'=>'69011','prefix'=>'นาย','first_name'=>'กิตติ',   'last_name'=>'ตั้งใจ',   'username'=>'kitti',     'email'=>'kitti@yru.ac.th',     'user_role'=>'Driver'],
    ['user_id'=>'USR-69012','employee_id'=>'69012','prefix'=>'นาย','first_name'=>'รุสลัน', 'last_name'=>'สอเฮาะ',  'username'=>'ruslan',    'email'=>'ruslan@yru.ac.th',    'user_role'=>'Driver'],
    // นักศึกษา (username = รหัสนักศึกษา, รหัสผ่าน = รหัสนักศึกษา)
    ['user_id'=>'USR-406665035','employee_id'=>'406665035','prefix'=>'นางสาว','first_name'=>'พิชญา', 'last_name'=>'ชุมมิคสา','username'=>'406665035','email'=>'406665035@yru.ac.th','user_role'=>'Passenger'],
    ['user_id'=>'USR-406665025','employee_id'=>'406665025','prefix'=>'นางสาว','first_name'=>'วรนุช', 'last_name'=>'อาด๊า',   'username'=>'406665025','email'=>'406665025@yru.ac.th','user_role'=>'Passenger'],
];

$results = [];
foreach ($users as $u) {
    // รหัสผ่าน = รหัสประจำตัว เช่น 69001
    $userPassword = password_hash($u['employee_id'], PASSWORD_BCRYPT, ['cost' => 12]);

    // ตรวจสอบว่า user มีอยู่แล้วหรือไม่
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$u['username'], $u['email']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // อัปเดต password และ email_verified_at ให้ login ได้
        $updateParts = ['password = ?'];
        $params = [$userPassword];

        if (in_array('status', $cols)) {
            $updateParts[] = "status = 'ใช้งาน'";
        }
        if (in_array('email_verified_at', $cols)) {
            $updateParts[] = 'email_verified_at = NOW()';
        }
        if (in_array('email', $cols)) {
            $updateParts[] = 'email = ?';
            $params[] = $u['email'];
        }
        $params[] = $u['username'];
        $sql = 'UPDATE users SET ' . implode(', ', $updateParts) . ' WHERE username = ?';
        $pdo->prepare($sql)->execute($params);
        $results[] = ['action' => 'updated', 'username' => $u['username'], 'email' => $u['email']];
    } else {
        // สร้างคอลัมน์ที่จะ insert (เฉพาะที่มีในตาราง)
        $insertCols = ['user_id', 'username', 'password', 'user_role', 'created_at', 'updated_at'];
        $insertVals = [
            $u['user_id'],
            $u['username'],
            $userPassword,
            $u['user_role'],
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ];

        // เพิ่ม email_verified_at ถ้ามีคอลัมน์นี้ (เพื่อให้ login ได้เลยไม่ต้อง OTP)
        if (in_array('email_verified_at', $cols)) {
            $insertCols[] = 'email_verified_at';
            $insertVals[] = date('Y-m-d H:i:s');
        }
        if (in_array('status', $cols)) {
            $insertCols[] = 'status';
            $insertVals[] = 'ใช้งาน';
        }
        if (in_array('email', $cols)) {
            $insertCols[] = 'email';
            $insertVals[] = $u['email'];
        }
        if (in_array('name', $cols)) {
            $insertCols[] = 'name';
            $insertVals[] = $u['prefix'] . $u['first_name'] . ' ' . $u['last_name'];
        }
        if (in_array('employee_id', $cols)) {
            $insertCols[] = 'employee_id';
            $insertVals[] = $u['employee_id'];
        }
        if (in_array('first_name', $cols)) {
            $insertCols[] = 'first_name';
            $insertVals[] = $u['first_name'];
        }
        if (in_array('last_name', $cols)) {
            $insertCols[] = 'last_name';
            $insertVals[] = $u['last_name'];
        }
        if (in_array('prefix', $cols)) {
            $insertCols[] = 'prefix';
            $insertVals[] = $u['prefix'];
        }
        if (in_array('usage_rights', $cols)) {
            $insertCols[] = 'usage_rights';
            $insertVals[] = 'active';
        }

        $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
        $colStr = implode(',', array_map(fn($c) => "`$c`", $insertCols));
        $pdo->prepare("INSERT INTO users ($colStr) VALUES ($placeholders)")->execute($insertVals);
        $results[] = ['action' => 'inserted', 'username' => $u['username'], 'email' => $u['email']];
    }
}

// ยืนยันผลลัพธ์
$selectCols = ['user_id', 'username', 'user_role'];
if (in_array('email', $cols))            $selectCols[] = 'email';
if (in_array('status', $cols))           $selectCols[] = 'status';
if (in_array('email_verified_at', $cols)) $selectCols[] = 'email_verified_at';
if (in_array('created_at', $cols))       $selectCols[] = 'created_at';
$selectStr = implode(', ', array_map(fn($c) => "`$c`", $selectCols));
$allUsers = $pdo->query("SELECT $selectStr FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'db_columns' => $cols,
    'processed' => $results,
    'total_users_in_db' => count($allUsers),
    'all_users' => $allUsers,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
