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

// รายชื่อผู้ใช้ทั้งหมดจากระบบอย่างเป็นทางการ (ถูกต้อง 100% ไม่ซ้ำซ้อน)
$users = [
    // 1. ผู้ดูแลระบบ
    ['user_id'=>'USR-000001','employee_id'=>'69001','prefix'=>'นาย','first_name'=>'มูฮัมหมัด','last_name'=>'ซอและ',   'username'=>'muhammad',  'email'=>'muhammad@yru.ac.th',  'phone_number'=>'081-234-5678','user_role'=>'Administrator'],
    // 2. ผู้บริหาร
    ['user_id'=>'USR-000002','employee_id'=>'69002','prefix'=>'ดร.', 'first_name'=>'สมชาย',  'last_name'=>'เรียนดี', 'username'=>'somchai',   'email'=>'somchai@yru.ac.th',   'phone_number'=>'082-345-6789','user_role'=>'Executive'],
    // 3-12. พนักงานขับรถประจำรถ EV-01 ถึง EV-10
    ['user_id'=>'USR-000003','employee_id'=>'69003','prefix'=>'นาย','first_name'=>'อัสมี',   'last_name'=>'มูเล็ง',  'username'=>'asmee',     'email'=>'asmee@yru.ac.th',     'phone_number'=>'083-456-7890','user_role'=>'Driver'],
    ['user_id'=>'USR-000004','employee_id'=>'69004','prefix'=>'นาย','first_name'=>'อัรฟาน', 'last_name'=>'มะเระ',    'username'=>'arfan',     'email'=>'arfan@yru.ac.th',     'phone_number'=>'084-567-8901','user_role'=>'Driver'],
    ['user_id'=>'USR-000005','employee_id'=>'69005','prefix'=>'นาย','first_name'=>'ซูเฟียน', 'last_name'=>'มะโละ',   'username'=>'sufiyan',   'email'=>'sufiyan@yru.ac.th',   'phone_number'=>'085-678-9012','user_role'=>'Driver'],
    ['user_id'=>'USR-000006','employee_id'=>'69006','prefix'=>'นาย','first_name'=>'อุสมาน', 'last_name'=>'สาและ',   'username'=>'usman',     'email'=>'usman@yru.ac.th',     'phone_number'=>'086-789-0123','user_role'=>'Driver'],
    ['user_id'=>'USR-000007','employee_id'=>'69007','prefix'=>'นาย','first_name'=>'บัดรี',   'last_name'=>'สาและ',    'username'=>'badri',     'email'=>'badri@yru.ac.th',     'phone_number'=>'087-890-1234','user_role'=>'Driver'],
    ['user_id'=>'USR-000008','employee_id'=>'69008','prefix'=>'นาย','first_name'=>'ตอริก',  'last_name'=>'ลือแมะ',  'username'=>'torik',     'email'=>'torik@yru.ac.th',     'phone_number'=>'088-901-2345','user_role'=>'Driver'],
    ['user_id'=>'USR-000009','employee_id'=>'69009','prefix'=>'นาย','first_name'=>'สมหวัง', 'last_name'=>'ใจดี',    'username'=>'somwang',   'email'=>'somwang@yru.ac.th',   'phone_number'=>'089-012-3456','user_role'=>'Driver'],
    ['user_id'=>'USR-000010','employee_id'=>'69010','prefix'=>'นาย','first_name'=>'สมใจ',   'last_name'=>'ใจดี',    'username'=>'somjai',    'email'=>'somjai@yru.ac.th',    'phone_number'=>'090-123-4567','user_role'=>'Driver'],
    ['user_id'=>'USR-000011','employee_id'=>'69011','prefix'=>'นาย','first_name'=>'กิตติ',   'last_name'=>'ตั้งใจ',   'username'=>'kitti',     'email'=>'kitti@yru.ac.th',     'phone_number'=>'091-234-5678','user_role'=>'Driver'],
    ['user_id'=>'USR-000012','employee_id'=>'69012','prefix'=>'นาย','first_name'=>'รุสลัน', 'last_name'=>'สอเฮาะ',  'username'=>'ruslan',    'email'=>'ruslan@yru.ac.th',    'phone_number'=>'092-345-6789','user_role'=>'Driver'],
    // 13. ช่างซ่อมบำรุง
    ['user_id'=>'USR-000013','employee_id'=>'69013','prefix'=>'นาย','first_name'=>'ประสาน', 'last_name'=>'งานดี',    'username'=>'prasan',    'email'=>'prasan.g@yru.ac.th',  'phone_number'=>'093-456-7890','user_role'=>'Mechanic'],
    // 14. หัวหน้างานยานพาหนะ
    ['user_id'=>'USR-000014','employee_id'=>'69014','prefix'=>'นาย','first_name'=>'ฮาดี',   'last_name'=>'ลือแมะ',  'username'=>'hadee',     'email'=>'hadee@yru.ac.th',     'phone_number'=>'094-567-8901','user_role'=>'VehicleHead'],
    // 15-17. นักศึกษา
    ['user_id'=>'USR-000015','employee_id'=>'406665014','prefix'=>'นางสาว','first_name'=>'ทัศนีย์','last_name'=>'สาและ',  'username'=>'406665014','email'=>'406665014@yru.ac.th','phone_number'=>'0635497741','user_role'=>'Student'],
    ['user_id'=>'USR-000016','employee_id'=>'406665035','prefix'=>'นางสาว','first_name'=>'พิชญา', 'last_name'=>'ชุมมิคสา','username'=>'406665035','email'=>'406665035@yru.ac.th','phone_number'=>'0635497741','user_role'=>'Student'],
    ['user_id'=>'USR-000017','employee_id'=>'406665025','prefix'=>'นางสาว','first_name'=>'วรนุช', 'last_name'=>'อาดำ',   'username'=>'406665025','email'=>'406665025@yru.ac.th','phone_number'=>'0635497741','user_role'=>'Student'],
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
        // อัปเดตข้อมูลให้ตรงกับค่าจริง 100% (แก้ไขชื่อสะกดผิด, รหัสพนักงาน, เบอร์โทร, สิทธิ์)
        $updateParts = ['password = ?'];
        $params = [$userPassword];

        if (in_array('name', $cols)) {
            $updateParts[] = 'name = ?';
            $params[] = $u['prefix'] . $u['first_name'] . ' ' . $u['last_name'];
        }
        if (in_array('employee_id', $cols)) {
            $updateParts[] = 'employee_id = ?';
            $params[] = $u['employee_id'];
        }
        if (in_array('prefix', $cols)) {
            $updateParts[] = 'prefix = ?';
            $params[] = $u['prefix'];
        }
        if (in_array('first_name', $cols)) {
            $updateParts[] = 'first_name = ?';
            $params[] = $u['first_name'];
        }
        if (in_array('last_name', $cols)) {
            $updateParts[] = 'last_name = ?';
            $params[] = $u['last_name'];
        }
        if (in_array('phone_number', $cols) && !empty($u['phone_number'])) {
            $updateParts[] = 'phone_number = ?';
            $params[] = $u['phone_number'];
        }
        if (in_array('user_role', $cols)) {
            $updateParts[] = 'user_role = ?';
            $params[] = $u['user_role'];
        }
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
        $results[] = ['action' => 'updated', 'username' => $u['username'], 'name' => $u['prefix'] . $u['first_name'] . ' ' . $u['last_name'], 'employee_id' => $u['employee_id']];
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
