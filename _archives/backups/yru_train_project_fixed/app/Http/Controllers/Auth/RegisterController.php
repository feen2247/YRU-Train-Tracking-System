<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/passenger/dashboard'; // ✨ แก้ไขจาก '/home' เป็นหน้าตรงของระบบคุณแล้ว

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'], // นำ uniqueออกชั่วคราวเพราะโครงสร้างจริงไม่มีคอลัมน์ email แยก
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data)
    {
        // นำค่าอีเมลหรือชื่อที่ผู้ใช้กรอกมาใช้เป็น username ในฐานข้อมูล
        return User::create([
            'user_id'      => 'USR-' . time() . rand(10, 99),
            'username'     => $data['email'], // ดึงค่าจากฟอร์มช่อง Email มาใส่ในคอลัมน์ username
            'password'     => Hash::make($data['password']),
            'user_role'    => 'passenger',    // ค่าเริ่มต้นบทบาทผู้ใช้งาน
            'usage_rights' => 'active',       // ค่าเริ่มต้นสิทธิ์การใช้งาน
        ]);
    }
}