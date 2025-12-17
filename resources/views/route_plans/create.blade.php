@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Buat Route Plan</h2>
    <form method="post" action="{{ route('route-plans.generate') }}">
        @csrf
        <div style="display:flex; gap:12px;">
            <div class="field" style="flex:1;">
                <label>Tanggal Rencana</label>
                <input type="date" name="plan_date" value="{{ old('plan_date', now()->toDateString()) }}" />
            </div>
            <div class="field" style="flex:1;">
                <label>Titik Mulai (opsional) - Latitude</label>
                <input type="number" step="0.0000001" name="start_lat" value="{{ old('start_lat') }}" />
            </div>
            <div class="field" style="flex:1;">
                <label>Titik Mulai (opsional) - Longitude</label>
                <input type="number" step="0.0000001" name="start_lng" value="{{ old('start_lng') }}" />
            </div>
        </div>

        <div class="field">
            <label>Pilih Orders (status pending dengan koordinat tujuan)</label>
            <div style="max-height:320px; overflow:auto; border:1px solid #e5e7eb; padding:8px; border-radius:6px;">
                @forelse($orders as $o)
                    <label style="display:flex; align-items:center; gap:8px; padding:4px 0;">
                        <input type="checkbox" name="order_ids[]" value="{{ $o->id }}" />
                        <span><strong>{{ $o->code ?? 'Order #'.$o->id }}</strong> — {{ $o->destination_name }} ({{ $o->destination_lat }}, {{ $o->destination_lng }})</span>
                    </label>
                @empty
                    <div>Tidak ada order pending dengan koordinat lengkap.</div>
                @endforelse
            </div>
        </div>

        <div>
            <button class="btn btn-primary" type="submit">Generate Route</button>
            <a class="btn" href="{{ route('route-plans.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
