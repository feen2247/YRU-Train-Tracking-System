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
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบอีเมลนี้ในระบบ'], 404);
        }

        $token = \Illuminate\Support\Str::random(60);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => \Illuminate\Support\Facades\Hash::make($token),
                'created_at' => now()
            ]
        );

        $resetLink = url('/password/reset/' . $token . '?email=' . urlencode($email));

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "สวัสดีครับ,\n\nคุณได้แจ้งขอรีเซ็ตรหัสผ่านใหม่สำหรับระบบ YRU EV Tracker\nกรุณาคลิกลิงก์ด้านล่างเพื่อตั้งรหัสผ่านใหม่:\n\n" . $resetLink . "\n\nหากคุณไม่ได้ทำรายการนี้ สามารถข้ามอีเมลนี้ได้เลย",
                function ($message) use ($email) {
                    $message->to($email)->subject('ลิงก์สำหรับตั้งรหัสผ่านใหม่ [YRU EV Tracker]');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send reset link to ' . $email . ': ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่ภายหลัง'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'ส่งลิงก์ตั้งรหัสผ่านใหม่ไปยังอีเมลของคุณแล้ว']);
    }
}
