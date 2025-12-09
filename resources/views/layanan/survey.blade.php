<!-- Judul Halaman -->
<h3 class="text-2xl font-bold mb-4 text-gray-800">
    Survey – Hydrographi dan Terestrial dengan GPS dan 3D Mobile System
</h3>

<div class="space-y-10 text-gray-700">

    <!-- a. Survey Karakteristik Perairan -->
    <div>
        <h4 class="font-bold mb-3">a. Survey Karakteristik Perairan, Danau, dan Sungai</h4>
        <img src="{{ asset('image/Survey.png') }}" 
             alt="Survey Karakteristik Perairan" 
             class="w-full object-contain rounded-lg shadow-md mb-3">
        <p class="text-justify">
            Survey ini dilakukan untuk mengetahui kondisi fisik dan karakteristik badan air, seperti kedalaman, arus, dan kualitas air di danau, sungai, maupun wilayah perairan lainnya. Peralatan GPS, sensor kedalaman, dan instrumen pengukuran air digunakan di atas kapal atau perahu untuk memastikan data yang akurat.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Pengukuran kedalaman dan profil dasar perairan</li>
            <li>Pemantauan kualitas air</li>
            <li>Pemetaan wilayah perairan</li>
        </ul>
    </div>

    <!-- b. Survey Topografi -->
    <div>
        <h4 class="font-bold mb-3">b. Survey Topografi</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
            <img src="{{ asset('image/Survey2.png') }}" 
                 alt="Survey Topografi 1" 
                 class="w-full object-contain rounded-lg shadow-md">
            <img src="{{ asset('image/Survey3.png') }}" 
                 alt="Survey Topografi 2" 
                 class="w-full object-contain rounded-lg shadow-md">
        </div>
        <p class="text-justify">
            Survey topografi bertujuan memetakan bentuk permukaan tanah dan menentukan elevasi titik-titik di suatu area. Menggunakan total station atau GNSS, survey ini menghasilkan data kontur dan ketinggian yang digunakan dalam perencanaan konstruksi, pertanian, dan tata ruang.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Pengukuran titik-titik kontrol tanah</li>
            <li>Pembuatan peta kontur</li>
            <li>Analisis kemiringan dan elevasi</li>
        </ul>
    </div>

    <!-- c. Survey Foto Udara dan LiDAR -->
    <div>
        <h4 class="font-bold mb-3">c. Survey Foto Udara dan LiDAR</h4>
        <img src="{{ asset('image/Survey4.png') }}" 
             alt="Survey Foto Udara dan LiDAR" 
             class="w-full object-contain rounded-lg shadow-md mb-3">
        <p class="text-justify">
            Metode ini memanfaatkan wahana udara (seperti drone atau pesawat kecil) yang dilengkapi kamera dan sensor LiDAR untuk menghasilkan data spasial resolusi tinggi. LiDAR mampu memetakan permukaan tanah bahkan di bawah tutupan vegetasi.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Pengambilan citra udara resolusi tinggi</li>
            <li>Pemetaan 3D dengan LiDAR</li>
            <li>Integrasi data dengan GIS</li>
        </ul>
    </div>

    <!-- d. Survey Toponimi Wilayah -->
    <div>
        <h4 class="font-bold mb-3">d. Survey Toponimi Wilayah</h4>
        <img src="{{ asset('image/Survey5.png') }}" 
             alt="Survey Toponimi Wilayah" 
             class="w-full object-contain rounded-lg shadow-md mb-3">
        <p class="text-justify">
            Survey ini mengidentifikasi dan mendokumentasikan nama-nama geografis di lapangan, seperti nama desa, sekolah, atau lokasi penting lainnya. Dokumentasi foto dan koordinat GPS digunakan untuk pembaruan peta dan database geospasial resmi.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Dokumentasi nama lokasi dan fitur geografis</li>
            <li>Pengambilan foto dan koordinat GPS</li>
            <li>Integrasi ke peta resmi dan SIG</li>
        </ul>
    </div>

    <!-- e. Survey Jalan dan Navigasi -->
    <div>
        <h4 class="font-bold mb-3">e. Survey Jalan dan Navigasi</h4>
        <img src="{{ asset('image/Survey6.png') }}" 
             alt="Survey Jalan dan Navigasi" 
             class="w-full object-contain rounded-lg shadow-md mb-3">
        <p class="text-justify">
            Survey ini menggunakan kendaraan yang dilengkapi kamera, GPS, dan perangkat pemetaan untuk merekam kondisi jalan, rambu lalu lintas, serta rute navigasi.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Perekaman kondisi jalan secara real-time</li>
            <li>Pemetaan rute dan simpang jalan</li>
            <li>Integrasi dengan sistem navigasi digital</li>
        </ul>
    </div>

</div>

@include('layanan.modal')
