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
        $driverId = auth()->user()->user_id;

        // Fetch schedules assigned to this driver for today
        $todaySchedules = Schedule::with(['route', 'electricTrain'])
            ->where('driver_id', $driverId)
            ->where('date', Carbon::today()->toDateString())
            ->orderBy('departure_time', 'asc')
            ->get();

        // Get currently active/running trip for this driver
        $activeTrip = TravelHistory::with(['electricTrain', 'route'])
            ->where('driver_id', $driverId)
            ->where('travel_status', 'Running')
            ->first();

        // Get list of all trains to allow selection
        $trains = ElectricTrain::all();

        return view('driver.dashboard', compact('todaySchedules', 'activeTrip', 'trains'));
    }

    /**
     * Update train GPS location.
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'skytrain_code' => 'required|exists:electric_trains,skytrain_code',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        TrainLocation::create([
            'skytrain_code' => $request->skytrain_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_time' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    /**
     * Update train operational status.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'skytrain_code' => 'required|exists:electric_trains,skytrain_code',
            'car_status' => 'required|string|in:Active,Maintenance,Inactive',
        ]);

        $train = ElectricTrain::findOrFail($request->skytrain_code);
        $train->update([
            'car_status' => $request->car_status,
        ]);

        return redirect()->back()->with('success', 'Train status updated successfully.');
    }

    /**
     * View all assigned schedules.
     */
    public function viewSchedule()
    {
        $driverId = auth()->user()->user_id;

        $schedules = Schedule::with(['route', 'electricTrain'])
            ->where('driver_id', $driverId)
            ->orderBy('date', 'desc')
            ->orderBy('departure_time', 'asc')
            ->get();

        return view('driver.schedule', compact('schedules'));
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
