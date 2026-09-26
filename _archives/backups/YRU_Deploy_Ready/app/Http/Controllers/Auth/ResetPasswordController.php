<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function showResetForm(\Illuminate\Http\Request $request, $token = null)
    {
        return view('passenger.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !\Illuminate\Support\Facades\Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง หรือหมดอายุแล้ว']);
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'ไม่พบผู้ใช้งานนี้ในระบบ']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect('/')->with('status', 'รหัสผ่านของคุณถูกตั้งใหม่เรียบร้อยแล้ว กรุณาเข้าสู่ระบบอีกครั้ง');
    }
}
