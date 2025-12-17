<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Delivery;
// RoutePlan removed
use App\Models\Assignment;
// TrackingEvent removed
use App\Models\Vehicle;
use App\Models\User;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now = now();
        $last7 = $now->copy()->subDays(6)->startOfDay();
        $last30 = $now->copy()->subDays(30);

        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total','status');

        // Delivered vs delivered on-time (delivered_at <= window_to)
        $deliveredTotal = Delivery::count();
        $deliveredOnTime = Delivery::query()
            ->join('orders','orders.id','=','deliveries.order_id')
            ->whereNotNull('orders.window_to')
            ->whereColumn('deliveries.delivered_at','<=','orders.window_to')
            ->count();

        // Route plans & tracking removed from analytics

        // Active vehicles and drivers
        $activeVehicles = Vehicle::where('active',true)->count();
        $drivers = User::where('role','Driver')->count();

        // Assignments today
        $assignToday = Assignment::whereDate('created_at',$now->toDateString())->count();

        // Stock movements
        $stockInTotal = StockMovement::where('type','in')->sum('quantity');
        $stockOutTotal = StockMovement::where('type','out')->sum('quantity');
        $stockIn30 = StockMovement::where('type','in')->where('created_at','>=',$last30)->sum('quantity');
        $stockOut30 = StockMovement::where('type','out')->where('created_at','>=',$last30)->sum('quantity');

        // Daily orders for last 7 days
        $dailyOrders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $last7)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with 0
        $ordersLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $ordersLast7Days[$date] = $dailyOrders->get($date)?->total ?? 0;
        }

        // Revenue by day for last 7 days
        $dailyRevenue = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('created_at', '>=', $last7)
            ->where('status', 'delivered')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $revenueLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $revenueLast7Days[$date] = $dailyRevenue->get($date)?->total ?? 0;
        }

        // Top products by quantity sold
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->where('orders.status', 'delivered')
            ->groupBy('order_items.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Monthly comparison (current vs previous month)
        $currentMonth = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $previousMonth = Order::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->count();

        return view('analytics.index', compact(
            'ordersByStatus','deliveredTotal','deliveredOnTime','activeVehicles','drivers','assignToday',
            'stockInTotal','stockOutTotal','stockIn30','stockOut30','ordersLast7Days','revenueLast7Days',
            'topProducts','currentMonth','previousMonth'
        ));
    }
}
