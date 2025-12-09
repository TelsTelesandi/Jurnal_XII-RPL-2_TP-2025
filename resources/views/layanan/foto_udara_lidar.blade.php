
    <!-- Judul -->
    <h3 class="text-2xl font-bold mb-4 text-gray-800">Pemetaan Foto Udara & Lidar</h3>

    <!-- Galeri Gambar -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @php
            $images = [
                ['file' => 'image/DATA RAW LIDAR.png', 'title' => 'Data Raw LIDAR'],
                ['file' => 'image/DATA RAW LIDAR 2.png', 'title' => 'Data Raw LIDAR 2'],
                ['file' => 'image/Data Raw LIDAR 3.png', 'title' => 'Data Raw LIDAR 3'],
                [
                    'file' => 'image/Pengolahan dan Pengklasifikasian Point Clouds LIDAR.png',
                    'title' => 'Pengolahan dan Pengklasifikasian Point Clouds LIDAR',
                ],
                ['file' => 'image/Digital Surface Model (DSM).png', 'title' => 'Digital Surface Model (DSM)'],
                ['file' => 'image/Digital Terrain Model (DTM).png', 'title' => 'Digital Terrain Model (DTM)'],
                ['file' => 'image/LIDAR Intensity Images.png', 'title' => 'LIDAR Intensity Images'],
            ];
        @endphp

        @foreach ($images as $img)
            <div class="relative cursor-pointer open-modal" data-img="{{ asset($img['file']) }}"
                data-title="{{ $img['title'] }}">
                <img src="{{ asset($img['file']) }}" alt="{{ $img['title'] }}"
                    class="w-full h-72 object-cover rounded-lg shadow-md hover:scale-105 transition-transform duration-300">
                <div class="absolute bottom-0 left-0 w-full bg-black/50 text-white text-center py-2 rounded-b-lg">
                    {{ $img['title'] }}
                </div>
            </div>
        @endforeach
    </div>

<br><br>


<!-- Deskripsi -->
<p class="text-gray-700 mb-4 text-justify"> Foto udara adalah teknik pengambilan gambar permukaan bumi dari ketinggian
    tertentu yang digunakan untuk membuat peta rinci dan inventarisasi visual wilayah. Metode ini efektif untuk
    pemantauan penutup dan penggunaan lahan, termasuk pertanian, perkebunan, pertambangan, dan sektor minyak & gas.
    Dengan menggunakan wahana UAV (drone), data dapat diambil secara fleksibel dan bebas dari hambatan seperti tutupan
    awan, lalu diolah menjadi citra bergeoreferensi yang akurat. </p> <!-- Daftar layanan -->
<ul class="space-y-2 text-gray-700">
    <li>✔ Pengambilan & Pengolahan Data LIDAR</li>
    <li>✔ Pengambilan & Pengolahan Foto Udara Digital</li>
    <li>✔ Unmanned Aerial Vehicle (UAV)</li>
    <li>✔ 3D Visualization, Simulation & Analisis</li>
</ul>


    {{-- Bagian Atas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
        {{-- Kolom kiri --}}
        <div><br>
            <h4 class="font-bold mb-3">PENGAMBILAN & PENGOLAHAN DATA LIDAR</h4>
            <ul class="space-y-2 text-gray-700">
                <li>✔ Data Raw LIDAR</li>
                <li>✔ Pengolahan dan Pengklasifikasian Point Clouds LIDAR</li>
                <li>✔ Digital Surface Model (DSM)</li>
                <li>✔ Digital Terrain Model (DTM)</li>
                <li>✔ LIDAR Intensity Images</li>
            </ul>
        </div>
        {{-- Kolom kanan --}}
        <div class="flex justify-center">
            <img src="{{ asset('image/lidar.png') }}" alt="Ilustrasi LIDAR" class="rounded-lg shadow-md">
        </div>
    </div>

    {{-- Bagian Bawah --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
        {{-- Foto Udara --}}
        <div>
            <h4 class="font-bold mb-3">Foto Udara</h4>
            <div class="grid grid-cols-1 gap-4">
                <img src="{{ asset('image/Data-Lidar.png') }}" class="rounded-lg shadow-md">
       
            </div>
        </div>
        {{-- Pengolahan Foto Udara Digital --}}
        <div>
            <h4 class="font-bold mb-3">PENGAMBILAN & PENGOLAHAN FOTO UDARA DIGITAL</h4>
            <ul class="space-y-2 text-gray-700">
                <li>✔ Triangulasi Udara</li>
                <li>✔ Stereomodel</li>
                <li>✔ Mosaik Orthophoto</li>
            </ul>
        </div>
    </div>


@include('layanan.modal')


