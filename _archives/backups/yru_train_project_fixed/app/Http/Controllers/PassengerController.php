<?php

namespace App\Http\Controllers;

use App\Models\ElectricTrain;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Notification;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    /**
     * Display real-time train tracking dashboard.
     */
    public function tracking()
    {
        $activeTrains = ElectricTrain::with(['locations' => function ($query) {
            $query->orderBy('recorded_time', 'desc');
        }])->where('car_status', 'Active')->get();

        return view('passenger.tracking', compact('activeTrains'));
    }

    /**
     * Show route and station details.
     */
    public function routesInfo()
    {
        $routes = Route::with(['stations' => function ($query) {
            $query->orderBy('order_of_parking_spots', 'asc');
        }])->get();

        return view('passenger.routes', compact('routes'));
    }

    /**
     * Show time schedules.
     */
    public function schedulesInfo()
    {
        $schedules = Schedule::with(['route', 'electricTrain', 'driver'])
            ->orderBy('date', 'asc')
            ->orderBy('departure_time', 'asc')
            ->get();

        return view('passenger.schedules', compact('schedules'));
    }

    /**
     * Show general notices and announcements.
     */
    public function notificationsInfo()
    {
        $notifications = Notification::with('creator')->latest()->get();

        return view('passenger.notifications', compact('notifications'));
    }

    /**
     * Passenger authenticated dashboard.
     */
    public function dashboard()
    {
        $activeTrainsCount = ElectricTrain::where('car_status', 'Active')->count();
        $routesCount = Route::count();
        $latestNotifications = Notification::latest()->limit(5)->get();

        return view('passenger.dashboard', compact('activeTrainsCount', 'routesCount', 'latestNotifications'));
    }
}
