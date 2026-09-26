<?php

namespace App\Http\Controllers;

use App\Models\GpsDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GpsController extends Controller
{
    private const ADMIN_ROLES = ['admin', 'administrator', 'ผู้ดูแลระบบ', 'staff', 'เจ้าหน้าที่'];

    /**
     * ESP32 ส่งพิกัดเข้ามา (POST /api/gps/report)
     * Header: X-GPS-Key ต้องตรงกับ GPS_DEVICE_KEY ใน .env
     * อุปกรณ์ที่ยังไม่เคยส่งจะถูกลงทะเบียนอัตโนมัติ แล้วรอแอดมินผูกกับรถ
     */
    public function report(Request $request): JsonResponse
    {
        $expectedKey = (string) config('services.gps.device_key');
        if ($expectedKey === '') {
            return response()->json(['status' => 'error', 'message' => 'GPS_DEVICE_KEY is not configured on the server'], 503);
        }
        if (!hash_equals($expectedKey, (string) $request->header('X-GPS-Key'))) {
            return response()->json(['status' => 'error', 'message' => 'Invalid device key'], 401);
        }

        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'satellites' => ['nullable', 'integer', 'min:0', 'max:255'],
            'hdop' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $device = GpsDevice::firstOrNew(['device_id' => $data['device_id']]);
        $device->fill([
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
            'speed_kmh' => $data['speed_kmh'] ?? null,
            'satellites' => $data['satellites'] ?? null,
            'hdop' => $data['hdop'] ?? null,
            'last_seen_at' => now(),
        ]);
        $device->save();

        return response()->json([
            'status' => 'ok',
            'device_id' => $device->device_id,
            'vehicle_id' => $device->vehicle_id,
        ]);
    }

    /**
     * ตำแหน่งล่าสุดของรถทุกคันที่มี GPS ผูกอยู่ (GET /api/gps/positions) ใช้บนแผนที่หน้า /home
     */
    public function positions(): JsonResponse
    {
        $positions = GpsDevice::whereNotNull('vehicle_id')
            ->whereNotNull('last_seen_at')
            ->get()
            ->map(fn (GpsDevice $d) => $d->toApiArray())
            ->values();

        return response()->json([
            'status' => 'ok',
            'stale_seconds' => (int) config('services.gps.stale_seconds', 120),
            'positions' => $positions,
        ]);
    }

    /** รายการอุปกรณ์ทั้งหมด สำหรับหน้าแอดมิน (GET /api/gps/devices) */
    public function devices(): JsonResponse
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        return response()->json([
            'status' => 'ok',
            'devices' => GpsDevice::orderBy('device_id')->get()->map(fn (GpsDevice $d) => $d->toApiArray())->values(),
        ]);
    }

    /** แอดมินเพิ่มอุปกรณ์ล่วงหน้าก่อนที่ ESP32 จะส่งข้อมูลครั้งแรก (POST /api/gps/devices) */
    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_\-]+$/', 'unique:gps_devices,device_id'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $device = GpsDevice::create($data);

        return response()->json(['status' => 'ok', 'device' => $device->toApiArray()], 201);
    }

    /**
     * ผูก/ยกเลิกการผูก GPS กับรถ (POST /api/gps/devices/{deviceId}/assign)
     * body: { vehicle_id: "EV-01" | null, name?: string }
     */
    public function assign(Request $request, string $deviceId): JsonResponse
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        $device = GpsDevice::where('device_id', $deviceId)->first();
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบอุปกรณ์ GPS นี้'], 404);
        }

        $data = $request->validate([
            'vehicle_id' => ['nullable', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $vehicleId = isset($data['vehicle_id']) && trim($data['vehicle_id']) !== '' ? trim($data['vehicle_id']) : null;

        if ($vehicleId !== null) {
            $taken = GpsDevice::where('vehicle_id', $vehicleId)
                ->where('id', '!=', $device->id)
                ->first();
            if ($taken) {
                return response()->json([
                    'status' => 'error',
                    'message' => "รถ {$vehicleId} ผูกกับ GPS {$taken->device_id} อยู่แล้ว กรุณายกเลิกการผูกก่อน",
                ], 409);
            }
        }

        $device->vehicle_id = $vehicleId;
        if (array_key_exists('name', $data)) {
            $device->name = $data['name'];
        }
        $device->save();

        return response()->json(['status' => 'ok', 'device' => $device->toApiArray()]);
    }

    /** ลบอุปกรณ์ (DELETE /api/gps/devices/{deviceId}) */
    public function destroy(string $deviceId): JsonResponse
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        $deleted = GpsDevice::where('device_id', $deviceId)->delete();
        if (!$deleted) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบอุปกรณ์ GPS นี้'], 404);
        }

        return response()->json(['status' => 'ok']);
    }

    private function denyUnlessAdmin(): ?JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ'], 401);
        }
        $role = strtolower(trim(Auth::user()->user_role ?? ''));
        if (!in_array($role, self::ADMIN_ROLES, true)) {
            return response()->json(['status' => 'error', 'message' => 'เฉพาะผู้ดูแลระบบเท่านั้น'], 403);
        }
        return null;
    }
}
