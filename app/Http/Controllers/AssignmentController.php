<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Order;
// Tracking removed
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssignmentController extends Controller
{
    public function create(Request $request)
    {
        $drivers = User::query()->where('role', 'Driver')->orderBy('name')->get();
        $vehicles = Vehicle::query()->where('active', true)->orderBy('plate')->get();
        return view('assignments.create', compact('drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => ['required','integer','exists:users,id'],
            'vehicle_id' => ['nullable','integer','exists:vehicles,id'],
            'shift_start' => ['nullable','date'],
            'shift_end' => ['nullable','date','after_or_equal:shift_start'],
            'notes' => ['nullable','string'],
        ]);
        // Block if driver already has active assignment or is delivering another order
        $hasActiveAssignment = Assignment::where('driver_id', $data['driver_id'])
            ->whereIn('status', ['assigned','in_progress'])
            ->exists();
        $hasInTransitOrder = Order::where('assigned_driver_id', $data['driver_id'])
            ->where('status', 'in_transit')
            ->exists();
        if ($hasActiveAssignment || $hasInTransitOrder) {
            return back()->with('error', 'Driver sedang memiliki tugas aktif atau sedang dalam perjalanan. Pilih driver lain.');
        }

        // If a vehicle is provided, ensure it's active and not currently used in active assignments
        if (!empty($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            if (!$vehicle || !$vehicle->active) {
                return back()->with('error', 'Kendaraan tidak tersedia atau nonaktif.');
            }
            $vehicleBusy = Assignment::where('vehicle_id', $data['vehicle_id'])
                ->whereIn('status', ['assigned','in_progress'])
                ->exists();
            if ($vehicleBusy) {
                return back()->with('error', 'Kendaraan sedang dipakai pada assignment lain.');
            }
        }

        $assignment = Assignment::create([
            'driver_id' => $data['driver_id'],
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'shift_start' => isset($data['shift_start']) ? Carbon::parse($data['shift_start']) : null,
            'shift_end' => isset($data['shift_end']) ? Carbon::parse($data['shift_end']) : null,
            'status' => 'assigned',
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('vehicles.index')
            ->with('success', 'Assignment berhasil dibuat.');
    }

    // Tracking endpoints removed
}
