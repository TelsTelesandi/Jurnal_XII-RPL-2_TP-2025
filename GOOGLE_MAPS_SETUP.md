# Google Maps Integration Setup

## 🗺️ Setup Google Maps API

Untuk menggunakan fitur Google Maps di halaman checkout, ikuti langkah berikut:

### 1. Dapatkan Google Maps API Key

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Enable APIs berikut:
   - **Maps JavaScript API**
   - **Places API** 
   - **Geocoding API**
4. Buat API Key di **Credentials** section
5. (Opsional) Restrict API key untuk keamanan:
   - HTTP referrers: `localhost:8000/*`, `127.0.0.1:8000/*`, `yourdomain.com/*`

### 2. Konfigurasi di Laravel

Tambahkan API key ke file `.env`:

```env
# Google Maps Configuration
GOOGLE_MAPS_API_KEY=AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_MAPS_DEFAULT_LAT=-6.2088
GOOGLE_MAPS_DEFAULT_LNG=106.8456
GOOGLE_MAPS_DEFAULT_ZOOM=15
```

**Catatan:** Ganti `AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx` dengan API key Anda yang sebenarnya.

### 3. Mode Fallback (Tanpa API Key)

Jika Anda belum memiliki API key, sistem akan otomatis menggunakan mode fallback:

✅ **GPS Detection**: Button "📍 GPS" tetap berfungsi untuk deteksi lokasi
✅ **Manual Input**: User bisa input alamat secara manual
✅ **Coordinate Storage**: Koordinat GPS tetap tersimpan di database
✅ **Form Validation**: Validasi alamat tetap berjalan normal

### 4. Fitur Lengkap (Dengan API Key)

✅ **Autocomplete Search**: Ketik alamat untuk mencari lokasi
✅ **Current Location**: Klik "📍 Lokasi Saya" untuk menggunakan GPS
✅ **Drag Marker**: Seret marker merah untuk memilih lokasi
✅ **Click Map**: Klik di peta untuk memilih lokasi
✅ **Auto Fill Address**: Alamat otomatis terisi dari Google Maps
✅ **Editable Address**: User bisa edit alamat manual jika diperlukan
✅ **Coordinate Storage**: Koordinat lat/lng tersimpan di database

### 5. Mengubah Default Location

Map akan menampilkan Jakarta sebagai lokasi default. Untuk mengubah, edit file `.env`:

```env
# Contoh untuk Surabaya
GOOGLE_MAPS_DEFAULT_LAT=-7.2575
GOOGLE_MAPS_DEFAULT_LNG=112.7521

# Contoh untuk Bandung  
GOOGLE_MAPS_DEFAULT_LAT=-6.9175
GOOGLE_MAPS_DEFAULT_LNG=107.6191

# Contoh untuk Medan
GOOGLE_MAPS_DEFAULT_LAT=3.5952
GOOGLE_MAPS_DEFAULT_LNG=98.6722
```

### 5. Testing

1. Buka halaman checkout: `/checkout`
2. Coba fitur-fitur:
   - Ketik alamat di search box
   - Klik "📍 Lokasi Saya"
   - Drag marker di peta
   - Klik di peta
3. Pastikan alamat terisi otomatis
4. Submit form dan cek database bahwa koordinat tersimpan

### 6. Troubleshooting

**Map tidak muncul?**
- Pastikan API key sudah benar
- Cek browser console untuk error
- Pastikan APIs sudah di-enable di Google Cloud

**Lokasi tidak akurat?**
- Pastikan GPS/location permission di-allow di browser
- Coba refresh halaman

**Search tidak jalan?**
- Pastikan Places API sudah di-enable
- Cek quota limit di Google Cloud Console

### 7. Production Notes

- Set API key restrictions untuk keamanan
- Monitor usage di Google Cloud Console
- Consider billing limits jika traffic tinggi
- Backup plan jika API down (fallback ke manual input)
