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

        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->orWhere('employee_id', $username)
            ->first();

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

        // ตรวจสอบสถานะบัญชี (รองรับทุกรูปแบบ: ใช้งาน, active, Active, ปกติ, normal, NULL, Suspended)
        $statusLower = strtolower(trim($user->status ?? ''));
        $rightsLower = strtolower(trim($user->usage_rights ?? ''));
        $isActive = in_array($statusLower, ['ใช้งาน', 'active', 'ใชงาน', 'ปกติ', 'normal', '']) 
                 || in_array($rightsLower, ['active', 'ใช้งาน', 'ปกติ', 'suspended', '']) 
                 || is_null($user->status);
        if (!$isActive) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'account_suspended',
                'message' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'
            ], 403);
        }

        $isYruLogin = str_ends_with(strtolower($username), '@yru.ac.th');
        $roleLower = strtolower(trim($user->user_role ?? ''));
        $roleNotPassenger = !in_array($roleLower, ['passenger', 'student', 'นักศึกษา', 'ผู้โดยสาร']);

        // ตรวจสอบการยืนยันอีเมลสำหรับผู้โดยสาร (ยกเว้น yru.ac.th หรือ role ที่ไม่ใช่ Passenger/Student ข้ามได้เลย)
        if (!$isYruLogin && !$roleNotPassenger && is_null($user->email_verified_at)) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_verified',
                'message' => 'กรุณายืนยันตัวตนด้วยรหัส OTP ทางอีเมลก่อนเข้าสู่ระบบ'
            ], 403);
        }

        Auth::login($user, $request->has('remember'));
        RateLimiter::clear($throttleKey);

        // Redirect based on role
        $role = strtolower(trim($user->user_role ?? ''));
        $redirectUrl = url('/home');

        $userKeyEmail = strtolower($user->email ?? '');
        $userKeyName = strtolower($user->username ?? '');
        $userKeyEmpId = strtolower($user->employee_id ?? '');
        $userName = trim($user->name ?? '');
        $userId = (string)($user->id ?? $user->user_id ?? '');

        // 1. Check dynamic driver assignments from Cache (saved when Admin edits vehicle)
        $cachedAssignments = \Illuminate\Support\Facades\Cache::get('driver_vehicle_assignments', []);
        $dynamicCar = null;

        foreach ($cachedAssignments as $cCode => $info) {
            $dId = $info['driver_id'] ?? '';
            $dName = $info['driver_name'] ?? '';
            if (
                (!empty($dId) && (strtolower($dId) === $userKeyEmpId || strtolower($dId) === $userKeyEmail || strtolower($dId) === strtolower($userId))) ||
                (!empty($dName) && ($dName === $userName || str_contains($dName, $userName) || (!empty($userName) && str_contains($userName, $dName)))) ||
                (!empty($dName) && !empty($userKeyEmail) && str_contains($userKeyEmail, explode('@', strtolower($dName))[0]))
            ) {
                $dynamicCar = $cCode;
                break;
            }
        }

        $defaultDriverCarMap = [
            'asmee' => 'EV-01', 'asmee@yru.ac.th' => 'EV-01', '69003' => 'EV-01',
            'arfan' => 'EV-02', 'arfan@yru.ac.th' => 'EV-02', '69004' => 'EV-02',
            'sufiyan' => 'EV-03', 'sufiyan@yru.ac.th' => 'EV-03', '69005' => 'EV-03',
            'usman' => 'EV-04', 'usman@yru.ac.th' => 'EV-04', '69006' => 'EV-04',
            'badri' => 'EV-05', 'badri@yru.ac.th' => 'EV-05', '69007' => 'EV-05',
            'torik' => 'EV-06', 'torik@yru.ac.th' => 'EV-06', '69008' => 'EV-06',
            'somwang' => 'EV-07', 'somwang@yru.ac.th' => 'EV-07', '69009' => 'EV-07',
            'somjai' => 'EV-08', 'somjai@yru.ac.th' => 'EV-08', 'somjal@yru.ac.th' => 'EV-08', '69010' => 'EV-08',
            'kitti' => 'EV-09', 'kitti@yru.ac.th' => 'EV-09', '69011' => 'EV-09',
            'ruslan' => 'EV-10', 'ruslan@yru.ac.th' => 'EV-10', '69012' => 'EV-10',
        ];

        $fallbackCar = $defaultDriverCarMap[$userKeyEmail] ?? ($defaultDriverCarMap[$userKeyName] ?? ($defaultDriverCarMap[$userKeyEmpId] ?? 'EV-01'));
        $assignedCar = $dynamicCar ?: $fallbackCar;

        if ($role === 'driver' || $role === 'พนักงานขับรถ' || !empty($dynamicCar) || isset($defaultDriverCarMap[$userKeyEmail]) || isset($defaultDriverCarMap[$userKeyName]) || isset($defaultDriverCarMap[$userKeyEmpId])) {
            $redirectUrl = url('/tracking?car=' . $assignedCar);
        } elseif ($role === 'operator' || $role === 'staff' || $role === 'ผู้ควบคุม') {
            $redirectUrl = url('/tracking');
        } elseif ($role === 'administrator' || $role === 'admin' || $role === 'ผู้ดูแลระบบ') {
            $redirectUrl = url('/admin-view');
        } elseif ($role === 'executive' || $role === 'ผู้บริหาร') {
            $redirectUrl = url('/executive-view');
        } elseif ($role === 'passenger' || $role === 'student' || $role === 'นักศึกษา' || $role === 'ผู้โดยสาร') {
            $redirectUrl = url('/home');
        } else {
            $redirectUrl = url('/home');
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl,
            'user' => [
                'user_id' => $user->employee_id ?: ('USR-' . str_pad($user->id, 6, '0', STR_PAD_LEFT)),
                'emp_id' => $user->employee_id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->user_role
            ]
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

        // ส่งอีเมลจริงผ่าน Mail Facade (SMTP)
        try {
            Mail::raw(
                "สวัสดีครับ,\n\nรหัส OTP เพื่อยืนยันการตั้งรหัสผ่านใหม่สำหรับระบบ YRU EV Tracker ของคุณคือ:\n\n" . $otp . "\n\n(รหัส OTP นี้มีอายุการใช้งาน 5 นาที นับจากเวลาที่ขอ)\n\nหากคุณไม่ได้เป็นผู้ทำรายการนี้ กรุณาข้ามอีเมลนี้เพื่อความปลอดภัย",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('รหัส OTP สำหรับกู้คืนรหัสผ่าน [YRU EV Tracker]');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send reset OTP email to ' . $user->email . ': ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'email' => $user->email,
            'otp' => $otp,
            'message' => 'สร้างและส่งรหัส OTP สำเร็จ'
        ]);
    }
}
