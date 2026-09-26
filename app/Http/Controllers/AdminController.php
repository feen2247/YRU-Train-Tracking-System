<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelHistory;

class AdminController extends Controller
{
    public function dashboard()
    {
        $recentActivities = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('travel_histories')) {
                $recentActivities = TravelHistory::with(['electricTrain', 'driver', 'route'])
                    ->latest()
                    ->take(10)
                    ->get();
            }
        } catch (\Exception $e) {
            $recentActivities = [];
        }

        return view('passenger.admin.index', compact('recentActivities'));
    }
}
