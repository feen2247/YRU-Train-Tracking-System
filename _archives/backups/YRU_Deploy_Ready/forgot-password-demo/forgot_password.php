<?php
/**
 * forgot_password.php
 * Form and logic to request a password reset link.
 */

// Start session to use for feedback messages and session-based rate limiting
session_start();

// Include database connection
require_once 'db_connect.php';

// If you are using Composer, you would require autoload:
// require 'vendor/autoload.php';
// Or if you download PHPMailer manually:
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// require 'path/to/PHPMailer/src/Exception.php';
// require 'path/to/PHPMailer/src/PHPMailer.php';
// require 'path/to/PHPMailer/src/SMTP.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "รูปแบบอีเมลไม่ถูกต้อง";
        $message_type = "error";
    } else {
        try {
            // 1. Check if the user exists in the database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Security Tip: To prevent email enumeration (guessing registered emails), 
                // you can show a generic message. However, for admin panel ease of use, 
                // we show "Email not found" or handle it gracefully.
                $message = "ไม่พบอีเมลนี้ในระบบ";
                $message_type = "error";
            } else {
                // 2. Rate Limiting: Prevent spamming by checking the database for recent requests.
                // We check if a token was created for this email in the last 60 seconds.
                $stmt = $pdo->prepare("SELECT created_at FROM password_resets WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $existing_request = $stmt->fetch();

                if ($existing_request) {
                    $last_request_time = strtotime($existing_request['created_at']);
                    $time_elapsed = time() - $last_request_time;
                    $cooldown = 60; // 60 seconds cooldown

                    if ($time_elapsed < $cooldown) {
                        $wait_time = $cooldown - $time_elapsed;
                        throw new Exception("กรุณารอ $wait_time วินาทีก่อนร้องขอรหัสผ่านใหม่อีกครั้ง (Rate Limited)");
                    }
                }

                // 3. Generate Secure Random Token
                // bin2hex(random_bytes(32)) generates a 64-character hex string.
                // random_bytes() is cryptographically secure.
                $raw_token = bin2hex(random_bytes(32));

                // Hash the token before storing it to prevent database leakage exploits
                $token_hash = hash('sha256', $raw_token);

                // Set token expiration (e.g., 30 minutes from now)
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                // 4. Save to Database
                // Delete any existing token for this email first
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);

                // Insert the new token hash
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token_hash, $expires_at]);

                // 5. Send Email with Reset Link
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $raw_token;

                // --- PHPMailer Implementation Example ---
                /*
                $mail = new PHPMailer(true);
                try {
                    // SMTP configuration
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';         // Set your SMTP server
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'your_email@gmail.com';   // Your email username
                    $mail->Password   = 'your_app_password';      // Your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom('no-reply@domain.com', 'YRU Train Tracking');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset Your Password - YRU Train Tracking';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <h2 style='color: #4F46E5; text-align: center;'>รีเซ็ตรหัสผ่านของคุณ</h2>
                            <p>สวัสดีครับ/ค่ะ,</p>
                            <p>คุณได้รับอีเมลนี้เนื่องจากมีการร้องขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณในระบบ YRU Train Tracking System</p>
                            <p>กรุณาคลิกปุ่มด้านล่างเพื่อดำเนินการตั้งรหัสผ่านใหม่ ลิงก์นี้จะหมดอายุภายใน 30 นาที:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$reset_link}' style='background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>รีเซ็ตรหัสผ่าน</a>
                            </div>
                            <p style='color: #ef4444;'>หากคุณไม่ได้เป็นผู้ร้องขอ โปรดเพิกเฉยต่ออีเมลนี้ รหัสผ่านเดิมของคุณจะไม่เปลี่ยนแปลง</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='font-size: 12px; color: #777;'>หากปุ่มใช้งานไม่ได้ คุณสามารถคัดลอกลิงก์ด้านล่างไปเปิดบนเบราว์เซอร์ได้โดยตรง:<br>{$reset_link}</p>
                        </div>
                    ";

                    $mail->send();
                    $message = "ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณเรียบร้อยแล้ว กรุณาตรวจสอบกล่องข้อความ";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}";
                    $message_type = "error";
                }
                */

                // For Demonstration purposes without configuring SMTP server, we will output the link directly
                $message = "<strong>[DEMO MODE]</strong> ลิงก์รีเซ็ตรหัสผ่านถูกสร้างสำเร็จ (ในระบบจริงจะส่งเข้าอีเมล):<br><a href='$reset_link' style='color:#fff; text-decoration:underline;'>$reset_link</a>";
                $message_type = "success";
            }
        } catch (Exception $e) {
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
    <title>ลืมรหัสผ่าน (Forgot Password)</title>
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

        input[type="email"] {
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

        input[type="email"]:focus {
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
    <h2>ลืมรหัสผ่าน</h2>
    <p class="subtitle">ระบุอีเมลที่คุณใช้สมัครสมาชิก เพื่อรับลิงก์สำหรับเปลี่ยนรหัสผ่านใหม่</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="email">อีเมลผู้ใช้งาน</label>
            <input type="email" id="email" name="email" required placeholder="example@yru.ac.th" autocomplete="email">
        </div>
        <button type="submit">ส่งลิงก์รีเซ็ต</button>
    </form>

    <div class="footer-links">
        <a href="#">กลับหน้าเข้าสู่ระบบ</a>
    </div>
</div>

</body>
</html>
