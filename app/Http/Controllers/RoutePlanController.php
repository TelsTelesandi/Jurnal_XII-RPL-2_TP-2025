<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RoutePlan;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RoutePlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = RoutePlan::query()->latest()->paginate(10);
        return view('route_plans.index', compact('plans'));
    }

    public function create()
    {
        $orders = Order::query()->where('status', 'pending')->whereNotNull('destination_lat')->whereNotNull('destination_lng')->latest()->limit(100)->get();
        return view('route_plans.create', compact('orders'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'plan_date' => ['required','date'],
            'order_ids' => ['required','array','min:1'],
            'order_ids.*' => ['integer','exists:orders,id'],
            'start_lat' => ['nullable','numeric','between:-90,90'],
            'start_lng' => ['nullable','numeric','between:-180,180'],
        ]);

        $orders = Order::whereIn('id', $data['order_ids'])->get();
        if ($orders->isEmpty()) {
            return back()->with('success', 'Tidak ada order valid dipilih.');
        }

        // Starting point
        $startLat = $data['start_lat'] ?? $orders->first()->destination_lat;
        $startLng = $data['start_lng'] ?? $orders->first()->destination_lng;

        // Nearest Neighbor heuristic
        $remaining = $orders->values();
        $seqOrders = [];
        $curLat = (float)$startLat; $curLng = (float)$startLng;
        while ($remaining->count() > 0) {
            $nextIdx = 0; $bestDist = INF;
            foreach ($remaining as $i => $ord) {
                $dist = $this->haversine($curLat, $curLng, (float)$ord->destination_lat, (float)$ord->destination_lng);
                if ($dist < $bestDist) { $bestDist = $dist; $nextIdx = $i; }
            }
            $next = $remaining->splice($nextIdx, 1)->first();
            $seqOrders[] = [$next, $bestDist];
            $curLat = (float)$next->destination_lat; $curLng = (float)$next->destination_lng;
        }

        $plan = RoutePlan::create([
            'code' => 'RTP-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
            'plan_date' => Carbon::parse($data['plan_date']),
            'total_distance_km' => 0,
            'total_duration_min' => 0,
            'status' => 'planned',
        ]);

        $eta = Carbon::parse($data['plan_date'])->setTime(8,0); // start at 08:00
        $totalDist = 0; $totalDur = 0; $seq = 1; $prevLat = (float)$startLat; $prevLng = (float)$startLng;
        foreach ($seqOrders as [$ord, $distFromPrev]) {
            $speedKmh = 40; // approx city speed
            $durMin = (int) round(($distFromPrev / $speedKmh) * 60);
            $eta = $eta->copy()->addMinutes($durMin);

            RouteStop::create([
                'route_plan_id' => $plan->id,
                'order_id' => $ord->id,
                'seq' => $seq++,
                'lat' => $ord->destination_lat,
                'lng' => $ord->destination_lng,
                'eta' => $eta,
                'status' => 'pending',
                'distance_from_prev_km' => $distFromPrev,
                'duration_from_prev_min' => $durMin,
            ]);

            $totalDist += $distFromPrev; $totalDur += $durMin;
            $prevLat = (float)$ord->destination_lat; $prevLng = (float)$ord->destination_lng;

            // Update order status to planned
            $ord->update(['status' => 'planned']);
        }

        $plan->update(['total_distance_km' => $totalDist, 'total_duration_min' => $totalDur]);

        return redirect()->route('route-plans.show', $plan)->with('success', 'Route Plan berhasil dibuat.');
    }

    public function show(RoutePlan $routePlan)
    {
        $routePlan->load(['routeStops.order', 'assignment.trackingEvents']);
        $lastEvent = $routePlan->assignment?->trackingEvents->sortByDesc('occurred_at')->first();
        $drivers = null; $vehicles = null;
        if (auth()->check() && auth()->user()->role === 'Admin') {
            $drivers = User::query()->where('role', 'Driver')->orderBy('name')->get();
            $vehicles = Vehicle::query()
                ->where('active', true)
                ->whereDoesntHave('assignments', function ($q) {
                    $q->whereIn('status', ['assigned','in_progress']);
                })
                ->orderBy('plate')
                ->get();
        }
        return view('route_plans.show', compact('routePlan', 'lastEvent', 'drivers', 'vehicles'));
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }
}
