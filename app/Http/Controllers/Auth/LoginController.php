<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'loginSubmit']);
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle custom API login request.
     */
    public function loginSubmit(\Illuminate\Http\Request $request)
    {
        $username = trim($request->input('username'));
        $password = trim($request->input('password'));

        $user = \App\Models\User::where('username', $username)
            ->orWhere('email', $username)
            ->orWhere('employee_id', $username)
            ->first();

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'email_not_found',
                'message' => 'ไม่พบรหัสผู้ใช้งานนี้ในระบบ'
            ], 422);
        }

        // ตรวจสอบรหัสผ่าน
        if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'wrong_password',
                'message' => 'รหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง'
            ], 422);
        }

        // ตรวจสอบสถานะบัญชี (usage_rights / status)
        $statusLower = strtolower(trim($user->status ?? ''));
        $rightsLower = strtolower(trim($user->usage_rights ?? ''));
        $isActive = in_array($statusLower, ['ใช้งาน', 'active', 'ใชงาน', 'ปกติ', 'normal', '']) 
                 || in_array($rightsLower, ['active', 'ใช้งาน', 'ปกติ', 'suspended', '']) 
                 || is_null($user->status);
        if (!$isActive) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'account_suspended',
                'message' => 'บัญชีของคุณถูกระงับการใช้งาน'
            ], 403);
        }

        \Illuminate\Support\Facades\Auth::login($user, $request->has('remember'));

        // กำหนด Redirect ตามสิทธิ์
        $redirectUrl = url('/home');
        $role = strtolower(trim($user->user_role ?? ''));
        
        if ($role === 'administrator' || $role === 'admin' || $role === 'ผู้ดูแลระบบ') {
            $redirectUrl = url('/admin-view');
        } elseif ($role === 'driver' || $role === 'พนักงานขับรถ') {
            // Check if the user is assigned to any vehicle
            $isAssigned = false;
            $driverAssignments = \Illuminate\Support\Facades\Cache::get('driver_vehicle_assignments', []);
            
            // Check from backend assignments first
            foreach ($driverAssignments as $cCode => $info) {
                if (
                    (!empty($info['driver_id']) && in_array((string)$info['driver_id'], [(string)$user->user_id, (string)$user->employee_id, (string)$user->email, (string)$user->username])) ||
                    (!empty($info['driver_name']) && (string)$info['driver_name'] === (string)$user->name)
                ) {
                    $isAssigned = true;
                    break;
                }
            }

            // Fallback: Check from global synced storage if not found in backend assignments
            if (!$isAssigned) {
                $tramsJson = \Illuminate\Support\Facades\Cache::get('global_storage_yru_trams_v18');
                if ($tramsJson) {
                    $trams = json_decode($tramsJson, true);
                    if (is_array($trams)) {
                        foreach ($trams as $t) {
                            if (
                                (!empty($t['driver_id']) && in_array((string)$t['driver_id'], [(string)$user->user_id, (string)$user->employee_id, (string)$user->email, (string)$user->username])) ||
                                (!empty($t['driver']) && (string)$t['driver'] === (string)$user->name)
                            ) {
                                $isAssigned = true;
                                break;
                            }
                        }
                    }
                }
            }

            // Block access if no vehicle assigned
            if (!$isAssigned) {
                \Illuminate\Support\Facades\Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'no_vehicle_assigned',
                    'message' => 'คุณยังไม่ได้รับมอบหมายรถ กรุณาติดต่อผู้ดูแลระบบ'
                ], 403);
            }

            $redirectUrl = url('/tracking'); 
        } elseif ($role === 'passenger' || $role === 'student' || $role === 'นักศึกษา' || $role === 'ผู้โดยสาร') {
            $redirectUrl = url('/home');
        } elseif ($role === 'operator' || $role === 'staff' || $role === 'ผู้ควบคุม') {
            $redirectUrl = url('/tracking');
        } elseif ($role === 'executive' || $role === 'ผู้บริหาร') {
            $redirectUrl = url('/executive-view');
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl
        ]);
    }
}
