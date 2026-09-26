<?php
/**
 * otp_verify.php
 * Handles OTP verification, brute-force protection, and password resetting.
 */

session_start();
require_once 'db_connect.php';

$message = '';
$message_type = '';
$email = $_SESSION['reset_email'] ?? '';
$demo_otp = $_SESSION['demo_otp'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $otp_input = trim($_POST['otp']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic Inputs Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "รูปแบบอีเมลไม่ถูกต้อง";
        $message_type = "error";
    } elseif (strlen($otp_input) !== 6 || !is_numeric($otp_input)) {
        $message = "รหัส OTP ต้องเป็นตัวเลข 6 หลัก";
        $message_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
        $message_type = "error";
    } else {
        try {
            // Retrieve OTP record for the email
            $stmt = $pdo->prepare("SELECT otp_code, expires_at, attempts FROM otp_resets WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $otp_record = $stmt->fetch();

            if (!$otp_record) {
                $message = "ไม่พบคำขอรับรหัส OTP สำหรับอีเมลนี้ หรือรหัสถูกยกเลิกแล้ว";
                $message_type = "error";
            } else {
                $max_attempts = 3;
                $current_attempts = (int) $otp_record['attempts'];

                // 2. Guess Protection: Check if already locked out
                if ($current_attempts >= $max_attempts) {
                    // Delete the OTP immediately to prevent further attempts
                    $stmt = $pdo->prepare("DELETE FROM otp_resets WHERE email = ?");
                    $stmt->execute([$email]);
                    throw new Exception("คุณกรอกรหัสผิดเกิน $max_attempts ครั้ง รหัส OTP นี้ถูกยกเลิกแล้ว กรุณาขอรหัสใหม่");
                }

                // 3. Expiration Check
                $expires_at = strtotime($otp_record['expires_at']);
                if ($expires_at <= time()) {
                    // Delete expired OTP
                    $stmt = $pdo->prepare("DELETE FROM otp_resets WHERE email = ?");
                    $stmt->execute([$email]);
                    throw new Exception("รหัส OTP หมดอายุการใช้งานแล้ว (รหัส OTP มีอายุ 5 นาที) กรุณาขอรหัสใหม่");
                }

                // 4. Verify OTP Code
                if ($otp_input !== $otp_record['otp_code']) {
                    // Increment incorrect attempts count
                    $new_attempts = $current_attempts + 1;
                    
                    if ($new_attempts >= $max_attempts) {
                        // Max attempts reached, delete the record
                        $stmt = $pdo->prepare("DELETE FROM otp_resets WHERE email = ?");
                        $stmt->execute([$email]);
                        $message = "คุณกรอกรหัสผิดเกิน $max_attempts ครั้ง รหัส OTP นี้ถูกยกเลิกแล้ว กรุณาขอรหัสใหม่";
                    } else {
                        // Update attempts count in database
                        $stmt = $pdo->prepare("UPDATE otp_resets SET attempts = ? WHERE email = ?");
                        $stmt->execute([$new_attempts, $email]);
                        $remaining = $max_attempts - $new_attempts;
                        $message = "รหัส OTP ไม่ถูกต้อง! คุณสามารถลองใหม่ได้อีก $remaining ครั้ง";
                    }
                    $message_type = "error";
                } else {
                    // OTP is valid! Perform Password Reset

                    // Start transaction
                    $pdo->beginTransaction();

                    // Hash new password using bcrypt
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Update password in users table
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->execute([$hashed_password, $email]);

                    // 5. Cleanup: Delete used OTP immediately
                    $stmt = $pdo->prepare("DELETE FROM otp_resets WHERE email = ?");
                    $stmt->execute([$email]);

                    $pdo->commit();

                    // Clear session demo variables
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['demo_otp']);

                    $message = "กู้คืนและเปลี่ยนรหัสผ่านเรียบร้อยแล้ว! สามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที";
                    $message_type = "success";
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = $e->getMessage();
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
    <title>ยืนยันรหัส OTP และเปลี่ยนรหัสผ่าน</title>
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
            margin-bottom: 18px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        input[type="email"],
        input[type="text"],
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

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        .otp-input {
            font-family: monospace;
            letter-spacing: 4px;
            font-size: 20px;
            text-align: center;
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
            display: flex;
            justify-content: space-between;
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
    <h2>ยืนยันรหัส OTP</h2>
    <p class="subtitle">กรอกรหัส OTP 6 หลักที่ส่งไปที่อีเมลของคุณ และตั้งค่ารหัสผ่านใหม่</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php 
    // Display demo helper if it exists in session
    if (!empty($demo_otp) && empty($message)): 
    ?>
        <div class="alert alert-success" style="text-align: center;">
            <strong>[DEMO HELPER]</strong><br>
            รหัส OTP ของคุณคือ: <span style="font-size: 18px; font-weight: bold; color: #facc15;"><?php echo htmlspecialchars($demo_otp); ?></span>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="email">อีเมลผู้ใช้งาน</label>
            <input type="email" id="email" name="email" required placeholder="example@yru.ac.th" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="form-group">
            <label for="otp">รหัส OTP (6 หลัก)</label>
            <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}" placeholder="------" class="otp-input" autocomplete="one-time-code">
        </div>
        <div class="form-group">
            <label for="password">รหัสผ่านใหม่</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="รหัสผ่านอย่างน้อย 8 ตัวอักษร">
        </div>
        <div class="form-group">
            <label for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="ป้อนรหัสผ่านใหม่อีกครั้ง">
        </div>
        <button type="submit">ยืนยันและรีเซ็ตรหัสผ่าน</button>
    </form>

    <div class="footer-links">
        <a href="otp_forgot_password.php">ขอรหัส OTP ใหม่</a>
        <a href="forgot_password.php">เปลี่ยนวิธีใช้ลิงก์</a>
    </div>
</div>

</body>
</html>
