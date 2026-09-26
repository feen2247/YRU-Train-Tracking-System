<?php
/**
 * reset_password.php
 * Handles validating the reset token and updating the password.
 */

session_start();
require_once 'db_connect.php';

$message = '';
$message_type = ''; // 'success' or 'error'
$token_valid = false;
$email = '';
$raw_token = '';

// 1. Get and Validate token from URL
if (isset($_GET['token'])) {
    $raw_token = $_GET['token'];
    
    // Hash the raw token from URL to match the DB hash (SHA-256)
    // This prevents database leak token compromises
    $token_hash = hash('sha256', $raw_token);

    try {
        // Query to check if the token exists and is not expired
        // expires_at must be in the future (greater than current datetime)
        $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token_hash = ? LIMIT 1");
        $stmt->execute([$token_hash]);
        $reset_request = $stmt->fetch();

        if ($reset_request) {
            // Double check expiry in PHP to ensure timezone consistency
            $expires_at = strtotime($reset_request['expires_at']);
            if ($expires_at > time()) {
                $token_valid = true;
                $email = $reset_request['email'];
            } else {
                $message = "ลิงก์นี้หมดอายุการใช้งานแล้ว (หมดอายุเมื่อ: " . $reset_request['expires_at'] . ")";
                $message_type = "error";
            }
        } else {
            $message = "ลิงก์ไม่ถูกต้อง หรือถูกใช้ไปแล้ว";
            $message_type = "error";
        }
    } catch (PDOException $e) {
        $message = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
        $message_type = "error";
    }
} else {
    $message = "ไม่พบรหัสอ้างอิงโทเค็น (Token Missing)";
    $message_type = "error";
}

// 2. Handle Form Submission to update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (strlen($password) < 8) {
        $message = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
        $message_type = "error";
    } else {
        try {
            // Begin Transaction to ensure both update and delete succeed
            $pdo->beginTransaction();

            // Secure Hashing using BCRYPT (PHP's built-in standard)
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Update user password in the users table
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);

            // Immediately delete token to prevent reuse (crucial security practice)
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $pdo->commit();

            $message = "เปลี่ยนรหัสผ่านของคุณเรียบร้อยแล้ว! สามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที";
            $message_type = "success";
            $token_valid = false; // Disable form display after success
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "ไม่สามารถบันทึกรหัสผ่านใหม่ได้: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่ารหัสผ่านใหม่ (Reset Password)</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.1);
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success-color: #10b981;
            --error-color: #ef4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 24px;
            color: #fff;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: var(--primary-hover);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
            line-height: 1.5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid var(--success-color);
            color: #a7f3d0;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--error-color);
            color: #fca5a5;
        }

        .footer-links {
            margin-top: 25px;
            font-size: 14px;
        }

        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>ตั้งรหัสผ่านใหม่</h2>
    <p class="subtitle">ระบุรหัสผ่านใหม่สำหรับบัญชี: <strong style="color: #fff;"><?php echo htmlspecialchars($email); ?></strong></p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($token_valid): ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="password">รหัสผ่านใหม่</label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="รหัสผ่านอย่างน้อย 8 ตัวอักษร">
            </div>
            <div class="form-group">
                <label for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="ป้อนรหัสผ่านใหม่อีกครั้ง">
            </div>
            <button type="submit">บันทึกรหัสผ่านใหม่</button>
        </form>
    <?php endif; ?>

    <div class="footer-links">
        <a href="forgot_password.php">กลับหน้าลืมรหัสผ่าน</a>
    </div>
</div>

</body>
</html>
