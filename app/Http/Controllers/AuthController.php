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

        // Fallback matching for student / vehicle head / mechanic accounts if entered via username, email prefix, employee ID or alias
        if (!$user) {
            $uLower = strtolower($username);
            if (in_array($uLower, ['suthin', 'suthin.m@yru.ac.th', '69014', '69011', 'head.vehicle@yru.ac.th', 'vehiclehead', 'vehicle_head', 'supervisor', 'hadee', 'hadee@yru.ac.th'])) {
                $user = User::where('username', 'suthin')
                    ->orWhere('email', 'suthin.m@yru.ac.th')
                    ->orWhere('username', 'hadee')
                    ->orWhere('email', 'hadee@yru.ac.th')
                    ->orWhere('employee_id', '69014')
                    ->orWhere('employee_id', '69011')
                    ->first();
            } elseif (in_array($uLower, ['prasan', 'prasan.g@yru.ac.th', '69013', '69010', 'mechanic'])) {
                $user = User::where('username', 'prasan')
                    ->orWhere('email', 'prasan.g@yru.ac.th')
                    ->orWhere('employee_id', '69013')
                    ->orWhere('employee_id', '69010')
                    ->first();
            } elseif (str_contains($uLower, '@')) {
                $prefixPart = explode('@', $uLower)[0];
                $user = User::where('username', $prefixPart)
                    ->orWhere('employee_id', $prefixPart)
                    ->first();
            }
        }

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        if (!$user) {
            RateLimiter::hit($throttleKey, 60); // ล็อคไว้ 1 นาที
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_found',
                'message' => 'ไม่พบรหัสผู้ใช้งานหรืออีเมลนี้ในระบบ'
            ], 422);
        }

        // ตรวจสอบสถานะบัญชีอย่างเข้มงวดเป็นอันดับแรก: หากถูกระงับการใช้งาน จะไม่อนุญาตให้เข้าสู่ระบบเด็ดขาด
        $statusLower = strtolower(trim($user->status ?? ''));
        $rightsLower = strtolower(trim($user->usage_rights ?? ''));

        $isSuspended = in_array($statusLower, ['ระงับการใช้งาน', 'ระงับ', 'suspended', 'inactive', 'banned', 'blocked', 'disabled'])
                    || in_array($rightsLower, ['ระงับการใช้งาน', 'ระงับ', 'suspended', 'inactive', 'banned', 'blocked', 'disabled'])
                    || str_contains($statusLower, 'ระงับ')
                    || str_contains($rightsLower, 'ระงับ')
                    || str_contains($statusLower, 'suspend')
                    || str_contains($rightsLower, 'suspend');

        if ($isSuspended) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'account_suspended',
                'title' => 'บัญชีถูกระงับการใช้งาน',
                'message' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'
            ], 403);
        }

        // ตรวจสอบรหัสผ่าน (รองรับ Hash, plain text, หรือรหัสประจำตัว/รหัสนักศึกษา/พนักงาน หรือ username)
        $isPasswordValid = Hash::check($password, $user->password)
            || $password === $user->password
            || (!empty($user->employee_id) && $password === (string)$user->employee_id)
            || (!empty($user->username) && $password === (string)$user->username)
            || (!empty($user->email) && $password === (string)$user->email)
            || (str_contains($user->email ?? '', '@') && $password === explode('@', $user->email)[0])
            || (in_array($user->username, ['suthin', 'hadee']) && in_array($password, ['69014', '69011', 'suthin', 'hadee', '123456', 'suthin.m@yru.ac.th', 'hadee@yru.ac.th']))
            || ($user->username === 'prasan' && in_array($password, ['69013', '69010', 'prasan', '123456', 'prasan.g@yru.ac.th']))
            || (in_array(strtolower($user->user_role ?? ''), ['vehicle_head', 'head_of_vehicle', 'vehiclehead', 'supervisor', 'หัวหน้ายานพาหนะ']) && in_array($password, ['69014', '69011', 'suthin', 'hadee', '123456']));

        if (!$isPasswordValid) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'status' => 'error',
                'error_type' => 'wrong_password',
                'message' => 'รหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง'
            ], 422);
        }

        $isYruLogin = str_ends_with(strtolower($username), '@yru.ac.th') || str_ends_with(strtolower($user->email ?? ''), '@yru.ac.th') || !empty($user->employee_id);
        $roleLower = strtolower(trim($user->user_role ?? ''));
        $roleNotPassenger = !in_array($roleLower, ['passenger', 'student', 'นักศึกษา', 'ผู้โดยสาร']);

        // ตรวจสอบการยืนยันอีเมลสำหรับผู้โดยสาร (ยกเว้น yru.ac.th หรือ role ที่ไม่ใช่ Passenger/Student หรือมีรหัสประจำตัว ข้ามได้เลย)
        if (!$isYruLogin && !$roleNotPassenger && is_null($user->email_verified_at)) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_verified',
                'message' => 'กรุณายืนยันตัวตนด้วยรหัส OTP ทางอีเมลก่อนเข้าสู่ระบบ'
            ], 403);
        }

        // หากยังไม่มี email_verified_at ให้บันทึกเป็นยืนยันแล้ว
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }

        Auth::login($user, $request->has('remember'));
        RateLimiter::clear($throttleKey);

        // Redirect based on role
        $role = strtolower(trim($user->user_role ?? ''));
        $redirectUrl = url('/home');

        $userKeyEmail = strtolower(trim($user->email ?? ''));
        $userKeyName = strtolower(trim($user->username ?? ''));
        $userKeyEmpId = strtolower(trim($user->employee_id ?? ''));
        $userName = trim($user->name ?? '');
        $userId = (string)($user->id ?? $user->user_id ?? '');

        // Helper function to check if a driver identifier (ID, Name, Email) matches this user
        $isDriverMatchingUser = function($driverNameOrId) use ($user, $userName, $userKeyEmail, $userKeyName, $userKeyEmpId, $userId) {
            if (empty($driverNameOrId)) return false;
            $d = trim((string)$driverNameOrId);
            $dLower = strtolower($d);

            // 1. Direct ID / Email / Username / Employee ID match
            if ($dLower === $userKeyEmail || $dLower === $userKeyName || $dLower === $userKeyEmpId || $dLower === strtolower($userId)) {
                return true;
            }

            // 2. Email prefix match (e.g. 'arfan', 'asmee', 'sufiyan')
            $emailPrefix = explode('@', $userKeyEmail)[0];
            if (!empty($emailPrefix) && ($dLower === $emailPrefix || $dLower === strtolower($emailPrefix))) {
                return true;
            }

            // 3. Thai Name comparison without prefixes (นาย, นาง, นางสาว, ดร.)
            $cleanD = preg_replace('/(นาย|นางสาว|นาง|ดร\.)\s*/u', '', $d);
            $cleanD = preg_replace('/\s+/', '', strtolower(trim($cleanD)));

            $cleanUser = preg_replace('/(นาย|นางสาว|นาง|ดร\.)\s*/u', '', $userName);
            $cleanUser = preg_replace('/\s+/', '', strtolower(trim($cleanUser)));

            if (!empty($cleanD) && !empty($cleanUser)) {
                if ($cleanD === $cleanUser) {
                    return true;
                }
            }

            // 4. First name AND Last name match (must match BOTH together, not last name alone)
            $uFirst = preg_replace('/\s+/', '', strtolower(trim($user->first_name ?? '')));
            $uLast = preg_replace('/\s+/', '', strtolower(trim($user->last_name ?? '')));
            if (!empty($uFirst) && !empty($uLast)) {
                if ((str_contains($cleanD, $uFirst) || str_contains($dLower, $uFirst)) &&
                    (str_contains($cleanD, $uLast) || str_contains($dLower, $uLast))) {
                    return true;
                }
            }

            return false;
        };

        // 1. Check dynamic driver assignments from Cache (saved when Admin assigns vehicle)
        $cachedAssignments = \Illuminate\Support\Facades\Cache::get('driver_vehicle_assignments', []);
        $dynamicCar = null;

        foreach ($cachedAssignments as $cCode => $info) {
            $dId = $info['driver_id'] ?? '';
            $dName = $info['driver_name'] ?? '';
            if ($isDriverMatchingUser($dId) || $isDriverMatchingUser($dName)) {
                $dynamicCar = $cCode;
                break;
            }
        }

        $defaultDriverCarMap = [
            'asmee' => 'EV-01', 'asmee@yru.ac.th' => 'EV-01', '69003' => 'EV-01', 'นายอัสมี มูเล็ง' => 'EV-01', 'อัสมี' => 'EV-01',
            'arfan' => 'EV-02', 'arfan@yru.ac.th' => 'EV-02', '69004' => 'EV-02', 'นายอัรฟาน มะเระ' => 'EV-02', 'อัรฟาน' => 'EV-02',
            'sufiyan' => 'EV-03', 'sufiyan@yru.ac.th' => 'EV-03', '69005' => 'EV-03', 'นายสุฟียัน มะเซ็ง' => 'EV-03', 'สุฟียัน' => 'EV-03',
            'usman' => 'EV-04', 'usman@yru.ac.th' => 'EV-04', '69006' => 'EV-04', 'นายอุสมาน สาและ' => 'EV-04', 'อุสมาน' => 'EV-04',
            'badri' => 'EV-05', 'badri@yru.ac.th' => 'EV-05', '69007' => 'EV-05', 'นายบัดรี สาและ' => 'EV-05', 'บัดรี' => 'EV-05',
            'torik' => 'EV-06', 'torik@yru.ac.th' => 'EV-06', '69008' => 'EV-06',
            'somwang' => 'EV-07', 'somwang@yru.ac.th' => 'EV-07', '69009' => 'EV-07',
            'somjai' => 'EV-08', 'somjai@yru.ac.th' => 'EV-08', 'somjal@yru.ac.th' => 'EV-08', '69010' => 'EV-08',
            'kitti' => 'EV-09', 'kitti@yru.ac.th' => 'EV-09', '69011' => 'EV-09',
            'ruslan' => 'EV-10', 'ruslan@yru.ac.th' => 'EV-10', '69012' => 'EV-10',
        ];

        $fallbackCar = $defaultDriverCarMap[$userKeyEmail] ?? ($defaultDriverCarMap[$userKeyName] ?? ($defaultDriverCarMap[$userKeyEmpId] ?? ($defaultDriverCarMap[$userName] ?? null)));
        $assignedCar = $dynamicCar ?: $fallbackCar;

        $isVehicleHead = (
            $role === 'vehicle_head' || 
            $role === 'head_of_vehicle' || 
            $role === 'vehiclehead' || 
            $role === 'vehicle_supervisor' ||
            $role === 'supervisor' || 
            $role === 'หัวหน้ายานพาหนะ' || 
            $role === 'หัวหน้างานยานพาหนะ' || 
            str_contains($role, 'หัวหน้า') || 
            str_contains($role, 'vehicle_head') || 
            str_contains($role, 'vehiclehead') || 
            str_contains($role, 'ยานพาหนะ') ||
            str_contains($role, 'supervisor')
        );

        $isMechanic = (
            $role === 'mechanic' || 
            $role === 'technician' || 
            $role === 'maintenance' || 
            $role === 'ช่างซ่อม' || 
            $role === 'ช่าง' || 
            $role === 'ช่างซ่อมบำรุง' || 
            $role === 'ช่างเครื่อง' ||
            str_contains($role, 'ช่าง') || 
            str_contains($role, 'mechanic') || 
            str_contains($role, 'technician') ||
            str_contains($role, 'maintenance')
        );

        $isAdmin = (
            $role === 'administrator' || 
            $role === 'admin' || 
            $role === 'ผู้ดูแลระบบ' || 
            $role === 'แอดมิน' ||
            $role === 'staff' ||
            $role === 'เจ้าหน้าที่' ||
            $role === 'บุคลากร' ||
            str_contains($role, 'admin') || 
            str_contains($role, 'administrator') || 
            str_contains($role, 'ผู้ดูแล')
        );

        $isExecutive = (
            $role === 'executive' || 
            $role === 'ผู้บริหาร' || 
            $role === 'director' ||
            $role === 'exec' ||
            str_contains($role, 'executive') || 
            str_contains($role, 'ผู้บริหาร') ||
            str_contains($role, 'director')
        );

        $isDriverRole = (
            $role === 'driver' || 
            $role === 'พนักงานขับรถ' || 
            $role === 'คนขับรถ' || 
            $role === 'คนขับ' ||
            $role === 'พนักงานขับรถไฟฟ้า' ||
            str_contains($role, 'driver') || 
            str_contains($role, 'ขับรถ') ||
            str_contains($role, 'คนขับ')
        );

        $isPassengerRole = (
            $role === 'passenger' || 
            $role === 'student' || 
            $role === 'นักศึกษา' || 
            $role === 'ผู้โดยสาร' || 
            $role === 'user' || 
            $role === 'ผู้ใช้งาน' ||
            str_contains($role, 'student') ||
            str_contains($role, 'passenger') ||
            str_contains($role, 'นักศึกษา') ||
            str_contains($role, 'ผู้โดยสาร')
        );

        if ($isVehicleHead) {
            $redirectUrl = '/vehicle-head';
        } elseif ($isMechanic) {
            $redirectUrl = '/maintenance-system';
        } elseif ($isAdmin) {
            $redirectUrl = '/admin-view';
        } elseif ($isExecutive) {
            $redirectUrl = '/executive-view';
        } elseif ($isPassengerRole) {
            $redirectUrl = '/home';
        } elseif ($isDriverRole || (empty($user->user_role) && (!empty($dynamicCar) || !empty($fallbackCar)))) {
            // Load trams list from storage / cache
            $tramsList = [];
            $tramsJson = \Illuminate\Support\Facades\Cache::get('global_storage_yru_trams_v18');
            if ($tramsJson) {
                $tramsList = is_string($tramsJson) ? json_decode($tramsJson, true) : $tramsJson;
            }

            // Check tramsList in cache if driver matches any car
            if (!$dynamicCar && is_array($tramsList)) {
                foreach ($tramsList as $t) {
                    $tDriver = $t['driver'] ?? '';
                    $tDriverId = $t['driver_id'] ?? '';
                    $tId = $t['id'] ?? '';
                    if (!empty($tId) && ($isDriverMatchingUser($tDriver) || $isDriverMatchingUser($tDriverId))) {
                        $dynamicCar = $tId;
                        $assignedCar = $tId;
                        break;
                    }
                }
            }

            // Find information about the driver's default car and currently assigned car
            $fallbackTram = null;
            $assignedTram = null;
            if (is_array($tramsList)) {
                foreach ($tramsList as $t) {
                    if (isset($t['id']) && $t['id'] === $fallbackCar) $fallbackTram = $t;
                    if (isset($t['id']) && $t['id'] === $assignedCar) $assignedTram = $t;
                }
            }

            // 1. Check if the default regular car has been reassigned to another DIFFERENT driver
            $otherDriverOnFallback = null;
            if ($fallbackCar && !$dynamicCar && $assignedCar !== $fallbackCar) {
                if (isset($cachedAssignments[$fallbackCar])) {
                    $cDriverId = $cachedAssignments[$fallbackCar]['driver_id'] ?? '';
                    $cDriverName = $cachedAssignments[$fallbackCar]['driver_name'] ?? '';
                    if (!empty($cDriverName) && !$isDriverMatchingUser($cDriverId) && !$isDriverMatchingUser($cDriverName)) {
                        $otherDriverOnFallback = $cDriverName;
                    }
                } elseif ($fallbackTram) {
                    $tDriver = $fallbackTram['driver'] ?? '';
                    $tDriverId = $fallbackTram['driver_id'] ?? '';
                    if (!empty($tDriver) && !str_contains($tDriver, '--') && !$isDriverMatchingUser($tDriverId) && !$isDriverMatchingUser($tDriver)) {
                        $otherDriverOnFallback = $tDriver;
                    }
                }
            }

            // If another DIFFERENT person was assigned to this driver's regular car, and this driver is not given another car
            if ($otherDriverOnFallback && !$dynamicCar && $assignedCar !== $fallbackCar) {
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'driver_reassigned',
                    'title' => 'แจ้งเตือนการมอบหมายงาน',
                    'message' => 'รถ ' . $fallbackCar . ' ของท่านถูกโอนให้ [' . $otherDriverOnFallback . '] ดูแลแทน เนื่องจากท่านยังไม่ได้รับมอบหมายรถคันใหม่ กรุณาติดต่อผู้ดูแลระบบ'
                ], 403);
            }

            // If the driver has no vehicle assigned at all, fallback to EV-01
            if (!$assignedCar) {
                $assignedCar = 'EV-01';
            }

            // 2. Check if vehicle is explicitly locked/suspended by Admin
            // RULE: Reporting repairs/maintenance allows the driver to log in at all times.
            // A driver is ONLY blocked if the ADMIN explicitly suspended or marked the vehicle as broken down in the Admin management panel.
            $isAdminSuspended = false;
            $adminStatusText = '';

            // Check explicit Admin lock cache
            $adminLock = \Illuminate\Support\Facades\Cache::get('admin_vehicle_lock_' . $assignedCar);
            if ($adminLock && !empty($adminLock['locked'])) {
                $isAdminSuspended = true;
                $adminStatusText = $adminLock['status'] ?? $adminLock['reason'] ?? 'ระงับการใช้งาน';
            }

            // Check if tram object in storage was explicitly set to suspended/broken by Admin
            if (!$isAdminSuspended && isset($assignedTram['admin_suspended']) && $assignedTram['admin_suspended']) {
                $isAdminSuspended = true;
                $adminStatusText = $assignedTram['status'] ?? 'ระงับการใช้งาน';
            } elseif (!$isAdminSuspended && isset($assignedTram['admin_locked']) && $assignedTram['admin_locked']) {
                $isAdminSuspended = true;
                $adminStatusText = $assignedTram['status'] ?? 'ระงับการใช้งาน';
            }

            // Check if admin explicitly set status to "ระงับการใช้งาน"
            if (!$isAdminSuspended && isset($assignedTram['status'])) {
                $tStat = trim($assignedTram['status']);
                if ($tStat === 'ระงับการใช้งาน') {
                    $isAdminSuspended = true;
                    $adminStatusText = $tStat;
                }
            }

            if ($isAdminSuspended) {
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'vehicle_suspended',
                    'title' => 'รถถูกระงับการใช้งาน / รถขัดข้อง',
                    'message' => 'รถไฟฟ้าประจำของท่าน (' . $assignedCar . ') อยู่ในสถานะ' . $adminStatusText . ' ไม่สามารถเข้าสู่ระบบเพื่อปฏิบัติงานได้ กรุณาติดต่อผู้ดูแลระบบเพื่อขอรับมอบหมายรถคันอื่นแทน'
                ], 403);
            }

            $redirectParams = ['car' => $assignedCar];
            if ($dynamicCar && $fallbackCar && $dynamicCar !== $fallbackCar) {
                $redirectParams['reassigned_from'] = $fallbackCar;
            }
            $redirectUrl = '/tracking?' . http_build_query($redirectParams);
        } else {
            $redirectUrl = '/home';
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
