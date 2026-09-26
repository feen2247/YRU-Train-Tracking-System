<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::withCount('stations')->get();
        return view('operator.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('operator.routes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'route_code' => 'required|string|max:10|unique:routes,route_code',
            'route_name' => 'required|string|max:100',
            'route_details' => 'nullable|string|max:255',
        ]);

        Route::create($request->all());

        return redirect()->route('operator.routes.index')->with('success', 'Route created successfully.');
    }

    public function show($id)
    {
        $route = Route::with(['stations' => function ($query) {
            $query->orderBy('order_of_parking_spots', 'asc');
        }])->findOrFail($id);

        return view('operator.routes.show', compact('route'));
    }

    public function edit($id)
    {
        $route = Route::findOrFail($id);
        return view('operator.routes.edit', compact('route'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'route_name' => 'required|string|max:100',
            'route_details' => 'nullable|string|max:255',
        ]);

        $route = Route::findOrFail($id);
        $route->update($request->only(['route_name', 'route_details']));

        return redirect()->route('operator.routes.index')->with('success', 'Route updated successfully.');
    }

    public function destroy($id)
    {
        $route = Route::findOrFail($id);
        $route->delete();

        return redirect()->route('operator.routes.index')->with('success', 'Route deleted successfully.');
    }
}
