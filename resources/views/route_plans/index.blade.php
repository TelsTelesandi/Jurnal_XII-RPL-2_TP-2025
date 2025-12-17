@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Route Plans</h2>
        <a class="btn btn-primary" href="{{ route('route-plans.create') }}">+ Buat Route Plan</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Jarak Total</th>
                <th>Durasi Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($plans as $p)
            <tr>
                <td>{{ $p->code }}</td>
                <td>{{ $p->plan_date?->format('Y-m-d') }}</td>
                <td>{{ $p->status }}</td>
                <td>{{ number_format((float)$p->total_distance_km, 2) }} km</td>
                <td>{{ (int)$p->total_duration_min }} menit</td>
                <td>
                    <a class="btn" href="{{ route('route-plans.show', $p) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Belum ada route plan.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $plans->links() }}
    </div>
</div>
@endsection
