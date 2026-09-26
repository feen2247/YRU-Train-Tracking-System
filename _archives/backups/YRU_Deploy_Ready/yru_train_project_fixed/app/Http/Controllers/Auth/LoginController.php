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

        $user = \App\Models\User::where('username', $username)->first();

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

        // ตรวจสอบสถานะบัญชี (usage_rights) ต้องเป็น active หรือ Active
        if (strtolower($user->usage_rights) !== 'active') {
            return response()->json([
                'status' => 'error',
                'error_type' => 'account_suspended',
                'message' => 'บัญชีของคุณถูกระงับการใช้งาน'
            ], 403);
        }

        \Illuminate\Support\Facades\Auth::login($user, $request->has('remember'));

        // กำหนด Redirect ตามสิทธิ์
        $redirectUrl = url('/home');
        $role = strtolower($user->user_role);
        
        if ($role === 'administrator' || $role === 'admin') {
            $redirectUrl = url('/admin-view');
        } elseif ($role === 'driver') {
            $redirectUrl = route('driver.dashboard'); 
        } elseif ($role === 'passenger') {
            $redirectUrl = url('/home'); // หรือหน้าแอปพลิเคชันสำหรับนักศึกษา
        } elseif ($role === 'operator' || $role === 'staff') {
            $redirectUrl = route('operator.dashboard');
        } elseif ($role === 'executive') {
            $redirectUrl = url('/executive-view');
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl
        ]);
    }
}
