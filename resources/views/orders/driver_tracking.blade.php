@extends('layouts.app')

@section('content')
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📡 Bagikan Lokasi Pengantaran</h1>
            <p class="text-sm text-gray-600">Order {{ $order->code }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-sm">Detail Order</a>
        </div>
    </div>
    <div class="p-6 space-y-4">
        <div class="flex items-center gap-3 flex-wrap">
            <button id="btn-start" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm">Mulai Berbagi Lokasi</button>
            <button id="btn-stop" class="px-5 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md text-sm" disabled>Stop</button>
            <div id="status" class="text-sm text-gray-600"></div>
            @php
                $originLat = config('googlemaps.company_location.lat', config('googlemaps.default_location.lat'));
                $originLng = config('googlemaps.company_location.lng', config('googlemaps.default_location.lng'));
                $destLat = $order->destination_lat ?? $order->shipping_lat;
                $destLng = $order->destination_lng ?? $order->shipping_lng;
            @endphp
            @if($destLat && $destLng)
                <a href="https://www.google.com/maps/dir/?api=1&origin={{ $originLat }},{{ $originLng }}&destination={{ $destLat }},{{ $destLng }}&travelmode=driving" target="_blank" rel="noopener" class="px-5 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-md text-sm">
                    🗺️ Buka Arah Google Maps
                </a>
            @endif
        </div>
        <div id="map" class="w-full h-80 rounded-lg border"></div>
        <div class="text-sm text-gray-600">Terakhir: <span id="last-time">-</span> | Lat: <span id="lat">-</span> | Lng: <span id="lng">-</span></div>
    </div>
</div>

 
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const postUrl = '{{ route('orders.track', $order) }}';
let map, marker, watchId = null, customerMarker = null;
let lastSentAt = 0; // ms
let lastLat = null, lastLng = null;
const MIN_INTERVAL_MS = 5000; // kirim max 1x per 5 detik
const MIN_MOVE_M = 15; // kirim jika bergerak >= 15 meter

const DEFAULT_CENTER = { lat: {{ config('googlemaps.default_location.lat', -6.2088) }}, lng: {{ config('googlemaps.default_location.lng', 106.8456) }} };
const DEST_LAT = {!! $destLat ? $destLat : 'null' !!};
const DEST_LNG = {!! $destLng ? $destLng : 'null' !!};

function initMap(lat = DEFAULT_CENTER.lat, lng = DEFAULT_CENTER.lng) {
    if (typeof google === 'undefined' || !google.maps) return;
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat, lng },
        zoom: 14,
    });
    marker = new google.maps.Marker({
        position: { lat, lng },
        map,
        label: 'D'
    });
    if (DEST_LAT !== null && DEST_LNG !== null) {
        customerMarker = new google.maps.Marker({
            position: { lat: DEST_LAT, lng: DEST_LNG },
            map,
            label: 'C'
        });
        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat, lng });
        bounds.extend({ lat: DEST_LAT, lng: DEST_LNG });
        map.fitBounds(bounds);
    }
}

function updateUI(pos) {
    document.getElementById('lat').textContent = pos.coords.latitude.toFixed(6);
    document.getElementById('lng').textContent = pos.coords.longitude.toFixed(6);
    document.getElementById('last-time').textContent = new Date().toLocaleString();
}

function sendPosition(pos) {
    fetch(postUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
            accuracy_m: pos.coords.accuracy,
            speed_kmh: pos.coords.speed ? (pos.coords.speed * 3.6) : null,
            heading_deg: pos.coords.heading ?? null,
            type: 'gps'
        })
    }).catch(() => {});
}

function onPosition(pos) {
    updateUI(pos);
    const { latitude, longitude } = pos.coords;
    if (!map) initMap(latitude, longitude);
    if (marker && typeof marker.setPosition === 'function') {
        marker.setPosition({ lat: latitude, lng: longitude });
    }
    // Hindari recenter tiap update agar tidak lag; user bisa geser manual jika perlu

    // Throttle: kirim hanya jika cukup waktu berlalu dan pergerakan signifikan
    const now = Date.now();
    const movedEnough = (() => {
        if (lastLat === null || lastLng === null) return true;
        const R = 6371000; // m
        const toRad = (d) => d * Math.PI / 180;
        const dLat = toRad(latitude - lastLat);
        const dLng = toRad(longitude - lastLng);
        const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lastLat)) * Math.cos(toRad(latitude)) * Math.sin(dLng/2)**2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const dist = R * c;
        return dist >= MIN_MOVE_M;
    })();

    if (now - lastSentAt >= MIN_INTERVAL_MS && movedEnough) {
        lastSentAt = now;
        lastLat = latitude; lastLng = longitude;
        sendPosition(pos);
    }
}

function onError(err) {
    document.getElementById('status').textContent = 'Gagal mengambil lokasi: ' + err.message;
}

document.getElementById('btn-start').addEventListener('click', () => {
    if (!navigator.geolocation) {
        document.getElementById('status').textContent = 'Geolocation tidak didukung browser.';
        return;
    }
    document.getElementById('status').textContent = 'Berbagi lokasi aktif...';
    document.getElementById('btn-start').disabled = true;
    document.getElementById('btn-stop').disabled = false;
    watchId = navigator.geolocation.watchPosition(onPosition, onError, { enableHighAccuracy: true, maximumAge: 10000, timeout: 20000 });
});

document.getElementById('btn-stop').addEventListener('click', () => {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    document.getElementById('btn-start').disabled = false;
    document.getElementById('btn-stop').disabled = true;
    document.getElementById('status').textContent = 'Berbagi lokasi dihentikan.';
});

// Auto-start on page load to immediately share location after accept
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-start').click();
});

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&language=id&region=ID" async defer></script>
@endsection
