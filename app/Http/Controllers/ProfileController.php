<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Assignment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)->latest()->limit(10)->get();

        $pendingOrders = collect();
        $completedOrders = collect();
        $deliveredByDriver = collect();
        $driverStatus = null;
        $driverVehicle = null;

        if ($user->role === 'Customer') {
            $pendingOrders = Order::with('items')
                ->where('customer_id', $user->id)
                ->whereIn('status', ['pending','validated','assigned','in_transit'])
                ->latest()->limit(10)->get();
            $completedOrders = Order::with('items')
                ->where('customer_id', $user->id)
                ->where('status', 'delivered')
                ->latest()->limit(10)->get();
        } elseif ($user->role === 'Driver') {
            $deliveredByDriver = Order::with('items')
                ->where('assigned_driver_id', $user->id)
                ->where('status', 'delivered')
                ->latest()->limit(10)->get();
            $hasInTransit = Order::where('assigned_driver_id', $user->id)->where('status','in_transit')->exists();
            $hasAssigned = Order::where('assigned_driver_id', $user->id)->where('status','assigned')->exists();
            $driverStatus = $hasInTransit ? 'Dalam Perjalanan' : ($hasAssigned ? 'Menunggu Konfirmasi / Mulai' : 'Idle');

            // Current vehicle from active assignment (assigned/in_progress)
            $currentAssignment = Assignment::with('vehicle')
                ->where('driver_id', $user->id)
                ->whereIn('status', ['assigned','in_progress'])
                ->latest()
                ->first();
            $driverVehicle = $currentAssignment?->vehicle;
        }

        return view('account.show', compact('user','notifications','pendingOrders','completedOrders','deliveredByDriver','driverStatus','driverVehicle'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'phone' => ['nullable','string','max:30'],
            'address' => ['nullable','string','max:1000'],
            'bio' => ['nullable','string','max:1000'],
            'avatar_url' => ['nullable','url'],
        ]);
        $user->update($data);
        return redirect()->route('account.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
