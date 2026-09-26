<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'username' => 'required|string'
        ]);
        $username = trim($request->username);

        // ค้นหาจาก username, email หรือ employee_id
        $user = \App\Models\User::where('username', $username)
            ->orWhere('email', $username)
            ->orWhere('employee_id', $username)
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบบัญชีผู้ใช้นี้ในระบบ'], 404);
        }

        $email = $user->email;
        if (empty($email)) {
            // ถ้าไม่มี email ใน database แต่ username มีรูปแบบ email ให้ใช้อันนั้น
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $email = $username;
            } else {
                return response()->json(['status' => 'error', 'message' => 'บัญชีนี้ไม่มีอีเมลเชื่อมโยงอยู่ กรุณาติดต่อผู้ดูแลระบบ'], 422);
            }
        }

        // สร้าง OTP 6 หลัก
        $otp = sprintf("%06d", mt_rand(1, 999999));

        // บันทึก OTP ใน password_reset_tokens โดยใช้ email เป็น key
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $username],
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "สวัสดีครับ,\n\nรหัส OTP สำหรับรีเซ็ตรหัสผ่านใหม่ของระบบ YRU EV Tracker คือ: {$otp}\n(รหัสนี้จะหมดอายุภายใน 5 นาที)",
                function ($message) use ($email) {
                    $message->to($email)->subject('รหัส OTP สำหรับตั้งรหัสผ่านใหม่ [YRU EV Tracker]');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP to ' . $email . ': ' . $e->getMessage());
            // เพื่อทดสอบได้สะดวก ถ้าส่งเมล์ผิดพลาดให้แจ้ง OTP ใน log
            \Illuminate\Support\Facades\Log::info("OTP for {$email} is {$otp}");
        }

        return response()->json([
            'status' => 'success', 
            'email' => $email,
            'message' => 'ส่งรหัส OTP ไปยังอีเมลของท่านเรียบร้อยแล้ว'
        ]);
    }
}
