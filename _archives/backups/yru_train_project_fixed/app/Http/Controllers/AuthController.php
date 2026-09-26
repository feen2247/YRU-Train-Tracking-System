<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     */
    public function loginSubmit(Request $request)
    {
        $username = trim($request->input('username'));
        $password = trim($request->input('password'));

        // ตรวจสอบการจำกัดจำนวนครั้งการล็อกอิน
        $throttleKey = Str::lower($username) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'error_type' => 'too_many_attempts',
                'message' => 'คุณพยายามเข้าสู่ระบบผิดพลาดหลายครั้งเกินไป กรุณารอ ' . $seconds . ' วินาที'
            ], 429);
        }

        $user = User::where('username', $username)->first();

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        if (!$user) {
            RateLimiter::hit($throttleKey, 60); // ล็อคไว้ 1 นาที
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_found',
                'message' => 'ไม่พบรหัสผู้ใช้งานนี้ในระบบ'
            ], 422);
        }

        // ตรวจสอบรหัสผ่าน
        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'status' => 'error',
                'error_type' => 'wrong_password',
                'message' => 'รหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง'
            ], 422);
        }

        // ตรวจสอบสถานะบัญชี
        if ($user->status !== 'ใช้งาน' && strtolower($user->status) !== 'active') {
            return response()->json([
                'status' => 'error',
                'error_type' => 'account_suspended',
                'message' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'
            ], 403);
        }

        $isYruLogin = str_ends_with(strtolower($username), '@yru.ac.th');

        // ตรวจสอบการยืนยันอีเมลสำหรับผู้โดยสาร (ยกเว้น yru.ac.th สามารถเข้าสู่ระบบได้เลยแม้ยังไม่ยืนยัน)
        if (!$isYruLogin && $user->user_role === 'Passenger' && is_null($user->email_verified_at)) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_verified',
                'message' => 'กรุณายืนยันตัวตนด้วยรหัส OTP ทางอีเมลก่อยเข้าสู่ระบบ'
            ], 403);
        }

        Auth::login($user, $request->has('remember'));
        RateLimiter::clear($throttleKey);

        $redirectUrl = url('/home');
        if ($username === 'tracking1@yru.ac.th') {
            $redirectUrl = url('/tracking') . '?car_id=1';
        } elseif ($username === 'tracking2@yru.ac.th') {
            $redirectUrl = url('/tracking') . '?car_id=2';
        } elseif ($username === 'executive@yru.ac.th') {
            $redirectUrl = url('/executive-view');
        } elseif ($username === 'admin@yru.ac.th') {
            $redirectUrl = url('/admin-view');
        } else {
            // ดึงสิทธิ์/บทบาทจริงในระบบ
            $role = strtolower($user->user_role);
            if ($role === 'driver') {
                $redirectUrl = route('driver.dashboard');
            } elseif ($role === 'operator' || $role === 'staff') {
                $redirectUrl = route('operator.dashboard');
            } elseif ($role === 'administrator' || $role === 'admin') {
                $redirectUrl = url('/admin-view');
            } elseif ($role === 'executive') {
                $redirectUrl = url('/executive-view');
            } elseif ($role === 'passenger') {
                $redirectUrl = url('/home');
            }
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl
        ]);
    }

    /**
     * Handle Forgot Password Request
     */
    public function forgotPasswordRequest(Request $request)
    {
        $username = trim($request->input('username'));

        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบบัญชีผู้ใช้นี้ในระบบ'
            ], 404);
        }

        // สร้าง OTP 6 หลัก
        $otp = rand(100000, 999999);

        // บันทึกลงฐานข้อมูล โดยกำหนดเวลาหมดอายุไว้ที่ 5 นาที (ใช้ตาราง password_reset_tokens)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email], // ใช้ email สำหรับอ้างอิงจริง
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );

        // ส่งอีเมลจริงไปยังที่อยู่อีเมลของผู้ใช้ผ่าน Mail Facade
        try {
            Mail::raw(
                "สวัสดีครับ,\n\nรหัส OTP เพื่อยืนยันการตั้งรหัสผ่านใหม่สำหรับระบบ YRU EV Tracker ของคุณคือ:\n\n" . $otp . "\n\n(รหัส OTP นี้มีอายุการใช้งาน 5 นาที นับจากเวลาที่ขอ)\n\nหากคุณไม่ได้เป็นผู้ทำรายการนี้ กรุณาข้ามอีเมลนี้เพื่อความปลอดภัย", 
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('รหัส OTP สำหรับกู้คืนรหัสผ่าน [YRU EV Tracker]');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send reset OTP email to ' . $user->email . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถส่งรหัส OTP ไปยังอีเมลของท่านได้สำเร็จ กรุณาตรวจสอบที่อยู่อีเมลหรือลองใหม่อีกครั้ง (' . $e->getMessage() . ')'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'email' => $user->email,
            'message' => 'สร้างและส่งรหัส OTP สำเร็จ'
        ]);
    }
}
