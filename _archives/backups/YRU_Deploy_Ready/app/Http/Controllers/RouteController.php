<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\ElectricTrain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    // Retrieve all routes with their polyline and stations
    public function apiIndex()
    {
        $routes = Route::with(['routeStops.station'])->get();
        return response()->json($routes);
    }

    // Create or update a route
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'route_code' => 'required|string|max:100',
            'route_name' => 'required|string|max:255',
            'route_details' => 'nullable|string|max:255',
            'route_color' => 'nullable|string|max:50',
            'polyline_data' => 'nullable|array',
            'stops' => 'nullable|array',
            'stops.*.parking_spot_code' => 'required|string',
            'stops.*.stop_order' => 'required|integer',
        ]);

        return DB::transaction(function () use ($validated) {
            $route = Route::updateOrCreate(
                ['route_code' => $validated['route_code']],
                [
                    'route_name' => $validated['route_name'],
                    'route_details' => $validated['route_details'] ?? null,
                    'route_color' => $validated['route_color'] ?? '#ec4899',
                    'polyline_data' => $validated['polyline_data'] ?? [],
                ]
            );

            // Sync stops by deleting old and creating new
            RouteStop::where('route_code', $route->route_code)->delete();

            if (!empty($validated['stops'])) {
                foreach ($validated['stops'] as $stop) {
                    RouteStop::create([
                        'route_code' => $route->route_code,
                        'parking_spot_code' => $stop['parking_spot_code'],
                        'stop_order' => $stop['stop_order'],
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Route saved successfully',
                'data' => $route->load('routeStops.station')
            ], 200);
        });
    }

    // Update an existing route
    public function apiUpdate(Request $request, $route_code)
    {
        $decodedCode = urldecode($route_code);
        $route = Route::where('route_code', $route_code)->orWhere('route_code', $decodedCode)->first();

        $validated = $request->validate([
            'route_code' => 'nullable|string|max:100',
            'route_name' => 'required|string|max:255',
            'route_details' => 'nullable|string|max:255',
            'route_color' => 'nullable|string|max:50',
            'polyline_data' => 'nullable|array',
            'stops' => 'nullable|array',
            'stops.*.parking_spot_code' => 'required|string',
            'stops.*.stop_order' => 'required|integer',
        ]);

        return DB::transaction(function () use ($route, $route_code, $decodedCode, $validated) {
            $newCode = $validated['route_code'] ?? $decodedCode;

            if ($route) {
                $route->update([
                    'route_code' => $newCode,
                    'route_name' => $validated['route_name'],
                    'route_details' => $validated['route_details'] ?? null,
                    'route_color' => $validated['route_color'] ?? '#ec4899',
                    'polyline_data' => $validated['polyline_data'] ?? [],
                ]);
            } else {
                $route = Route::create([
                    'route_code' => $newCode,
                    'route_name' => $validated['route_name'],
                    'route_details' => $validated['route_details'] ?? null,
                    'route_color' => $validated['route_color'] ?? '#ec4899',
                    'polyline_data' => $validated['polyline_data'] ?? [],
                ]);
            }

            // Sync stops by deleting and recreating
            RouteStop::where('route_code', $route_code)->orWhere('route_code', $newCode)->orWhere('route_code', $decodedCode)->delete();

            if (!empty($validated['stops'])) {
                foreach ($validated['stops'] as $stop) {
                    RouteStop::create([
                        'route_code' => $newCode,
                        'parking_spot_code' => $stop['parking_spot_code'],
                        'stop_order' => $stop['stop_order'],
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => $route->load('routeStops.station')
            ]);
        });
    }

    // Delete a route
    public function apiDestroy($route_code)
    {
        $decodedCode = urldecode($route_code);
        RouteStop::where('route_code', $route_code)->orWhere('route_code', $decodedCode)->delete();
        Route::where('route_code', $route_code)->orWhere('route_code', $decodedCode)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Route deleted successfully'
        ]);
    }

    // Assign route to EV Train
    public function apiAssignTrain(Request $request)
    {
        $validated = $request->validate([
            'skytrain_code' => 'required|string|exists:electric_trains,skytrain_code',
            'route_code' => 'nullable|string|exists:routes,route_code',
        ]);

        $train = ElectricTrain::findOrFail($validated['skytrain_code']);
        $train->update([
            'route_code' => $validated['route_code'] ?? null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Train assigned to route successfully',
            'data' => $train
        ]);
    }
}
