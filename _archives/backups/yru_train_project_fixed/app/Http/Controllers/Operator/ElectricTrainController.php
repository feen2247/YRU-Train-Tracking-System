<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ElectricTrain;
use Illuminate\Http\Request;

class ElectricTrainController extends Controller
{
    public function index()
    {
        $trains = ElectricTrain::all();
        return view('operator.trains.index', compact('trains'));
    }

    public function create()
    {
        return view('operator.trains.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'skytrain_code' => 'required|string|max:10|unique:electric_trains,skytrain_code',
            'car_number' => 'required|string|max:10',
            'electric_train_type' => 'required|string|max:50',
            'car_status' => 'required|string|in:Active,Maintenance,Inactive',
        ]);

        ElectricTrain::create($request->all());

        return redirect()->route('operator.trains.index')->with('success', 'Electric Train created successfully.');
    }

    public function show($id)
    {
        $train = ElectricTrain::with('locations')->findOrFail($id);
        return view('operator.trains.show', compact('train'));
    }

    public function edit($id)
    {
        $train = ElectricTrain::findOrFail($id);
        return view('operator.trains.edit', compact('train'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'car_number' => 'required|string|max:10',
            'electric_train_type' => 'required|string|max:50',
            'car_status' => 'required|string|in:Active,Maintenance,Inactive',
        ]);

        $train = ElectricTrain::findOrFail($id);
        $train->update($request->only(['car_number', 'electric_train_type', 'car_status']));

        return redirect()->route('operator.trains.index')->with('success', 'Electric Train updated successfully.');
    }

    public function destroy($id)
    {
        $train = ElectricTrain::findOrFail($id);
        $train->delete();

        return redirect()->route('operator.trains.index')->with('success', 'Electric Train deleted successfully.');
    }
}
