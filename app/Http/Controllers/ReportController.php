<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function completed(Request $request)
    {
        $filters = $request->validate([
            'start' => ['nullable','date'],
            'end' => ['nullable','date','after_or_equal:start'],
        ]);

        $query = Order::with(['items.product','delivery','customer'])
            ->where('status', 'delivered');

        if (!empty($filters['start'])) {
            $query->whereHas('delivery', function($q) use ($filters) {
                $q->whereDate('delivered_at', '>=', $filters['start']);
            });
        }
        if (!empty($filters['end'])) {
            $query->whereHas('delivery', function($q) use ($filters) {
                $q->whereDate('delivered_at', '<=', $filters['end']);
            });
        }

        // Optional: filter by selected IDs for print selected
        $idsParam = $request->input('ids');
        if (!empty($idsParam)) {
            $ids = is_array($idsParam) ? $idsParam : array_filter(explode(',', (string) $idsParam));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        // Decide pagination or fetch all
        $perAll = $request->input('per') === 'all' || !empty($idsParam);
        if ($perAll) {
            $orders = $query->orderByDesc('id')->get();
        } else {
            $orders = $query->orderByDesc('id')->paginate(20)->withQueryString();
        }

        // Aggregates over the actual collection
        $collection = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->getCollection() : $orders;
        $grandTotal = $collection->sum('total_amount');
        $totalItems = $collection->reduce(function($carry, $order){
            return $carry + $order->items->sum('quantity');
        }, 0);

        $isPrint = (bool) $request->boolean('print');

        if ($isPrint) {
            return view('admin.reports.completed_print', compact('orders','filters','grandTotal','totalItems','isPrint'));
        }

        return view('admin.reports.completed', compact('orders','filters','grandTotal','totalItems','isPrint'));
    }
}
