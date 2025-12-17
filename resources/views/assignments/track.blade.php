@extends('layouts.app')

@section('content')
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    📍 Live Tracking Assignment #{{ $assignment->id }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Route Plan: <span class="font-medium">{{ $assignment->routePlan?->code }}</span> — 
                    Status: <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($assignment->status) }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="px-6 py-6">
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                🗺️ Peta Tracking Real-Time
            </h3>
            <div id="map" class="w-full h-96 rounded-lg border border-gray-300 shadow-sm"></div>
        </div>

        <!-- Location Form -->
        <div class="bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                📡 Update Lokasi Driver
            </h3>
            <form method="post" action="{{ route('assignments.track', $assignment) }}" class="space-y-6">
                @csrf
                
                <!-- Primary Location Data -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                        <input type="number" step="0.0000001" name="lat" required 
                               value="{{ old('lat', $lastEvent->lat ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="-6.2088">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                        <input type="number" step="0.0000001" name="lng" required 
                               value="{{ old('lng', $lastEvent->lng ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="106.8456">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Kejadian</label>
                        <input type="datetime-local" name="occurred_at" 
                               value="{{ old('occurred_at') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Additional Tracking Data -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kecepatan (km/h)</label>
                        <input type="number" step="0.01" name="speed_kmh" 
                               value="{{ old('speed_kmh') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Arah (derajat)</label>
                        <input type="number" step="0.01" name="heading_deg" 
                               value="{{ old('heading_deg') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Akurasi GPS (meter)</label>
                        <input type="number" step="0.01" name="accuracy_m" 
                               value="{{ old('accuracy_m') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Kirim Lokasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Status Indicator -->
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        Status: Tracking aktif dengan update real-time
                    </p>
                    <p class="text-sm text-green-600">
                        Peta akan diperbarui secara otomatis saat ada perubahan lokasi
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function(){
            const stops = @json($assignment->routePlan?->routeStops->sortBy('seq')->map(fn($s)=>[
                'seq' => $s->seq,
                'lat' => (float)$s->lat,
                'lng' => (float)$s->lng,
                'code' => $s->order?->code,
            ]) ?? []);
            const lastEvent = @json($lastEvent ? [
                'lat' => (float)$lastEvent->lat,
                'lng' => (float)$lastEvent->lng,
                'occurred_at' => optional($lastEvent->occurred_at)->format('Y-m-d H:i:s'),
            ] : null);

            const centerLat = lastEvent ? lastEvent.lat : (stops[0]?.lat ?? -6.1754);
            const centerLng = lastEvent ? lastEvent.lng : (stops[0]?.lng ?? 106.8272);

            let map, liveMarker;

            function initMap() {
                if (typeof google === 'undefined' || !google.maps) return;
                map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: centerLat, lng: centerLng },
                    zoom: 12,
                });
                // Draw route polyline and stop markers
                if (stops.length > 0) {
                    const path = stops.map(s => ({ lat: s.lat, lng: s.lng }));
                    new google.maps.Polyline({ path, strokeColor: '#0ea5e9', strokeWeight: 3, map });
                    stops.forEach(s => {
                        new google.maps.Marker({ position: { lat: s.lat, lng: s.lng }, map, label: String(s.seq) });
                    });
                }
                if (lastEvent) setLiveMarker(lastEvent.lat, lastEvent.lng, `Posisi Terakhir\n${lastEvent.occurred_at ?? ''}`);
            }

            function setLiveMarker(lat, lng, text) {
                if (!map) return;
                if (!liveMarker) {
                    liveMarker = new google.maps.Marker({ position: { lat, lng }, map, label: 'D' });
                } else {
                    liveMarker.setPosition({ lat, lng });
                }
                if (text) {
                    const infowindow = new google.maps.InfoWindow({ content: `<div style="font-size:12px">${text.replace(/\n/g,'<br>')}</div>` });
                    infowindow.open({ anchor: liveMarker, map, shouldFocus: false });
                }
            }

            // Live updates via SSE with fallback polling
            const streamUrl = `{{ route('assignments.track.stream', $assignment) }}`;
            const latestUrl = `{{ route('assignments.track.latest', $assignment) }}`;

            function startPolling() {
                setInterval(async () => {
                    try {
                        const r = await fetch(latestUrl, {headers: {'Accept':'application/json'}});
                        if (!r.ok) return;
                        const data = await r.json();
                        if (data && typeof data.lat === 'number' && typeof data.lng === 'number') {
                            setLiveMarker(data.lat, data.lng, `Posisi Terakhir\n${data.occurred_at ?? ''}`);
                        }
                    } catch (e) {}
                }, 5000);
            }

            function startStream() {
                if (!window.EventSource) { startPolling(); return; }
                try {
                    const es = new EventSource(streamUrl);
                    es.onmessage = (ev) => {
                        try {
                            const data = JSON.parse(ev.data);
                            if (data && typeof data.lat === 'number' && typeof data.lng === 'number') {
                                setLiveMarker(data.lat, data.lng, `Posisi Terakhir\n${data.occurred_at ?? ''}`);
                            }
                        } catch(_){ }
                    };
                    es.onerror = () => { es.close(); startPolling(); };
                } catch(_e) { startPolling(); }
            }

            document.addEventListener('DOMContentLoaded', function(){
                // Initialize map after script loaded
                const tryInit = setInterval(() => {
                    if (typeof google !== 'undefined' && google.maps) {
                        clearInterval(tryInit);
                        initMap();
                        startStream();
                    }
                }, 200);
            });
        })();
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&language=id&region=ID" async defer></script>
@endpush
