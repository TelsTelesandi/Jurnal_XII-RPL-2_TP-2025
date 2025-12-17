@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Detail Route Plan: {{ $routePlan->code }}</h2>
        <div>
            <a class="btn" href="{{ route('route-plans.index') }}">Kembali</a>
            @if(auth()->check() && auth()->user()->role === 'Admin')
                @if(!$routePlan->assignment)
                    <form method="post" action="{{ route('assignments.store') }}" style="display:inline-flex; gap:8px; align-items:center; margin-left:8px;">
                        @csrf
                        <input type="hidden" name="route_plan_id" value="{{ $routePlan->id }}">
                        <select name="driver_id" required>
                            <option value="">-- Pilih Driver --</option>
                            @foreach(($drivers ?? []) as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->email }})</option>
                            @endforeach
                        </select>
                        <select name="vehicle_id">
                            <option value="">-- Tanpa Kendaraan --</option>
                            @foreach(($vehicles ?? []) as $v)
                                <option value="{{ $v->id }}">{{ $v->plate }} — {{ $v->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="submit">Assign</button>
                    </form>
                @else
                    <span style="margin-left:8px; font-size:14px; color:#374151;">
                        Assigned: <strong>{{ $routePlan->assignment->driver?->name ?? '-' }}</strong>
                        @if($routePlan->assignment->vehicle)
                            · Vehicle: <strong>{{ $routePlan->assignment->vehicle->plate }}</strong>
                        @endif
                    </span>
                @endif
            @endif
        </div>
    </div>
    <div style="margin-top:12px; display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px;">
        <div class="field">
            <label>Kode</label>
            <div>{{ $routePlan->code }}</div>
        </div>
        <div class="field">
            <label>Tanggal</label>
            <div>{{ $routePlan->plan_date?->format('Y-m-d') }}</div>
        </div>
        <div class="field">
            <label>Status</label>
            <div>{{ $routePlan->status }}</div>
        </div>
        <div class="field">
            <label>Jarak Total</label>
            <div>{{ number_format((float)$routePlan->total_distance_km, 2) }} km</div>
        </div>
        <div class="field">
            <label>Durasi Total</label>
            <div>{{ (int)$routePlan->total_duration_min }} menit</div>
        </div>
    </div>

    <h3>Stops</h3>

    {{-- Map removed intentionally --}}

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Order</th>
                <th>Lokasi</th>
                <th>ETA</th>
                <th>Status</th>
                <th>ΔJarak</th>
                <th>ΔDurasi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($routePlan->routeStops->sortBy('seq') as $s)
            <tr>
                <td>{{ $s->seq }}</td>
                <td>{{ $s->order?->code ?? ('Order #'.$s->order_id) }}</td>
                <td>{{ $s->lat }}, {{ $s->lng }}</td>
                <td>{{ $s->eta?->format('Y-m-d H:i') }}</td>
                <td>{{ $s->status }}</td>
                <td>{{ $s->distance_from_prev_km }} km</td>
                <td>{{ $s->duration_from_prev_min }} min</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{-- Scripts removed intentionally --}}
@endsection
