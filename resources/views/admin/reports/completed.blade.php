@extends('layouts.app')

@section('content')
<div class="space-y-6" id="report-container">
    @php
        $isPrint = $isPrint ?? false;
        $ordersTotal = ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $orders->total() : $orders->count();
    @endphp
    @unless($isPrint)
    <!-- Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 shadow-2xl print:hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative px-8 py-12">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4 text-white">
                    <div class="bg-white/20 backdrop-blur-sm p-4 rounded-2xl shadow-lg">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-black mb-2">Laporan Penjualan</h1>
                        <p class="text-blue-100 text-lg font-medium">Analisis komprehensif pesanan yang telah diselesaikan</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 text-white">
                        <div class="text-xs font-medium opacity-80">Periode Aktif</div>
                        <div class="text-lg font-bold">
                            @if(request('start') && request('end'))
                                {{ \Carbon\Carbon::parse(request('start'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end'))->format('d/m/Y') }}
                            @else
                                Semua Data
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endunless

    <!-- Filter & Print Options -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 print:hidden">
        <!-- Filter Form -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-2 rounded-lg">
                    <i class="fas fa-filter text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Filter Laporan</h3>
            </div>
            <form method="get" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                        <input type="date" name="start" value="{{ request('start') }}" 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                        <input type="date" name="end" value="{{ request('end') }}" 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="flex-1 min-w-[120px] bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                        <i class="fas fa-search mr-2"></i>Terapkan Filter
                    </button>
                    <a href="{{ route('reports.completed') }}" class="px-6 py-3 rounded-xl border-2 border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold transition-colors">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Print Options -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 p-2 rounded-lg">
                    <i class="fas fa-print text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Cetak Data</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Hanya data tabel yang akan dicetak. Centang baris pada tabel untuk memilih data tertentu.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="printAllData()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow">
                    <i class="fas fa-file-export mr-2"></i> Print Seluruh Data
                </button>
                <button type="button" onclick="printSelectedData()" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold shadow">
                    <i class="fas fa-check-square mr-2"></i> Print Data Terpilih
                </button>
            </div>
            <div class="my-5 border-t border-gray-200"></div>
            <div>
                <h4 class="text-sm font-bold text-gray-900 mb-3">Opsi Cetak (versi awal)</h4>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                        <input type="checkbox" id="print-summary" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Ringkasan Penjualan</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                        <input type="checkbox" id="print-orders" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Daftar Pesanan</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                        <input type="checkbox" id="print-items" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Detail Item</span>
                    </label>
                </div>
                <button onclick="printReport()" class="w-full mt-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-print mr-2"></i>Cetak Laporan (versi awal)
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @unless($isPrint)
    <div id="summary-section" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium uppercase tracking-wide mb-2">Total Pesanan</p>
                    <p class="text-4xl font-black">{{ $ordersTotal }}</p>
                    <p class="text-emerald-200 text-sm mt-1">Pesanan selesai</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide mb-2">Total Item</p>
                    <p class="text-4xl font-black">{{ number_format($totalItems, 0, ',', '.') }}</p>
                    <p class="text-blue-200 text-sm mt-1">Item terjual</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <i class="fas fa-boxes text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-pink-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wide mb-2">Total Nilai</p>
                    <p class="text-3xl font-black">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                    <p class="text-purple-200 text-sm mt-1">Pendapatan kotor</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    @endunless

    <!-- Orders Table -->
    <div id="orders-section" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        @unless($isPrint)
        <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 p-2 rounded-lg">
                        <i class="fas fa-table text-white text-sm"></i>
                    </div>
                    Daftar Pesanan Selesai
                </h2>
                <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full border">
                    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $orders->count() }} dari {{ $orders->total() }} pesanan
                    @else
                        {{ $orders->count() }} pesanan
                    @endif
                </div>
            </div>
        </div>
        @endunless
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600 border-b border-gray-200">
                        @unless($isPrint)
                            <th class="px-6 py-4">
                                <input type="checkbox" id="check-all" class="w-4 h-4">
                            </th>
                        @endunless
                        <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider">Kode Order</th>
                        <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-gray-50/50 transition-colors order-row" data-id="{{ $o->id }}">
                            @unless($isPrint)
                            <td class="px-6 py-4">
                                <input type="checkbox" class="row-check w-4 h-4" value="{{ $o->id }}">
                            </td>
                            @endunless
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $o->code }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>{{ optional($o->delivery?->delivered_at)->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-medium">
                                {{ optional($o->delivery?->delivered_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $o->customer_name }}</div>
                                <div class="text-sm text-gray-600">{{ $o->customer_phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 max-w-xs">
                                <div class="truncate">{{ $o->shipping_address }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold text-lg text-gray-900">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</div>
                            </td>
                        </tr>
                        <tr class="bg-gray-50/30 order-items" id="items-{{ $o->id }}">
                            <td colspan="{{ $isPrint ? 5 : 6 }}" class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-1 rounded">
                                        <i class="fas fa-list text-white text-xs"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Detail Item Pesanan</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($o->items as $it)
                                        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">{{ $it->name }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Qty: <span class="font-medium text-gray-700">{{ $it->quantity }}</span> × 
                                                        Rp {{ number_format($it->price, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-bold text-emerald-600">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="bg-gray-100 p-4 rounded-full">
                                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                    </div>
                                    <div class="text-gray-500 font-medium">Tidak ada pesanan selesai pada rentang tanggal ini</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    /* Hide non-printable elements */
    nav, .print\:hidden, footer, .pagination, button, .border-t { 
        display: none !important; 
    }
    /* Ensure overflow content is printable */
    .overflow-x-auto { overflow: visible !important; }
    #orders-section .max-w-xs { max-width: none !important; white-space: normal !important; }
    .truncate { white-space: normal !important; overflow: visible !important; text-overflow: clip !important; }

    /* Reset layout for print */
    body { 
        margin: 0; 
        padding: 20px; 
        font-size: 12px; 
        line-height: 1.4;
        color: #000;
        background: #fff !important;
    }
    
    main { 
        padding: 0; 
        max-width: none;
    }
    
    /* Flatten cards/containers */
    .rounded-xl, .rounded-2xl { border-radius: 0 !important; }
    .bg-white, .bg-gray-50, .bg-gradient-to-r, .bg-gradient-to-br { background: #fff !important; }
    .border, .border-gray-100, .border-gray-200 { border-color: #ddd !important; }
    .shadow, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }
    
    /* Summary section for print */
    .print-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        border: 1px solid #ddd;
        background: #f9f9f9;
    }
    
    .print-summary-item {
        text-align: center;
    }
    
    .print-summary-item h3 {
        font-size: 14px;
        margin: 0 0 10px 0;
        color: #666;
        text-transform: uppercase;
    }
    
    .print-summary-item .value {
        font-size: 18px;
        font-weight: bold;
        color: #000;
    }
    
    /* Table styles for print */
    #orders-section table { width: 100%; border-collapse: collapse; }
    #orders-section thead th, 
    #orders-section tbody td { border: 1px solid #e5e7eb; padding: 8px; font-size: 11px; background: #fff !important; }
    #orders-section thead th { background: #f3f4f6 !important; font-weight: 700; text-transform: uppercase; }
    #orders-section td.text-right { text-align: right; }
    
    /* Items table */
    .print-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 10px;
    }
    
    .print-items-table th,
    .print-items-table td {
        border: 1px solid #ccc;
        padding: 5px;
    }
    
    .print-items-table th {
        background: #f5f5f5;
    }
    
    /* Page breaks */
    .page-break {
        page-break-before: always;
    }
    
    /* Hide elements based on checkboxes */
    .hide-summary #summary-section { display: none !important; }
    .hide-orders #orders-section .order-row { display: none !important; }
    .hide-items #orders-section .order-items { display: none !important; }
}

@page {
    margin: 1cm;
    size: A4;
}
</style>

<script>
(function(){
    function currentFilters(){
        const start = document.querySelector('input[name="start"]')?.value || '';
        const end = document.querySelector('input[name="end"]')?.value || '';
        const p = new URLSearchParams();
        if (start) p.set('start', start);
        if (end) p.set('end', end);
        return p;
    }

    window.printAllData = function(){
        const p = currentFilters();
        p.set('per','all');
        p.set('print','1');
        window.open("{{ route('reports.completed') }}" + '?' + p.toString(), '_blank');
    };

    window.printSelectedData = function(){
        const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
        if (!ids.length) {
            alert('Pilih minimal satu data untuk dicetak.');
            return;
        }
        const p = currentFilters();
        p.set('ids', ids.join(','));
        p.set('print','1');
        window.open("{{ route('reports.completed') }}" + '?' + p.toString(), '_blank');
    };

    const checkAll = document.getElementById('check-all');
    if (checkAll){
        checkAll.addEventListener('change', function(){
            document.querySelectorAll('.row-check').forEach(cb => { cb.checked = checkAll.checked; });
        });
    }

    // Legacy print with checkboxes (prints current page)
    window.printReport = function(){
        const printSummary = document.getElementById('print-summary')?.checked ?? false;
        const printOrders = document.getElementById('print-orders')?.checked ?? true;
        const printItems = document.getElementById('print-items')?.checked ?? true;

        document.body.classList.remove('hide-summary','hide-orders','hide-items');
        if (!printSummary) document.body.classList.add('hide-summary');
        if (!printOrders) document.body.classList.add('hide-orders');
        if (!printItems) document.body.classList.add('hide-items');

        setTimeout(() => {
            window.print();
            setTimeout(() => {
                document.body.classList.remove('hide-summary','hide-orders','hide-items');
            }, 300);
        }, 50);
    };

    @if(!empty($isPrint) && $isPrint)
    // Auto print when in print mode
    setTimeout(() => window.print(), 200);
    @endif
})();
</script>
@endsection
