<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ElectricTrain;
use App\Models\Route;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['electricTrain', 'driver', 'route'])
            ->orderBy('date', 'desc')
            ->orderBy('departure_time', 'asc')
            ->get();
            
        return view('operator.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $trains = ElectricTrain::all();
        $routes = Route::all();
        $drivers = User::where('user_role', 'Driver')->get();
        
        return view('operator.schedules.create', compact('trains', 'routes', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'timetable_code' => 'required|string|max:10|unique:schedules,timetable_code',
            'skytrain_code' => 'required|exists:electric_trains,skytrain_code',
            'driver_id' => 'required|exists:users,user_id',
            'route_code' => 'required|exists:routes,route_code',
            'departure_time' => 'required',
            'arrival_time' => 'required',
            'date' => 'required|date',
        ]);

        Schedule::create($request->all());

        return redirect()->route('operator.schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function show($id)
    {
        $schedule = Schedule::with(['electricTrain', 'driver', 'route'])->findOrFail($id);
        return view('operator.schedules.show', compact('schedule'));
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $trains = ElectricTrain::all();
        $routes = Route::all();
        $drivers = User::where('user_role', 'Driver')->get();
        
        return view('operator.schedules.edit', compact('schedule', 'trains', 'routes', 'drivers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'skytrain_code' => 'required|exists:electric_trains,skytrain_code',
            'driver_id' => 'required|exists:users,user_id',
            'route_code' => 'required|exists:routes,route_code',
            'departure_time' => 'required',
            'arrival_time' => 'required',
            'date' => 'required|date',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->only([
            'skytrain_code',
            'driver_id',
            'route_code',
            'departure_time',
            'arrival_time',
            'date'
        ]));

        return redirect()->route('operator.schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('operator.schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
