<?php

namespace App\Http\Controllers;

use App\Models\ElectricTrain;
use App\Models\TrainLocation;
use App\Models\Schedule;
use App\Models\TravelHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverController extends Controller
{
    /**
     * Driver Portal Dashboard.
     */
    public function dashboard()
    {
        return redirect('/tracking');
    }

    /**
     * View all assigned schedules.
     */
    public function viewSchedule()
    {
        return redirect('/tracking');
    }

    /**
     * Start a new travel trip logging session.
     */
    public function endRound(Request $request) 
{
    // 1. รับค่าที่ส่งมาจาก JavaScript (status: 'ended', ended_at: 'เวลา')
    $status = $request->input('status');
    $endedAt = $request->input('ended_at');

    // 2. เขียน Logic เพื่ออัปเดตสถานะรถลงฐานข้อมูลในระบบของคุณตรงนี้...
    // เช่น: Tram::where('driver_id', auth()->id())->update(['status' => $status, 'end_time' => $endedAt]);

    // 3. ส่งข้อมูลตอบกลับเป็น JSON เพื่อให้ JavaScript ทราบว่าทำงานสำเร็จ
    return response()->json([
        'status' => 'success',
        'message' => 'สิ้นสุดรอบการเดินรถเรียบร้อยแล้ว'
    ]);
}

    /**
     * End a travel trip logging session.
     */
    public function endTrip(Request $request)
    {
        $request->validate([
            'travel_history_id' => 'required|exists:travel_histories,id',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        $trip = TravelHistory::findOrFail($request->travel_history_id);

        if ($trip->driver_id !== auth()->user()->user_id) {
            abort(403, 'Unauthorized access to this trip log.');
        }

        $trip->update([
            'end_time' => Carbon::now(),
            'distance_km' => $request->input('distance_km', 0.00),
            'travel_status' => 'Completed',
        ]);

        return redirect()->back()->with('success', 'Trip ended successfully. Data recorded.');
    }
}
