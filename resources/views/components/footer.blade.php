<section class="relative">
    <!-- Foto Kantor Section -->
    <div class="bg-[#476ba5] text-white py-12 w-full">
        <h3 class="text-3xl font-bold text-center mb-8 text-white">Kantor Kami</h3>

        <!-- Office Photo -->
        <div class="w-full mb-8">
            <img src="{{ asset('image/image copy.png') }}" 
                 alt="Kantor PT. Rekan Kinerja Abadi"
                 class="w-full h-64 md:h-96 object-cover shadow-xl border-0">

            <!-- Office Info -->
            <div class="bg-white/10 backdrop-blur-sm p-6 mt-0">
                <div class="flex items-start max-w-4xl mx-auto">
                    <i class="fa-solid fa-map-marker-alt text-red-400 text-xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="text-lg font-semibold mb-2">Alamat Kantor</h4>
                        <p class="text-gray-200">
                            Komplek Wijaya Grand Center Blok H No. 41 Jl. Wijaya II, PULO - Kebayoran Baru
                            Jakarta Selatan 12160
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Map Section -->
    <div class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-3xl font-bold text-center mb-8 text-gray-800">Lokasi di Peta</h3>

            <!-- Map Container -->
            <div class="relative bg-white rounded-lg shadow-xl overflow-hidden">
                <!-- Map Controls -->
                <div class="absolute top-4 right-4 z-20 flex flex-col space-y-2 ">
                    <button id="fullscreen-btn"
                        class="bg-white hover:bg-gray-100 text-gray-800 p-3 rounded-lg shadow-lg transition-all transform hover:scale-105">
                        <i class="fas fa-expand text-lg"></i>
                    </button>
                    <button id="my-location-btn"
                        class="bg-white hover:bg-gray-100 text-gray-800 p-3 rounded-lg shadow-lg transition-all transform hover:scale-105">
                        <i class="fas fa-crosshairs text-lg"></i>
                    </button>
                </div>

                <!-- Map -->
                <div id="map" class="w-full h-[500px] z-10"></div>

                <!-- Map Info -->
                <div class="bg-[#476ba5]/10 p-4 border-t border-[#0f9cdc]">
                    <div class="flex flex-wrap justify-between items-center text-sm text-gray-700">
                        <div class="flex items-center space-x-4">
                            <span><i class="fas fa-mouse-pointer mr-1"></i> Klik & drag untuk geser peta</span>
                            <span><i class="fas fa-search-plus mr-1"></i> Scroll untuk zoom</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="https://maps.google.com/maps?q=-6.2531257,106.7998016" target="_blank"
                                class="bg-[#476ba5] hover:bg-[#0f9cdc] text-white px-4 py-2 rounded-lg transition">
                                <i class="fab fa-google mr-2"></i>Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Content -->
    <div class="bg-[#476ba5] text-white">
        <div class="relative max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Logo & Deskripsi -->
            <div>
                <img src="{{ asset('image/image.png') }}" alt="Logo RKA" class="h-16 mb-4 drop-shadow-lg">
                <h3 class="text-lg font-bold mb-2">PT. Rekan Kinerja Abadi</h3>
                <p class="text-sm leading-relaxed text-gray-200 text-justify">
                    Menawarkan penginderaan jauh, GIS, basis data spasial, pelatihan geomatika,
                    penelitian GIS, survei, dan pemetaan digital. 80% praktik dan 20% teori.
                </p>
            </div>

            <!-- Social Media -->
            <div>
                <h3 class="text-lg font-bold mb-4">Social Media</h3>
                <div class="flex space-x-4 mb-4 text-2xl">
                    <a href="#" class="hover:scale-110 hover:text-[#0f9cdc] transition">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="hover:scale-110 hover:text-pink-500 transition">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="hover:scale-110 hover:text-red-500 transition">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Hubungi Kami -->
            <div>
                <h3 class="text-lg font-bold mb-4">Hubungi Kami</h3>

                <!-- Alamat -->
                <div class="flex items-start mb-4">
                    <i class="fa-solid fa-map-marker-alt text-red-500 text-lg mr-3 mt-1"></i>
                    <div>
                        <p class="text-sm text-gray-200">
                            Komplek Wijaya Grand Center Blok H No. 41<br>
                            Jl. Wijaya II, PULO - Kebayoran Baru<br>
                            Jakarta Selatan
                        </p>
                    </div>
                </div>

                <!-- Telepon -->
                <a href="tel:0217258071"
                    class="flex items-center bg-white text-gray-800 rounded px-3 py-2 shadow-lg mb-3 hover:bg-gray-100 transition">
                    <i class="fa-solid fa-phone text-green-500 text-lg mr-4"></i>
                    Telp. 021 7258071
                </a>

                <!-- Fax -->
                <a href="tel:0217258958"
                    class="flex items-center bg-white text-gray-800 rounded px-3 py-2 shadow-lg hover:bg-gray-100 transition">
                    <i class="fa-solid fa-fax text-blue-500 text-lg mr-4"></i>
                    Fax. 021 7258958
                </a>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="relative bg-[#476ba5] text-white text-center py-3 border-t border-[#0f9cdc]">
            &copy; 2025 | PT. Rekan Kinerja Abadi
        </div>
    </div>
</section>
