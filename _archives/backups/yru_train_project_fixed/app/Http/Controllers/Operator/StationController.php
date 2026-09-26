<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Route;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::with('route')
            ->orderBy('route_code')
            ->orderBy('order_of_parking_spots')
            ->get();
            
        return view('operator.stations.index', compact('stations'));
    }

    public function create()
    {
        $routes = Route::all();
        return view('operator.stations.create', compact('routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parking_spot_code' => 'required|string|max:10|unique:stations,parking_spot_code',
            'route_code' => 'required|exists:routes,route_code',
            'parking_spot_name' => 'required|string|max:100',
            'order_of_parking_spots' => 'required|integer|min:1',
        ]);

        Station::create($request->all());

        return redirect()->route('operator.stations.index')->with('success', 'Station/Stop created successfully.');
    }

    public function show($id)
    {
        $station = Station::with('route')->findOrFail($id);
        return view('operator.stations.show', compact('station'));
    }

    public function edit($id)
    {
        $station = Station::findOrFail($id);
        $routes = Route::all();
        return view('operator.stations.edit', compact('station', 'routes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'route_code' => 'required|exists:routes,route_code',
            'parking_spot_name' => 'required|string|max:100',
            'order_of_parking_spots' => 'required|integer|min:1',
        ]);

        $station = Station::findOrFail($id);
        $station->update($request->only(['route_code', 'parking_spot_name', 'order_of_parking_spots']));

        return redirect()->route('operator.stations.index')->with('success', 'Station/Stop updated successfully.');
    }

    public function destroy($id)
    {
        $station = Station::findOrFail($id);
        $station->delete();

        return redirect()->route('operator.stations.index')->with('success', 'Station/Stop deleted successfully.');
    }
}
