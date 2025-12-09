
    <!-- Judul -->
    <h3 class="text-2xl font-bold mb-4 text-gray-800">Pemetaan Tematik</h3>

<!-- Grid 2 gambar (30% + 70%) -->
<div class="grid grid-cols-1 md:grid-cols-10 gap-5 mb-5">
    <!-- Gambar 1 (30%) -->
    <div class="relative cursor-pointer md:col-span-3"
         onclick="openModal('{{ asset('image/Tematik2.png') }}', 'Pemetaan Tematik - 30%')">
        <img src="{{ asset('image/Tematik2.png') }}" alt="Pemetaan Tematik 1"
             class="w-full h-[300px] object-cover rounded-lg shadow-md">
        <div class="absolute bottom-0 left-0 w-full bg-black/50 text-white text-center py-1 rounded-b-lg">
            Tematik 1
        </div>
    </div>

    <!-- Gambar 2 (70%) -->
    <div class="relative cursor-pointer md:col-span-7"
         onclick="openModal('{{ asset('image/Tematik.png') }}', 'Pemetaan Tematik - 70%')">
        <img src="{{ asset('image/Tematik.png') }}" alt="Pemetaan Tematik 2"
             class="w-full h-[300px] object-cover rounded-lg shadow-md">
        <div class="absolute bottom-0 left-0 w-full bg-black/50 text-white text-center py-1 rounded-b-lg">
            Tematik 2
        </div>
    </div>
</div>
<br>

    <!-- Konten lain full lebar -->
    <p class="text-gray-700 mb-4 text-justify">
        Peta Tematik adalah peta yang menyajikan tema tertentu untuk kepentingan tertentu 
        seperti status lahan, penduduk, transportasi, dan lainnya. Peta ini menggunakan peta 
        rupabumi yang disederhanakan sebagai dasar untuk meletakkan informasi tematiknya, 
        dilengkapi simbol-simbol yang mewakili data spesifik.
    </p>

  <!-- Tambahkan CSS Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />



    <!-- Bagian atas: Judul & Carousel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <!-- Kolom Kiri: Judul & List -->
        <div>
    <br>
            <h4 class="font-semibold text-gray-700 uppercase tracking-wide">
               <b>Sasaran dari Kegiatan Pemetaan Penutup Lahan adalah:</b> 
            </h4>
            <ol class="list-decimal list-inside text-gray-700 mt-4 space-y-4 leading-relaxed">
                <li>
                    Informasi Geospasial Tematik Penutup Lahan skala 1:50.000 dalam format NLP dan seamless (Region, provinsi, kabupaten).
                </li>
                <li>
                    Pemetaan Tematik untuk berbagai bidang & GIS Analisis:
                    <ul class="list-disc list-inside ml-6 mt-2 space-y-1 text-gray-600">
                        <li>Manajemen Pesisir dan Kelautan</li>
                        <li>Kebencanaan</li>
                        <li>Manajemen Sumber Daya Alam</li>
                        <li>Lingkungan</li>
                        <li>Tata Ruang</li>
                        <li>Perencanaan Transportasi Umum</li>
                        <li>Karakteristik Ekosistem</li>
                        <li>Penggunaan Tanah & Lahan</li>
                        <li>Kehutanan</li>
                        <li>Pertanian</li>
                    </ul>
                </li>
                <li>Buku Deskripsi Analisis Pembaruan Peta Penutup Lahan</li>
                <li>Metadata Pembaruan Peta Penutup Lahan</li>
            </ol>
        </div>

            <!-- Kolom Kanan: Swiper -->
            <div>
                <div class="swiper mySwiper rounded-lg shadow-lg overflow-hidden">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="image/1.png" class="w-full h-80 object-cover" alt="">
                        </div>
                        <div class="swiper-slide">
                            <img src="image/2.png" class="w-full h-80 object-cover" alt="">
                        </div>
                        <div class="swiper-slide">
                            <img src="image/3.png" class="w-full h-80 object-cover" alt="">
                        </div>
                        <div class="swiper-slide">
                            <img src="image/10.png" class="w-full h-80 object-cover" alt="">
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
    <br><br>
        <!-- Gambar + Teks -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            <div>
                <img src="image/10.png" class="rounded-lg shadow-md w-full" alt="">
                <p class="mt-2 font-medium text-center text-gray-700">Data penutup lahan hasil integrasi dan sinkronisasi dengan data K/L</p>
            </div>
            <div>
                <img src="image/11.png" class="rounded-lg shadow-md w-full" alt="">
                <p class="mt-2 font-medium text-center text-gray-700">Peta Penutup Lahan hasil integrasi dengan Kementerian/Lembaga</p>
            </div>
        </div>

        <!-- Layout Peta -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <img src="image/14.png" class="rounded-lg shadow-md w-full" alt="">
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">Layout peta penutup lahan provinsi</h4>
                <ol class="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed">
                    <li>Hasil digitasi data penutup lahan dilakukan interpolasi 3D.</li>
                    <li>Data yang digunakan adalah DSM (Digital Surface Model) dan DTM (Digital Terrain Model).</li>
                    <li>Proses interpolasi menghasilkan data ketinggian sesuai vertek di data penutup lahan 2D.</li>
                    <li>Analisis 3D menggunakan extension 3D analyst dengan metode Interpolate shape.</li>
                    <li>Konversi data vektor 2D ke 3D menggunakan data DEMNAS dan DTM.</li>
                </ol>
            </div>
        </div>

        <!-- Hasil Citra -->
        <div class="text-center">
            <img src="image/15.png" class="rounded-lg shadow-md mx-auto" alt="">
            <p class="mt-2 font-medium text-gray-700">Hasil citra Landsat 8 hasil proses pansharpening (kanan) dan multispektral (kiri)</p>
        </div>

        <!-- Color Composite -->
        <div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <img src="image/16.png" class="rounded-lg shadow-md w-full h-64 object-cover" alt="">
                <img src="image/17.png" class="rounded-lg shadow-md w-full h-64 object-cover" alt="">
                <img src="image/18.png" class="rounded-lg shadow-md w-full h-64 object-cover" alt="">
            </div>
            <p class="mt-4 font-medium text-center text-gray-700">Contoh color composite yang digunakan dalam penutup lahan</p>
        </div>




    
