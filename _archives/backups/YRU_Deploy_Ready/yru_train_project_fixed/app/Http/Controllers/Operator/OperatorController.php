<?php

namespace App\Http\Controllers;

use App\Models\ElectricTrain;
use App\Models\Route;
use App\Models\Station;
use App\Models\Maintenance;
use App\Models\TravelHistory;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    /**
     * Operator Central Dashboard.
     */
    public function dashboard()
    {
        $trainsCount = ElectricTrain::count();
        $routesCount = Route::count();
        $stationsCount = Station::count();
        $activeMaintenancesCount = Maintenance::where('repair_status', '!=', 'Completed')->count();

        // Get recent travel trips
        $recentTrips = TravelHistory::with(['electricTrain', 'driver', 'route'])
            ->latest()
            ->limit(5)
            ->get();

        return view('operator.dashboard', compact(
            'trainsCount',
            'routesCount',
            'stationsCount',
            'activeMaintenancesCount',
            'recentTrips'
        ));
    }

    /**
     * View all travel log history.
     */
    public function travelLogs()
    {
        $travelLogs = TravelHistory::with(['electricTrain', 'driver', 'route'])
            ->orderBy('start_time', 'desc')
            ->get();

        return view('operator.travel_logs', compact('travelLogs'));
    }
}
