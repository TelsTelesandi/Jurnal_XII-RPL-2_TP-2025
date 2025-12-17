<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTrackingEvent;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role === 'Admin') {
            // Admin can see all orders with filtering options
            $query = Order::with(['customer', 'items.product', 'validator', 'assignedDriver', 'delivery']);
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter pending validation for admin dashboard
            if ($request->filled('pending_validation')) {
                $query->pendingValidation();
            }
            
            // Search functionality
            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function($subQuery) use ($q) {
                    $subQuery->where('code', 'like', "%$q%")
                            ->orWhere('customer_name', 'like', "%$q%")
                            ->orWhere('customer_phone', 'like', "%$q%")
                            ->orWhere('shipping_address', 'like', "%$q%");
                });
            }
            
            $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        } elseif ($user->role === 'Driver') {
            $orders = Order::with(['customer', 'items.product', 'validator', 'assignedDriver', 'delivery'])
                ->where('assigned_driver_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            // Customer can only see their own orders
            $orders = Order::with(['items.product','delivery'])
                ->where('customer_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }
        
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        if ((string) (auth()->user()->role ?? '') !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat membuat pemesanan.');
        }
        return view('orders.create');
    }

    public function store(Request $request)
    {
        if ((string) (auth()->user()->role ?? '') !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat membuat pemesanan.');
        }
        $data = $this->validatedData($request);
        $data['code'] = 'ORD-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
        $data['status'] = $data['status'] ?? 'pending';

        // Jika Customer membuat order, set customer_id otomatis ke dirinya
        $data['customer_id'] = Auth::id();

        Order::create($data);

        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    public function edit(Order $order)
    {
        if ((string) (Auth::user()->role ?? '') === 'Customer' && $order->customer_id !== Auth::id()) {
            abort(403);
        }
        if ((string) (Auth::user()->role ?? '') === 'Driver') {
            abort(403);
        }
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        if ((string) (Auth::user()->role ?? '') === 'Customer' && $order->customer_id !== Auth::id()) {
            abort(403);
        }
        if ((string) (Auth::user()->role ?? '') === 'Driver') {
            abort(403);
        }
        $data = $this->validatedData($request, update: true);
        // Pastikan Customer tidak bisa memindahkan kepemilikan order
        if ((string) (Auth::user()->role ?? '') === 'Customer') {
            $data['customer_id'] = $order->customer_id; // lock ownership
        }
        $order->update($data);

        return redirect()->route('orders.index')->with('success', 'Order berhasil diperbarui.');
    }

    public function destroy(Order $order)
    {
        if ((string) (Auth::user()->role ?? '') === 'Customer' && $order->customer_id !== Auth::id()) {
            abort(403);
        }
        if ((string) (Auth::user()->role ?? '') === 'Driver') {
            abort(403);
        }
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus.');
    }

    public function show(Order $order)
    {
        if ((string) (Auth::user()->role ?? '') === 'Customer' && $order->customer_id !== Auth::id()) {
            abort(403);
        }
        return view('orders.show', compact('order'));
    }

    private function validatedData(Request $request, bool $update = false): array
    {
        $rules = [
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'origin_name' => ['nullable', 'string', 'max:255'],
            'origin_address' => ['nullable', 'string', 'max:1000'],
            'origin_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_name' => ['nullable', 'string', 'max:255'],
            'destination_address' => ['nullable', 'string', 'max:1000'],
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'window_from' => ['nullable', 'date'],
            'window_to' => ['nullable', 'date', 'after_or_equal:window_from'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,planned,assigned,loading,in_transit,delivered,cancelled'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        // Normalisasi datetime-local (HTML) ke format datetime
        foreach (['window_from', 'window_to'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Carbon::make($data[$field]);
            }
        }

        return $data;
    }

    public function validate(Order $order)
    {
        // Only admin can validate orders
        if (auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $order->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);

        return back()->with('success', 'Order berhasil divalidasi!');
    }

    public function assign(Request $request, Order $order)
    {
        // Only admin can assign orders to drivers
        if (auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        // Block assigning if driver already has an active task (assigned/loading/in_transit)
        $isBusy = Order::where('assigned_driver_id', $request->driver_id)
            ->whereIn('status', ['assigned','loading','in_transit'])
            ->exists();
        if ($isBusy) {
            return back()->with('error', 'Driver masih memiliki tugas aktif dan tidak dapat menerima penugasan baru hingga tugas sebelumnya selesai.');
        }

        $order->update([
            'status' => 'assigned',
            'assigned_driver_id' => $request->driver_id,
            'assigned_at' => now()
        ]);

        return back()->with('success', 'Order berhasil di-assign ke driver!');
    }

    public function cancel(Order $order)
    {
        // Admin or order owner can cancel
        if (auth()->user()->role !== 'Admin' && $order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order berhasil dibatalkan!');
    }

    // Driver accepts the assignment; optionally records initial location and switches order to in_transit
    public function driverAccept(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user->role !== 'Driver' || (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }

        $order->update([
            'driver_accepted_at' => now(),
            'driver_declined_at' => null,
            'driver_decline_reason' => null,
            'status' => 'in_transit',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Anda menerima penugasan. Silakan lakukan pengiriman.');
    }

    // Driver declines; unassign and revert to validated for admin to reassign
    public function driverDecline(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user->role !== 'Driver' || (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required','string','max:255']
        ]);

        $order->update([
            'status' => 'validated',
            'assigned_driver_id' => null,
            'assigned_at' => null,
            'driver_accepted_at' => null,
            'driver_declined_at' => now(),
            'driver_decline_reason' => $data['reason'] ?? null,
        ]);

        // Notify all admins with the decline reason and link to confirm
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Driver menolak penugasan - ' . $order->code,
                'body' => 'Alasan: ' . ($data['reason'] ?? '-'),
                'link_url' => route('orders.show', $order),
            ]);
        }

        return redirect()->route('orders.index')->with('success', 'Anda menolak penugasan. Order dikembalikan ke admin.');
    }

    // Driver tracking page
    public function trackView(Order $order)
    {
        $user = auth()->user();
        if ($user->role !== 'Driver' || (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }
        return view('orders.driver_tracking', compact('order'));
    }

    // Record driver location for this order
    public function track(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user->role !== 'Driver' || (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }

        $payload = $request->validate([
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
            'speed_kmh' => ['nullable','numeric','min:0'],
            'heading_deg' => ['nullable','numeric','min:0','max:360'],
            'accuracy_m' => ['nullable','numeric','min:0'],
            'occurred_at' => ['nullable','date'],
            'type' => ['nullable','in:gps,status_update'],
        ]);

        OrderTrackingEvent::create([
            'order_id' => $order->id,
            'lat' => $payload['lat'],
            'lng' => $payload['lng'],
            'speed_kmh' => $payload['speed_kmh'] ?? null,
            'heading_deg' => $payload['heading_deg'] ?? null,
            'accuracy_m' => $payload['accuracy_m'] ?? null,
            'occurred_at' => isset($payload['occurred_at']) ? Carbon::parse($payload['occurred_at']) : now(),
            'type' => $payload['type'] ?? 'gps',
        ]);

        if ($order->status !== 'in_transit') {
            $order->update(['status' => 'in_transit']);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with('success', 'Lokasi terekam.');
    }

    // Latest location for live tracking
    public function latest(Order $order)
    {
        $event = $order->trackingEvents()->orderByDesc('occurred_at')->first();
        if (!$event) {
            return response()->json(null);
        }
        return response()->json([
            'lat' => (float) $event->lat,
            'lng' => (float) $event->lng,
            'occurred_at' => optional($event->occurred_at)->toIso8601String(),
        ]);
    }

    // SSE stream for live map
    public function stream(Order $order)
    {
        @ini_set('zend.assertions', 0);
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');
        ignore_user_abort(true);
        set_time_limit(0);

        return response()->stream(function () use ($order) {
            $lastTs = null;
            for ($i = 0; $i < 120; $i++) {
                $event = $order->trackingEvents()->orderByDesc('occurred_at')->first();
                $payload = null;
                if ($event) {
                    $ts = optional($event->occurred_at)->timestamp;
                    if ($ts !== $lastTs) {
                        $lastTs = $ts;
                        $payload = [
                            'lat' => (float) $event->lat,
                            'lng' => (float) $event->lng,
                            'occurred_at' => optional($event->occurred_at)->toIso8601String(),
                        ];
                    }
                }
                if ($payload !== null) {
                    echo 'data: ' . json_encode($payload) . "\n\n";
                    @ob_flush();
                    @flush();
                }
                usleep(2500000);
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

// Admin confirms the driver's decline reason acknowledgment
public function confirmDriverDecline(Order $order)
{
if (auth()->user()->role !== 'Admin') {
    abort(403);
}
if (!$order->driver_declined_at) {
    return back()->with('error', 'Tidak ada penolakan driver untuk dikonfirmasi.');
}
$order->update([
    'driver_decline_admin_confirmed_at' => now(),
    'driver_decline_admin_confirmed_by' => auth()->id(),
]);

// Optional: notify driver that admin has acknowledged
if ($order->assigned_driver_id) {
    Notification::create([
        'user_id' => $order->assigned_driver_id,
        'title' => 'Konfirmasi Admin atas penolakan - ' . $order->code,
        'body' => 'Admin telah mengonfirmasi alasan penolakan Anda.',
        'link_url' => route('orders.show', $order),
    ]);
}

return back()->with('success', 'Alasan penolakan driver telah dikonfirmasi admin.');
}
}
