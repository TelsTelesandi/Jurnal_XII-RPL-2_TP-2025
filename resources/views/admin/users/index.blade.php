@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">👥 Manajemen Pengguna</h1>
            <p class="text-sm text-gray-600">Pantau Admin, Driver, dan Customer. Admin tidak dapat mengedit akun pengguna.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">+ Tambah Akun Admin/Driver</a>
    </div>

    <!-- Admins -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">👨‍💼 Daftar Admin</h2>
            <span class="text-xs px-2 py-1 rounded bg-gray-100">{{ $admins->count() }}</span>
        </div>
        <div class="divide-y">
            @forelse($admins as $u)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">{{ $u->name }}</div>
                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">Admin</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">Belum ada admin.</div>
            @endforelse
        </div>
    </div>

    <!-- Drivers -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">🚚 Driver</h2>
            <span class="text-xs px-2 py-1 rounded bg-gray-100">{{ $driverSummaries->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="text-left px-6 py-3">Nama</th>
                        <th class="text-left px-6 py-3">Telepon</th>
                        <th class="text-left px-6 py-3">Status</th>
                        <th class="text-right px-6 py-3">Assigned</th>
                        <th class="text-right px-6 py-3">In Transit</th>
                        <th class="text-right px-6 py-3">Delivered</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($driverSummaries as $row)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $row['d']->name }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $row['d']->phone ?: '-' }}</td>
                            <td class="px-6 py-3">
                                @if($row['status'] === 'Dalam Perjalanan')
                                    <span class="px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800">Busy</span>
                                @elseif($row['status'] === 'Menunggu Konfirmasi / Mulai')
                                    <span class="px-2 py-1 rounded text-xs bg-purple-100 text-purple-800">Assigned</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Idle</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">{{ $row['assigned'] }}</td>
                            <td class="px-6 py-3 text-right">{{ $row['inTransit'] }}</td>
                            <td class="px-6 py-3 text-right">{{ $row['delivered'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada driver.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customers -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">🧑‍💼 Customer</h2>
            <span class="text-xs px-2 py-1 rounded bg-gray-100">{{ $customers->total() }}</span>
        </div>
        <div class="divide-y">
            @forelse($customers as $u)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">{{ $u->name }}</div>
                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                        @if($u->phone)
                            <div class="text-xs text-gray-500">{{ $u->phone }}</div>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">Customer</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">Belum ada customer.</div>
            @endforelse
        </div>
        <div class="px-6 py-4">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
