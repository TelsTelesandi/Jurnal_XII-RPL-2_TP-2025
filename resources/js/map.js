import L from "leaflet";
import "leaflet/dist/leaflet.css";
// pastikan leaflet-providers dimuat juga



document.addEventListener("DOMContentLoaded", function () {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    console.log("🗺️ Loading map for Wijaya Grand Center...");

    const lat = -6.2531257;
    const lng = 106.7998016;

    // Init map
    const map = L.map("map", {
        center: [lat, lng],
        zoom: 17,
        zoomControl: false,
    });

    // Tile layers
    const streetLayer = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
        maxZoom: 20,
    }).addTo(map);

    const satelliteLayer = L.tileLayer(
        "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        { attribution: "© Esri & Contributors", maxZoom: 20 }
    );

    // Marker kantor
    const redIcon = new L.Icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png",
        shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });

    const marker = L.marker([lat, lng], { icon: redIcon })
        .addTo(map)
        .bindPopup("<b>Wijaya Grand Center</b><br>Jl. Wijaya II, Jakarta Selatan");

    // ✅ BUTTON CONTROLS (semua di dalam DOMContentLoaded)

    // Fullscreen Button
    document.getElementById("fullscreen-btn")?.addEventListener("click", function () {
        if (!document.fullscreenElement) {
            mapElement.requestFullscreen?.();
            this.innerHTML = '<i class="fas fa-compress text-lg"></i>';
        } else {
            document.exitFullscreen?.();
            this.innerHTML = '<i class="fas fa-expand text-lg"></i>';
        }
        setTimeout(() => map.invalidateSize(), 200);
    });

    // Toggle Satellite
    document.getElementById("satellite-toggle")?.addEventListener("click", function () {
        if (map.hasLayer(streetLayer)) {
            map.removeLayer(streetLayer);
            map.addLayer(satelliteLayer);
            this.innerHTML = '<i class="fas fa-road text-lg"></i>';
        } else {
            map.removeLayer(satelliteLayer);
            map.addLayer(streetLayer);
            this.innerHTML = '<i class="fas fa-satellite text-lg"></i>';
        }
    });

    // 🔹 Tombol Lokasi Kantor
    document.getElementById("my-location-btn")?.addEventListener("click", function () {
        console.log("🏢 Tombol lokasi kantor diklik!");

        map.flyTo([lat, lng], 18, { animate: true, duration: 1.5 });
        marker.openPopup();

        const circle = L.circle([lat, lng], {
            color: "#0f9cdc",
            fillColor: "#476ba5",
            fillOpacity: 0.3,
            radius: 80,
        }).addTo(map);

        setTimeout(() => map.removeLayer(circle), 2000);
    });
});
const baseMaps = {
  "🗺️ Street": streetLayer,
  "🛰️ Satellite": satelliteLayer,
  "🗻 Topo": L.tileLayer("https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png")
};

const overlays = {
  "📍 Kantor": marker,
  "🗺️ Batas Provinsi": L.geoJSON(provinsiData, { style: { color: "blue" } }),
  "👥 Jumlah Penduduk": L.geoJSON(populationData, {
      style: f => ({
        fillColor: f.properties.jumlah > 1000000 ? "red" : "green",
        color: "white",
        weight: 1
      })
  })
};

L.control.layers(baseMaps, overlays, { collapsed: false }).addTo(map);
document.addEventListener("DOMContentLoaded", function () {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    const lat = -6.2531257;
    const lng = 106.7998016;

    // Init map
    const map = L.map("map", {
        center: [lat, lng],
        zoom: 13,
    });

    // === Base Maps (pakai Leaflet Providers) ===
    const osm = L.tileLayer.provider("OpenStreetMap.Mapnik").addTo(map); // default
    const satellite = L.tileLayer.provider("Esri.WorldImagery");
    const topo = L.tileLayer.provider("OpenTopoMap");
    const dark = L.tileLayer.provider("CartoDB.DarkMatter");
    const light = L.tileLayer.provider("CartoDB.Positron");

    // Marker kantor
    const redIcon = new L.Icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png",
        shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });

    const marker = L.marker([lat, lng], { icon: redIcon })
        .addTo(map)
        .bindPopup("<b>Wijaya Grand Center</b><br>Jl. Wijaya II, Jakarta Selatan");

    // Base map options
    const baseMaps = {
        "🗺️ Street (OSM)": osm,
        "🛰️ Satellite (Esri)": satellite,
        "⛰️ Topo": topo,
        "🌙 Dark Mode": dark,
        "☀️ Light Mode": light,
    };

    // Overlays (contoh)
    const overlays = {
        "📍 Kantor": marker,
        "🗺️ Batas Provinsi": L.geoJSON(window.provinsiData || {}, { style: { color: "blue" } }),
        "👥 Jumlah Penduduk": L.geoJSON(window.populationData || {}, {
            style: f => ({
                fillColor: f.properties.jumlah > 1000000 ? "red" : "green",
                color: "white",
                weight: 1
            }),
            onEachFeature: (f, layer) => {
                layer.bindPopup(`${f.properties.nama}<br>Jumlah: ${f.properties.jumlah}`);
            }
        })
    };

    // Control Layer
    L.control.layers(baseMaps, overlays, { collapsed: false }).addTo(map);

    // Scale
    L.control.scale().addTo(map);
});