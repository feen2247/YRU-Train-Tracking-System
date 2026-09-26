<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * ดึงข้อมูลผู้ใช้งานทั้งหมดส่งกลับเป็น JSON
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    /**
     * เก็บข้อมูลผู้ใช้ใหม่ (Create)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string|unique:users,employee_id',
            'prefix' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:100', 'unique:users,username', 'regex:/^[a-zA-Z0-9@.-]+$/'], 
            'email' => ['required', 'email', 'unique:users,email', 'regex:/@yru\.ac\.th$/i'],
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|string',
            'status' => 'required|string',
            'remark' => 'nullable|string',
        ], [
            'username.regex' => 'Username ต้องเป็นภาษาอังกฤษ ตัวเลข หรืออีเมลเท่านั้น ห้ามเว้นวรรค',
            'username.unique' => 'Username นี้ถูกใช้งานแล้ว',
            'employee_id.unique' => 'รหัสพนักงานนี้ถูกใช้งานแล้ว',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'email.regex' => 'อีเมลต้องเป็นโดเมน @yru.ac.th เท่านั้น',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // สร้างรหัส user_id ถัดไป รูปแบบ USR-XXXXXX
        $latestUser = User::where('user_id', 'like', 'USR-%')
            ->get()
            ->filter(function($u) {
                return preg_match('/^USR-\d+$/', $u->user_id);
            })
            ->sortByDesc(function($u) {
                return (int)substr($u->user_id, 4);
            })
            ->first();

        $nextId = 1;
        if ($latestUser && preg_match('/USR-(\d+)/', $latestUser->user_id, $matches)) {
            $nextId = (int)$matches[1] + 1;
        }
        $userId = 'USR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        $cleanFirstName = trim($request->first_name);
        if ($request->prefix && str_starts_with($cleanFirstName, $request->prefix)) {
            $cleanFirstName = trim(substr($cleanFirstName, mb_strlen($request->prefix)));
        }
        $name = trim($request->prefix . $cleanFirstName . ' ' . $request->last_name);

        $roleMap = [
            'admin' => 'Administrator',
            'executive' => 'Executive',
            'driver' => 'Driver',
            'staff' => 'Staff',
            'student' => 'Student',
            'operator' => 'Operator',
            'passenger' => 'Passenger'
        ];
        $backendRole = $roleMap[strtolower($request->role)] ?? $request->role;

        $user = User::create([
            'user_id' => $userId,
            'employee_id' => $request->employee_id,
            'prefix' => $request->prefix,
            'first_name' => $cleanFirstName,
            'last_name' => $request->last_name,
            'name' => $name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->employee_id), // รหัสผ่านเริ่มต้นเป็นรหัสพนักงาน
            'phone_number' => $request->phone_number,
            'user_role' => $backendRole,
            'usage_rights' => 'Active',
            'status' => $request->status,
            'remark' => $request->remark,
            'email_verified_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'เพิ่มผู้ใช้งานสำเร็จ', 'user' => $user]);
    }

    /**
     * อัปเดตข้อมูลผู้ใช้งาน (Update)
     */
    public function update(Request $request, $id)
    {
        $user = User::where('user_id', $id)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบผู้ใช้งาน'], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => ['required', 'string', Rule::unique('users', 'employee_id')->ignore($user->user_id, 'user_id')],
            'prefix' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z0-9@.-]+$/', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'email', 'regex:/@yru\.ac\.th$/i', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|string',
            'status' => 'required|string',
            'remark' => 'nullable|string',
        ], [
            'username.regex' => 'Username ต้องเป็นภาษาอังกฤษ ตัวเลข หรืออีเมลเท่านั้น ห้ามเว้นวรรค',
            'username.unique' => 'Username นี้ถูกใช้งานแล้ว',
            'employee_id.unique' => 'รหัสพนักงานนี้ถูกใช้งานแล้ว',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'email.regex' => 'อีเมลต้องเป็นโดเมน @yru.ac.th เท่านั้น',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $cleanFirstName = trim($request->first_name);
        if ($request->prefix && str_starts_with($cleanFirstName, $request->prefix)) {
            $cleanFirstName = trim(substr($cleanFirstName, mb_strlen($request->prefix)));
        }
        $name = trim($request->prefix . $cleanFirstName . ' ' . $request->last_name);

        $roleMap = [
            'admin' => 'Administrator',
            'executive' => 'Executive',
            'driver' => 'Driver',
            'staff' => 'Staff',
            'student' => 'Student',
            'operator' => 'Operator',
            'passenger' => 'Passenger',
            'mechanic' => 'Mechanic',
            'technician' => 'Mechanic',
            'ช่างซ่อม' => 'Mechanic',
            'vehicle_head' => 'VehicleHead',
            'head_of_vehicle' => 'VehicleHead',
            'หัวหน้ายานพาหนะ' => 'VehicleHead'
        ];
        $backendRole = $roleMap[strtolower($request->role)] ?? ($roleMap[$request->role] ?? $request->role);

        $user->update([
            'employee_id' => $request->employee_id,
            'prefix' => $request->prefix,
            'first_name' => $cleanFirstName,
            'last_name' => $request->last_name,
            'name' => $name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'user_role' => $backendRole,
            'status' => $request->status,
            'remark' => $request->remark,
        ]);

        return response()->json(['status' => 'success', 'message' => 'อัปเดตข้อมูลผู้ใช้งานสำเร็จ', 'user' => $user]);
    }

    /**
     * ลบผู้ใช้งาน (Delete)
     */
    public function destroy($id)
    {
        $user = User::where('user_id', $id)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบผู้ใช้งาน'], 404);
        }

        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'ลบผู้ใช้งานสำเร็จ']);
    }

    /**
     * ซิงก์ข้อมูลผู้ใช้จาก LocalStorage มาลง Database (สำหรับหน้า Admin ที่ต้องการเก็บ LocalStorage ไว้)
     */
    public function syncFromLocal(Request $request)
    {
        $finalUsername = $request->username ?: (explode('@', $request->email)[0] ?: $request->employee_id);
        $user = User::where('employee_id', $request->employee_id)
                    ->orWhere('email', $request->email)
                    ->orWhere('username', $finalUsername)
                    ->first();
                    
        if (!$user) {
            // สร้างรหัส user_id ถัดไป รูปแบบ USR-XXXXXX
            $latestUser = User::where('user_id', 'like', 'USR-%')
                ->get()
                ->filter(function($u) { return preg_match('/^USR-\d+$/', $u->user_id); })
                ->sortByDesc(function($u) { return (int)substr($u->user_id, 4); })
                ->first();

            $nextId = 1;
            if ($latestUser && preg_match('/USR-(\d+)/', $latestUser->user_id, $matches)) {
                $nextId = (int)$matches[1] + 1;
            }
            $userId = 'USR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            $user = new User();
            $user->user_id = $userId;
            $user->password = Hash::make($request->password ?: ($request->employee_id ?: '12345678'));
            $user->email_verified_at = now();
        }
        
        $roleMap = [
            'admin' => 'Administrator',
            'administrator' => 'Administrator',
            'ผู้ดูแลระบบ' => 'Administrator',
            'executive' => 'Executive',
            'ผู้บริหาร' => 'Executive',
            'driver' => 'Driver',
            'พนักงานขับรถ' => 'Driver',
            'staff' => 'Staff',
            'บุคลากร' => 'Staff',
            'student' => 'Student',
            'นักศึกษา' => 'Student',
            'operator' => 'Operator',
            'passenger' => 'Passenger',
            'mechanic' => 'Mechanic',
            'technician' => 'Mechanic',
            'ช่างซ่อม' => 'Mechanic',
            'vehicle_head' => 'VehicleHead',
            'head_of_vehicle' => 'VehicleHead',
            'vehiclehead' => 'VehicleHead',
            'หัวหน้ายานพาหนะ' => 'VehicleHead'
        ];
        $backendRole = $roleMap[strtolower($request->role)] ?? ($roleMap[$request->role] ?? 'VehicleHead');

        $isActive = in_array($request->status, ['ปกติ', 'ใช้งาน', 'Active', 'active', 'ACTIVE']) || empty($request->status);

        $user->employee_id = $request->employee_id ?: '';
        $user->prefix = $request->prefix ?: '';
        $user->first_name = $request->first_name ?: '';
        $user->last_name = $request->last_name ?: '';
        $user->name = trim(($request->prefix ?? '') . ($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: $finalUsername;
        $user->username = $finalUsername;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number ?: '';
        $user->user_role = $backendRole;
        $user->usage_rights = $isActive ? 'Active' : 'Suspended';
        $user->status = $isActive ? 'ปกติ' : 'ระงับการใช้งาน';
        $user->remark = $request->remark ?: '';
        $user->save();

        return response()->json(['status' => 'success', 'user' => $user]);
    }

    /**
     * ลบผู้ใช้งานจาก LocalStorage Sync
     */
    public function syncDeleteLocal($empId)
    {
        User::where('employee_id', $empId)->delete();
        return response()->json(['status' => 'success']);
    }
}
