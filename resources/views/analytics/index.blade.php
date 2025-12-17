@extends('layouts.app')

@section('content')
<div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
    <!-- Header with Gradient -->
    <div class="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-8 py-10">
        <div class="absolute inset-0 bg-black opacity-5"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Analytics Dashboard
            </h1>
            <p class="mt-2 text-blue-100 text-lg">Ringkasan performa dan metrik operasional distribusi real-time</p>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="px-8 py-8 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="group bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wide mb-2">Total Delivered</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $deliveredTotal }}</p>
                        <div class="flex items-center gap-1 text-blue-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                            <span>All time</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-green-500 via-green-600 to-emerald-700 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium uppercase tracking-wide mb-2">On-Time Delivery</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $deliveredOnTime }}</p>
                        <div class="flex items-center gap-1 text-green-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Success rate</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            

            <div class="group bg-gradient-to-br from-amber-500 via-yellow-600 to-orange-600 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium uppercase tracking-wide mb-2">Active Vehicles</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $activeVehicles }}</p>
                        <div class="flex items-center gap-1 text-yellow-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                            </svg>
                            <span>In fleet</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2v0a2 2 0 01-2-2v-5a2 2 0 00-2-2H8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-indigo-500 via-indigo-600 to-blue-700 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium uppercase tracking-wide mb-2">Drivers</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $drivers }}</p>
                        <div class="flex items-center gap-1 text-indigo-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span>Total active</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-red-500 via-rose-600 to-pink-600 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium uppercase tracking-wide mb-2">Assignments</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $assignToday }}</p>
                        <div class="flex items-center gap-1 text-red-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span>Today</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-600 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium uppercase tracking-wide mb-2">Stock In</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $stockInTotal }}</p>
                        <div class="flex items-center gap-1 text-emerald-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span>30 hari: {{ $stockIn30 }}</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-rose-500 via-fuchsia-600 to-purple-600 rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-rose-100 text-sm font-medium uppercase tracking-wide mb-2">Stock Out</p>
                        <p class="text-4xl font-extrabold text-white mb-1">{{ $stockOutTotal }}</p>
                        <div class="flex items-center gap-1 text-rose-100 text-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>30 hari: {{ $stockOut30 }}</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3 group-hover:bg-white/30 transition-colors">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="space-y-8">
            @php
                $hasOrdersTrend = collect($ordersLast7Days ?? [])->sum() > 0;
                $hasRevenueTrend = collect($revenueLast7Days ?? [])->sum() > 0;
                $hasStatusChart = collect($ordersByStatus ?? [])->sum() > 0;
                $hasTopProducts = ($topProducts ?? collect())->count() > 0;
                $hasMonthlyComparison = (int)($previousMonth ?? 0) > 0 || (int)($currentMonth ?? 0) > 0;
            @endphp
            <!-- Orders Trend & Revenue -->
            @if($hasOrdersTrend || $hasRevenueTrend)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Orders Chart -->
                @if($hasOrdersTrend)
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-3 rounded-xl shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Tren Pesanan Harian</h3>
                            <p class="text-sm text-gray-600">7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-emerald-50 rounded-xl p-4">
                        <canvas id="chartDailyOrders" height="200"></canvas>
                    </div>
                </div>
                @endif

                <!-- Daily Revenue Chart -->
                @if($hasRevenueTrend)
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-xl shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Pendapatan Harian</h3>
                            <p class="text-sm text-gray-600">7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-purple-50 rounded-xl p-4">
                        <canvas id="chartDailyRevenue" height="200"></canvas>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Orders Status Chart -->
            @if($hasStatusChart)
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 p-3 rounded-xl shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Distribusi Status Pesanan</h3>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-6 shadow-sm border border-blue-100">
                        <canvas id="chartOrdersByStatus" height="200"></canvas>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl p-6 shadow-sm border border-indigo-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b-2 border-indigo-200">
                                        <th class="px-4 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-indigo-100">
                                @forelse($ordersByStatus as $status => $total)
                                    <tr class="hover:bg-indigo-50/50 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-gray-900 bg-white px-3 py-1 rounded-full border border-gray-200 shadow-sm">{{ ucfirst($status) }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="text-lg font-bold text-indigo-600">{{ $total }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Top Products & Monthly Comparison -->
            @if($hasTopProducts || $hasMonthlyComparison)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Products Chart -->
                @if($hasTopProducts)
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-xl shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Produk Terlaris</h3>
                            <p class="text-sm text-gray-600">Berdasarkan quantity terjual</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-orange-50 rounded-xl p-4">
                        <canvas id="chartTopProducts" height="200"></canvas>
                    </div>
                </div>
                @endif

                <!-- Monthly Comparison -->
                @if($hasMonthlyComparison)
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-xl shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Perbandingan Bulanan</h3>
                            <p class="text-sm text-gray-600">Bulan ini vs bulan lalu</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-cyan-50 rounded-xl p-4">
                        <canvas id="chartMonthlyComparison" height="200"></canvas>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function(){
        function initAnalyticsCharts(){
            Chart.defaults.font.family = 'Inter, sans-serif';
            Chart.defaults.color = '#6B7280';
            Chart.defaults.plugins.legend.display = false;
            Chart.defaults.scales.x.grid.display = false;
            Chart.defaults.scales.y.beginAtZero = true;
            const noDataPlugin = { id: 'noData', afterDraw(chart, args, opts){
                const hasData = chart.data && chart.data.datasets && chart.data.datasets.some(ds => Array.isArray(ds.data) && ds.data.some(v => Number(v) !== 0));
                if (hasData) return;
                const area = chart.chartArea; if (!area) return;
                const ctx = chart.ctx; ctx.save();
                ctx.fillStyle = (opts && opts.color) || '#9CA3AF';
                ctx.font = '600 12px Inter, sans-serif';
                ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                ctx.fillText((opts && opts.text) || 'Tidak ada data', area.left + area.width/2, area.top + area.height/2);
                ctx.restore();
            }};
            Chart.register(noDataPlugin);

        // Orders by Status Chart (Doughnut)
            const ordersByStatus = @json($ordersByStatus);
            const labelsStatus = Object.keys(ordersByStatus);
            const dataStatus = Object.values(ordersByStatus).map(v=>Number(v));
            const statusColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
        
            const ctx1 = document.getElementById('chartOrdersByStatus');
            if (ctx1) new Chart(ctx1, {
            type: 'doughnut',
            data: { 
                labels: labelsStatus.map(s => s.charAt(0).toUpperCase() + s.slice(1)), 
                datasets: [{ 
                    data: dataStatus, 
                    backgroundColor: statusColors.slice(0, labelsStatus.length),
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }] 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { size: 12, weight: 'bold' }
                        }
                    }
                }
            }
        });

        // Daily Orders Chart (Line)
            const ordersLast7Days = @json($ordersLast7Days);
            const orderDates = Object.keys(ordersLast7Days);
            const orderCounts = Object.values(ordersLast7Days);
        
                const ctx2 = document.getElementById('chartDailyOrders');
            if (ctx2) { const g2 = ctx2.getContext('2d').createLinearGradient(0,0,0,200); g2.addColorStop(0,'rgba(16, 185, 129, 0.25)'); g2.addColorStop(1,'rgba(16, 185, 129, 0.03)'); new Chart(ctx2, {
            type: 'line',
            data: {
                labels: orderDates.map(date => new Date(date).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' })),
                datasets: [{
                    label: 'Pesanan',
                    data: orderCounts,
                    borderColor: '#10B981',
                    backgroundColor: g2,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    noData: { text: 'Tidak ada data' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        }); }

        // Daily Revenue Chart (Area)
            const revenueLast7Days = @json($revenueLast7Days);
            const revenueCounts = Object.values(revenueLast7Days);
        
                const ctx3 = document.getElementById('chartDailyRevenue');
            if (ctx3) { const g3 = ctx3.getContext('2d').createLinearGradient(0,0,0,200); g3.addColorStop(0,'rgba(139, 92, 246, 0.25)'); g3.addColorStop(1,'rgba(139, 92, 246, 0.03)'); new Chart(ctx3, {
            type: 'line',
            data: {
                labels: orderDates.map(date => new Date(date).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' })),
                datasets: [{
                    label: 'Pendapatan',
                    data: revenueCounts,
                    borderColor: '#8B5CF6',
                    backgroundColor: g3,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8B5CF6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    },
                    noData: { text: 'Tidak ada data' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                            }
                        }
                    }
                }
            }
        }); }

        // Top Products Chart (Horizontal Bar)
            const topProducts = @json($topProducts);
            const productNames = topProducts.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name);
            const productQuantities = topProducts.map(p => p.total_quantity);
        
            const ctx4 = document.getElementById('chartTopProducts');
            if (ctx4) new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [{
                    label: 'Quantity Terjual',
                    data: productQuantities,
                    backgroundColor: [
                        '#F97316',
                        '#EF4444', 
                        '#F59E0B',
                        '#10B981',
                        '#3B82F6'
                    ],
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Monthly Comparison Chart (Bar)
            const currentMonth = @json($currentMonth);
            const previousMonth = @json($previousMonth);
        
            const ctx5 = document.getElementById('chartMonthlyComparison');
            if (ctx5) new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: ['Bulan Lalu', 'Bulan Ini'],
                datasets: [{
                    label: 'Pesanan',
                    data: [previousMonth, currentMonth],
                    backgroundColor: ['#06B6D4', '#0EA5E9'],
                    borderRadius: 12,
                    borderSkipped: false,
                    barThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        }
        if (typeof window.Chart !== 'undefined') {
            initAnalyticsCharts();
        } else {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            s.onload = initAnalyticsCharts;
            s.onerror = function(){
                var s2 = document.createElement('script');
                s2.src = 'https://unpkg.com/chart.js@4.4.1/dist/chart.umd.js';
                s2.onload = initAnalyticsCharts;
            };
            document.head.appendChild(s);
        }
    })();
</script>
@endpush
