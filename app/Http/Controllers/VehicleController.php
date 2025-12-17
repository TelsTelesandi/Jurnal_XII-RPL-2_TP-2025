<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $vehicles = Vehicle::query()
            ->with(['assignments' => function ($q2) {
                $q2->whereIn('status', ['assigned', 'in_progress'])
                   ->latest()
                   ->with(['driver']);
            }])
            ->when($q, function ($query) use ($q) {
                $query->where(function($qq) use ($q) {
                    $qq->where('plate', 'like', "%$q%")
                       ->orWhere('name', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        // Data untuk form Assign inline: daftar driver
        $drivers = User::query()->where('role', 'Driver')->orderBy('name')->get();

        return view('vehicles.index', compact('vehicles', 'q', 'drivers'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plate' => ['required','string','max:50','unique:vehicles,plate'],
            'name' => ['nullable','string','max:255'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = (bool)($data['active'] ?? true);
        Vehicle::create($data);
        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dibuat.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'plate' => ['required','string','max:50','unique:vehicles,plate,'.$vehicle->id],
            'name' => ['nullable','string','max:255'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = (bool)($data['active'] ?? true);
        $vehicle->update($data);

        // Jika kendaraan dinonaktifkan, lepaskan dari assignment aktif (assigned/in_progress)
        if (!$vehicle->active) {
            \App\Models\Assignment::where('vehicle_id', $vehicle->id)
                ->whereIn('status', ['assigned','in_progress'])
                ->update(['vehicle_id' => null]);
        }
        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dihapus.');
    }
}
