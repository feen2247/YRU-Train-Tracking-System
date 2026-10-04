<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelHistory;
use Illuminate\Support\Facades\Cache;

class ExecutiveController extends Controller
{
    /**
     * Display Executive Dashboard and Analytics
     */
    public function index()
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/');
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        $role = strtolower(trim($user->user_role ?? ''));
        $isExecutive = in_array($role, ['executive', 'ผู้บริหาร', 'admin', 'administrator', 'ผู้ดูแลระบบ', 'director']) 
            || str_contains($role, 'executive') 
            || str_contains($role, 'ผู้บริหาร') 
            || str_contains($role, 'admin');

        if (!$isExecutive) {
            return redirect('/home');
        }

        $recentActivities = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('travel_histories')) {
                $recentActivities = TravelHistory::with(['electricTrain', 'driver', 'route'])
                    ->latest()
                    ->take(15)
                    ->get();
            }
        } catch (\Exception $e) {
            $recentActivities = [];
        }

        $maintenanceRequests = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('maintenance_requests')) {
                $maintenanceRequests = \App\Models\MaintenanceRequest::latest()->get();
            }
        } catch (\Exception $e) {
            $maintenanceRequests = [];
        }

        $drivers = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                $drivers = \App\Models\User::where('user_role', 'like', '%driver%')
                    ->orWhere('user_role', 'like', '%ขับรถ%')
                    ->get();
            }
        } catch (\Exception $e) {
            $drivers = [];
        }

        $electricTrains = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
                $electricTrains = \App\Models\ElectricTrain::all();
            }
        } catch (\Exception $e) {
            $electricTrains = [];
        }

        return view('passenger.executive.index', compact('recentActivities', 'maintenanceRequests', 'drivers', 'electricTrains'));
    }

    /**
     * API: Approve Maintenance Ticket
     */
    public function approveMaintenance(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $approver = $request->input('approver', 'ผู้บริหาร (Executive)');
        $remarks = $request->input('remarks', 'อนุมัติการซ่อมบำรุงตามที่เสนอ');

        $urgency = $request->input('urgency');
        $urgencyColor = 'bg-amber-100 text-amber-800';
        if ($urgency === 'วิกฤต') $urgencyColor = 'bg-rose-100 text-rose-800';
        else if ($urgency === 'ปกติ') $urgencyColor = 'bg-emerald-100 text-emerald-800';

        // 1. Update Executive Action History
        $history = Cache::get('executive_maintenance_actions', []);
        $history[$ticketId] = [
            'ticket_id' => $ticketId,
            'action' => 'approved',
            'approver' => $approver,
            'remarks' => $remarks,
            'urgency' => $urgency,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s')
        ];
        Cache::put('executive_maintenance_actions', $history, 86400 * 30);

        // 2. Update Global Tickets Cache
        $rawStorage = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
        if ($rawStorage) {
            $tickets = is_string($rawStorage) ? json_decode($rawStorage, true) : $rawStorage;
            if (is_array($tickets)) {
                foreach ($tickets as &$t) {
                    $mId = $t['id'] ?? '';
                    $mNo = $t['ticket_no'] ?? '';
                    if ($mId === $ticketId || $mNo === $ticketId || strtolower($mId) === strtolower($ticketId) || strtolower($mNo) === strtolower($ticketId)) {
                        $t['status'] = 'approved';
                        $t['director_action'] = 'approved';
                        $t['director_remarks'] = $remarks;
                        $t['director_name'] = $approver;
                        $t['approved_at'] = date('Y-m-d H:i');
                        $t['approver'] = $approver;
                        $t['remarks'] = $remarks;
                        if ($urgency) {
                            $t['urgency'] = $urgency;
                            $t['urgency_color'] = $urgencyColor;
                        }
                    }
                }
                Cache::forever('global_storage_yru_maintenance_tickets_v3', json_encode($tickets));
                Cache::put('maintenance_tickets', $tickets, 86400 * 30);
            }
        }

        // 3. Update Database `maintenances` table
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
                \App\Models\Maintenance::where('maintenance_code', $ticketId)
                    ->orWhere('id', $ticketId)
                    ->update([
                        'repair_status' => 'approved',
                        'repair_start_date' => date('Y-m-d')
                    ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "อนุมัติรายการแจ้งซ่อมรหัส {$ticketId} เรียบร้อยแล้ว",
            'data' => $history[$ticketId]
        ]);
    }

    /**
     * API: Reject Maintenance Ticket
     */
    public function rejectMaintenance(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $approver = $request->input('approver', 'ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)');
        $remarks = $request->input('remarks', 'ไม่อนุมัติ / ขอข้อมูลเพิ่มเติม');

        // 1. Update Executive Action History
        $history = Cache::get('executive_maintenance_actions', []);
        $history[$ticketId] = [
            'ticket_id' => $ticketId,
            'action' => 'rejected',
            'approver' => $approver,
            'remarks' => $remarks,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s')
        ];
        Cache::put('executive_maintenance_actions', $history, 86400 * 30);

        // 2. Update Global Tickets Cache
        $rawStorage = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
        if ($rawStorage) {
            $tickets = is_string($rawStorage) ? json_decode($rawStorage, true) : $rawStorage;
            if (is_array($tickets)) {
                foreach ($tickets as &$t) {
                    $mId = $t['id'] ?? '';
                    $mNo = $t['ticket_no'] ?? '';
                    if ($mId === $ticketId || $mNo === $ticketId || strtolower($mId) === strtolower($ticketId) || strtolower($mNo) === strtolower($ticketId)) {
                        $t['status'] = 'rejected';
                        $t['director_action'] = 'rejected';
                        $t['director_remarks'] = $remarks;
                        $t['director_name'] = $approver;
                        $t['approved_at'] = date('Y-m-d H:i');
                        $t['approver'] = $approver;
                        $t['remarks'] = $remarks;
                    }
                }
                Cache::forever('global_storage_yru_maintenance_tickets_v3', json_encode($tickets));
                Cache::put('maintenance_tickets', $tickets, 86400 * 30);
            }
        }

        // 3. Update Database `maintenances` table
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
                \App\Models\Maintenance::where('maintenance_code', $ticketId)
                    ->orWhere('id', $ticketId)
                    ->update([
                        'repair_status' => 'rejected'
                    ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "บันทึกการไม่อนุมัติ/ตีกลับรายการแจ้งซ่อมรหัส {$ticketId} เรียบร้อยแล้ว",
            'data' => $history[$ticketId]
        ]);
    }
}
