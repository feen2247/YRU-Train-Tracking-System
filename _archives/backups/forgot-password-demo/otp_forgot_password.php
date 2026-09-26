<?php
/**
 * otp_forgot_password.php
 * Form to request a 6-digit OTP code for password recovery.
 */

session_start();
require_once 'db_connect.php';

// If you are using Composer, you would require autoload:
// require 'vendor/autoload.php';
// Or manual require of PHPMailer classes

$message = '';
$message_type = '';
$open_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "รูปแบบอีเมลไม่ถูกต้อง";
        $message_type = "error";
    } else {
        try {
            // 1. Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $message = "ไม่พบอีเมลนี้ในระบบ";
                $message_type = "error";
            } else {
                // 2. Rate Limiting: Prevent requesting a new OTP too quickly (60 seconds limit)
                $stmt = $pdo->prepare("SELECT created_at FROM otp_resets WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $existing_request = $stmt->fetch();

                if ($existing_request) {
                    $last_request = strtotime($existing_request['created_at']);
                    $time_diff = time() - $last_request;
                    $cooldown = 60;

                    if ($time_diff < $cooldown) {
                        $wait = $cooldown - $time_diff;
                        throw new Exception("กรุณารอ $wait วินาทีก่อนขอรหัส OTP ใหม่อีกครั้ง (Rate Limited)");
                    }
                }

                // 3. Generation: Cryptographically Secure 6-digit OTP
                // Use random_int() which is cryptographically secure. Avoid rand() or mt_rand() for security keys.
                $otp_code = (string) random_int(100000, 999999);

                // Set expiration time to 5 minutes from now
                $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // 4. Save to Database
                // Delete existing OTP record if any
                $stmt = $pdo->prepare("DELETE FROM otp_resets WHERE email = ?");
                $stmt->execute([$email]);

                // Insert new OTP with attempts = 0
                $stmt = $pdo->prepare("INSERT INTO otp_resets (email, otp_code, expires_at, attempts) VALUES (?, ?, ?, 0)");
                $stmt->execute([$email, $otp_code, $expires_at]);

                // Store email in session to verify in otp_verify.php
                $_SESSION['reset_email'] = $email;

                // 5. Send Email containing OTP
                // --- PHPMailer Code Block (Example) ---
                /*
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'your_email@gmail.com';
                    $mail->Password   = 'your_app_password';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('no-reply@domain.com', 'YRU Train Tracking');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset OTP - YRU Train Tracking';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                            <h2 style='color: #4F46E5; text-align: center; margin-bottom: 20px;'>รหัส OTP สำหรับกู้คืนรหัสผ่าน</h2>
                            <p style='font-size: 16px; color: #1e293b;'>สวัสดีครับ/ค่ะ,</p>
                            <p style='font-size: 14px; color: #475569; line-height: 1.6;'>คุณได้ทำการร้องขอรหัสผ่านใหม่ โปรดใช้รหัส OTP ด้านล่างเพื่อยืนยันความเป็นเจ้าของบัญชีของคุณ:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='font-family: monospace; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #4F46E5; background-color: #EEF2FF; padding: 12px 30px; border-radius: 8px; border: 1px dashed #6366F1; display: inline-block;'>{$otp_code}</span>
                            </div>
                            <p style='font-size: 14px; color: #dc2626; font-weight: bold;'>รหัส OTP นี้จะมีอายุใช้งานเพียง 5 นาทีเท่านั้น</p>
                            <p style='font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: 20px;'>หากคุณไม่ได้ร้องขอเปลี่ยนรหัสผ่าน สามารถละทิ้งอีเมลนี้ได้อย่างปลอดภัย</p>
                        </div>
                    ";

                    $mail->send();
                    
                    // Redirect after successfully sending email
                    header("Location: otp_verify.php");
                    exit;

                } catch (Exception $e) {
                    $message = "ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}";
                    $message_type = "error";
                }
                */

                // Parse email domain
                $parts = explode('@', $email);
                $domain = strtolower(end($parts));

                if ($domain === 'gmail.com' || $domain === 'yru.ac.th') {
                    // YRU uses Google Workspace
                    $open_url = 'https://mail.google.com';
                } elseif (in_array($domain, ['outlook.com', 'hotmail.com', 'live.com'])) {
                    $open_url = 'https://outlook.live.com';
                }

                $_SESSION['demo_otp'] = $otp_code;

                if (!empty($open_url)) {
                    $message = "<strong>[DEMO MODE]</strong> ส่งรหัส OTP เรียบร้อยแล้ว!<br>รหัส OTP ของคุณคือ: <span style='font-size:20px; font-weight:bold; color:#facc15;'>$otp_code</span> (จะหมดอายุภายใน 5 นาที)<br><br>ระบบเปิดกล่องจดหมายของคุณในหน้าต่างใหม่เรียบร้อยแล้ว หรือคุณสามารถ <a href='$open_url' target='_blank' style='color:#fff; text-decoration:underline; font-weight:bold;'>คลิกที่นี่เพื่อตรวจสอบอีเมลของคุณ</a>";
                } else {
                    $message = "ระบบได้ส่ง OTP ไปยังอีเมลของคุณแล้ว กรุณาไปเช็คในกล่องจดหมาย<br><br><strong>[DEMO MODE]</strong> รหัส OTP ของคุณคือ: <span style='font-size:20px; font-weight:bold; color:#facc15;'>$otp_code</span>";
                }
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
    <title>กู้คืนรหัสผ่านด้วย OTP - ขอรหัสผ่าน</title>
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
    <h2>กู้คืนด้วย OTP</h2>
    <p class="subtitle">ระบุอีเมลของคุณเพื่อรับรหัส OTP 6 หลักสำหรับรีเซ็ตรหัสผ่าน</p>

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
        <button type="submit">ส่งรหัส OTP</button>
    </form>

    <div class="footer-links">
        <a href="otp_verify.php">หน้ายืนยัน OTP</a>
        <a href="forgot_password.php">เปลี่ยนวิธีใช้ลิงก์</a>
    </div>
</div>

<?php if (!empty($open_url)): ?>
<script>
    // Automatically open the email provider in a new tab
    window.open('<?php echo $open_url; ?>', '_blank');
</script>
<?php endif; ?>

</body>
</html>
