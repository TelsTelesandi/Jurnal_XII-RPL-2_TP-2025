@extends('layouts.app')

@section('content')
<div class="bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                @if(auth()->user()->role === 'Admin')
                    📋 Manajemen Orders Ecommerce
                @else
                    📋 Pesanan Saya
                @endif
            </h1>
            @if(auth()->user()->role === 'Admin')
                <div class="flex gap-2">
                    <a href="{{ route('orders.index', ['pending_validation' => 1]) }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-white text-sm font-semibold bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 shadow-md transition-all">
                        ⏳ Pending Validasi
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Search & Filter Form -->
        <div class="mt-4">
            <form method="get" action="{{ route('orders.index') }}" class="flex flex-wrap items-center gap-3 bg-white/70 backdrop-blur rounded-xl p-4 border border-gray-200">
                <div class="flex-1 min-w-[240px]">
                    <input type="text" name="q" value="{{ request('q') }}" 
                           placeholder="Cari order code, nama customer, telepon..." 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                @if(auth()->user()->role === 'Admin')
                    <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validated</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-lg text-white text-sm font-semibold bg-gray-700 hover:bg-gray-800 transition-colors">
                    🔍 Filter
                </button>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-lg text-gray-700 text-sm font-semibold bg-gray-200 hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
            @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- Order Info -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $order->code }}</div>
                        <div class="text-xs text-gray-500">{{ $order->items->count() }} produk</div>
                    </td>
                    
                    <!-- Customer Info -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                        <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                        @if($order->customer_email)
                            <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
                        @endif
                    </td>
                    
                    <!-- Total Amount -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $order->formatted_total_amount }}</div>
                        <div class="text-xs text-gray-500">{{ $order->payment_method === 'cod' ? 'COD' : 'Transfer' }}</div>
                    </td>
                    
                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status_badge_color }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        @if($order->status === 'validated' && $order->validator)
                            <div class="text-xs text-gray-500 mt-1">oleh {{ $order->validator->name }}</div>
                        @endif
                        @if($order->status === 'assigned' && $order->assignedDriver)
                            <div class="text-xs text-gray-500 mt-1">ke {{ $order->assignedDriver->name }}</div>
                        @endif
                    </td>
                    
                    <!-- Date -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $order->created_at?->format('d/m/Y') }}</div>
                        <div class="text-xs">{{ $order->created_at?->format('H:i') }}</div>
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex flex-col gap-1">
                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors w-max">
                                👁️ Detail
                            </a>
                            
                            @if(auth()->user()->role === 'Admin')
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('orders.validate', $order) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 transition-colors" 
                                                onclick="return confirm('Validasi order ini?')">
                                            ✅ Validasi
                                        </button>
                                    </form>
                                @endif
                                
                                @if($order->status === 'validated')
                                    <button onclick="showAssignModal({{ $order->id }})" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors">
                                        👤 Assign Driver
                                    </button>
                                @endif
                                
                                @if(in_array($order->status, ['pending', 'validated']))
                                    <form method="POST" action="{{ route('orders.cancel', $order) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition-colors" 
                                                onclick="return confirm('Batalkan order ini?')">
                                            ❌ Batalkan
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada orders</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat order pertama Anda.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<!-- Assign Driver Modal -->
<div id="assignModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 w-96 shadow-2xl rounded-2xl bg-white border border-gray-200">
        <div class="mt-1">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Assign Driver</h3>
            <form id="assignForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Pilih Driver:</label>
                    <select name="driver_id" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Driver --</option>
                        @foreach(\App\Models\User::where('role', 'Driver')->get() as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 transition-all shadow-md">
                        Assign
                    </button>
                    <button type="button" onclick="hideAssignModal()" class="flex-1 px-4 py-2.5 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAssignModal(orderId) {
    const modal = document.getElementById('assignModal');
    const form = document.getElementById('assignForm');
    form.action = `/orders/${orderId}/assign`;
    modal.classList.remove('hidden');
}

function hideAssignModal() {
    const modal = document.getElementById('assignModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAssignModal();
    }
});
</script>
@endsection
