@extends('layout.app')

@section('content')
    <!-- Beranda -->
    <section id="beranda" class="relative h-screen flex items-center justify-center">
        <!-- Gambar Latar -->
        <img src="{{ asset('image/image copy.png') }}" alt="Peta Indonesia"
            class="absolute inset-0 w-full h-full object-cover">
        <!-- Overlay Gelap -->
        <div class="absolute inset-0 bg-black/70"></div>
        <!-- Teks -->
        <div class="relative z-10 text-center text-white px-4">
            <h1 class="text-4xl md:text-5xl font-extrabold">PT. Rekan Kinerja Abadi</h1>
            
        </div>
    </section>

    <!-- Tentang Kami -->
     <section id="tentang-kami">
                    <!-- Header Gambar -->
                    <div class="relative h-[50vh] flex flex-col items-center justify-center overflow-hidden">
                        <!-- Gambar -->
                        <img src="{{ asset('image/image copy.png') }}" alt="Tentang Kami"
                            class="absolute inset-0 w-full h-full object-cover">

                        <!-- Overlay blur putih -->
                        <div class="absolute inset-0 bg-white/50 backdrop-blur-sm"></div>

                        <!-- Judul -->
                        <h2 class="relative z-10 text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-white drop-shadow-lg">
        TENTANG KAMI
    </h2>

                        <!-- Subjudul -->
                        <p
                            class="relative z-10 mt-4 text-2xl font-bold text-blue-900 bg-white/60 px-6 py-2 rounded-lg backdrop-blur-md shadow-md">
                            PT. Rekan Kinerja Abadi
                        </p>

                        <!-- Gradasi transisi ke konten -->
                        <div class="absolute bottom-0 w-full h-24 bg-gradient-to-t from-gray-100 to-transparent"></div>
                    </div>

                    <!-- Isi Konten -->
                    <div class="max-w-5xl mx-auto px-6 py-12 bg-gray-100">
                        <p class="text-lg leading-relaxed text-gray-700 text-justify mb-6">
                            Perusahaan Rekan Kinerja Abadi sendiri menawarkan dasar dan Penerapan Penginderaan Jauh, GIS dan basis
                            data
                            spasial
                            untuk pengkajian sumber daya alam, studi lingkungan, seperti Pengelolaan Pesisir dan Laut, Inventarisasi
                            dan
                            Pengelolaan Hutan,
                            Pemetaan Pertanian dan Vegetasi Presisi, Perencanaan Kota, Geologi dan Geomorfologi, Eksplorasi Mineral,
                            Minyak dan Gas,
                            dan pelatihan geomatika lainnya.
                        </p>
                        <p class="text-lg leading-relaxed text-gray-700 text-justify mb-6">
                            Selain pelatihan, juga dilakukan penelitian penerapan GIS, Penginderaan Jauh, Survei, dan Pemetaan
                            Digital.
                            Perusahaan Rekan Kinerja Abadi berhubungan tenaga ahli professional dengan keahlian dan pengalaman
                            khusus sesuai kebutuhan pengguna jasa.
                        </p>
                        <p class="text-lg leading-relaxed text-gray-700 text-justify">
                            Kami memberikan pengalaman langsung untuk pengumpulan data, analisis GIS, Penginderaan Jauh, dan
                            pemetaan
                            digital. Pelatihan terdiri dari 80% praktik dan 20% teori.
                        </p>
                    </div>

        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-4">STRUKTUR ORGANISASI</h2>
            <div class="w-16 h-1 bg-blue-500 mx-auto mb-8"></div>
            <img src="{{ asset('image/image copy 3.png') }}" alt="Struktur Organisasi"
                class="mx-auto max-w-full rounded-lg shadow-lg">
        </div>
    </section>

    <!-- Layanan -->
    @php
        // default kalau tidak dikirim dari route
        $selected = $selected ?? 'foto_udara_lidar';
    @endphp


    <!-- Layanan -->
    <section id="layanan" class="py-20 bg-gray-100 relative">
        <div class="max-w-6xl mx-auto px-6">

            {{-- Header with Dropdown on the Right --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div class="md:flex-1">
                    <h2 class="text-3xl font-bold text-center md:text-left mb-2">Layanan Kami</h2>
                    <div class="w-20 h-1 bg-blue-500 rounded mx-auto md:mx-0"></div>
                </div>

                {{-- Dropdown positioned on the right --}}
                <div class="relative mt-4 md:mt-0">
                    <button type="button" id="layanan-dropdown-btn"
                        class="bg-white border-2 border-blue-500 rounded-lg px-6 py-3 text-left flex items-center justify-between hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer w-full md:w-80 shadow-md">
                        <span id="selected-service" class="font-medium text-gray-700">Foto Udara & Lidar</span>
                        <svg class="w-5 h-5 text-blue-500 transition-transform duration-200 ml-3 flex-shrink-0"
                            id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div id="layanan-dropdown-menu"
                        class="absolute top-full right-0 bg-white border border-gray-200 rounded-lg shadow-2xl z-[9999] hidden mt-2 w-80 max-h-64 overflow-y-auto">
                        <ul class="py-1">
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="foto_udara_lidar" data-label="Foto Udara & Lidar">
                                    Foto Udara & Lidar
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="tematik" data-label="Tematik">
                                    Tematik
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="software_development" data-label="Software Development">
                                    Software Development
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="survey" data-label="Survey">
                                    Survey
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="training" data-label="Training">
                                    Training
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                    class="dropdown-option w-full text-left px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer border-0 bg-transparent focus:bg-blue-100"
                                    data-key="data_software_provider" data-label="Data & Software Provider">
                                    Data & Software Provider
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Content Panels --}}
            <div id="layanan-content" class="bg-white p-6 rounded-xl shadow-lg relative min-h-[400px]">
                <div id="layanan-foto_udara_lidar" class="content-panel active">
                    @includeIf('layanan.foto_udara_lidar')
                </div>
                <div id="layanan-tematik" class="content-panel hidden">
                    @includeIf('layanan.tematik')
                </div>
                <div id="layanan-software_development" class="content-panel hidden">
                    @includeIf('layanan.software_development')
                </div>
                <div id="layanan-survey" class="content-panel hidden">
                    @includeIf('layanan.survey')
                </div>
                <div id="layanan-training" class="content-panel hidden">
                    @includeIf('layanan.training')
                </div>
                <div id="layanan-data_software_provider" class="content-panel hidden">
                    @includeIf('layanan.data_software_provider')
                </div>
            </div>
        </div>
    </section>

    <!-- Mitra Kerja -->
    <section id="mitra-kerja" class="py-16 bg-white">
        @include('sections.mitra_kerja')
    </section>
 @isset($posts)
       <!-- Blog -->
<section id="blog" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="flex items-center mb-8 gap-4">
            <span class="bg-[#0f9cdc] text-white px-5 py-2 text-base md:text-lg font-bold rounded-lg shadow flex-shrink-0">
                Blog
            </span>

            <!-- Running Text -->
            <div class="flex-1 h-10 flex items-center overflow-hidden rounded-lg bg-[#476ba5] px-4 relative">
                <div class="whitespace-nowrap animate-marquee text-white text-sm md:text-base">
                    🌐 PT. Rekan Kinerja Abadi – Pelatihan GIS & Remote Sensing 80% Praktik, 20% Teori •
                    ✨ Inovasi teknologi untuk pembangunan berkelanjutan •
                    📡 Workshop Remote Sensing untuk profesional •
                    🤝 Kerja sama strategis dengan mitra pendidikan & teknologi
                    &nbsp;&nbsp;&nbsp;
                    🌐 PT. Rekan Kinerja Abadi – Pelatihan GIS & Remote Sensing 80% Praktik, 20% Teori •
                    ✨ Inovasi teknologi untuk pembangunan berkelanjutan •
                    📡 Workshop Remote Sensing untuk profesional •
                    🤝 Kerja sama strategis dengan mitra pendidikan & teknologi
                </div>
            </div>
        </div>
<!-- Grid Artikel -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @if ($posts->count() > 0)
        @php $main = $posts->first(); @endphp

        <!-- Artikel Utama -->
        <div class="md:col-span-2 order-1">
            <a href="{{ route('blog.show', $main->slug) }}"
               class="block rounded-xl shadow-lg overflow-hidden relative group">
                <img src="{{ $main->thumbnail ? asset('storage/' . $main->thumbnail) : asset('images/default.jpg') }}"
                     alt="{{ $main->judul }}" class="w-full h-[250px] md:h-[400px] object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <p class="text-sm mb-2">{{ $main->created_at->diffForHumans() }}</p>
                    <h2 class="text-xl md:text-2xl font-bold mb-2 group-hover:text-[#0f9cdc] transition">
                        {{ $main->judul }}
                    </h2>
                    <p class="text-white/90 line-clamp-2">
                        {{ $main->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($main->isi), 120) }}
                    </p>
                </div>
            </a>
        </div>

        <!-- Artikel Samping -->
        <div class="flex flex-col gap-4 order-2">
            @foreach ($posts->skip(1)->take(2) as $post)
                <a href="{{ route('blog.show', $post->slug) }}"
                   class="block rounded-xl shadow overflow-hidden relative group">
                    <img src="{{ $post->thumbnail ? asset('storage/' . $post->thumbnail) : asset('images/default.jpg') }}"
                         alt="{{ $post->judul }}" class="w-full h-[180px] md:h-[190px] object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                        <p class="text-xs mb-1">{{ $post->created_at->diffForHumans() }}</p>
                        <h3 class="text-base md:text-lg font-bold group-hover:text-[#0f9cdc] transition">
                            {{ $post->judul }}
                        </h3>
                        <p class="text-sm text-white/90 line-clamp-2">
                            {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->isi), 80) }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>


        <!-- Button -->
        <div class="text-center mt-10">
            <a href="{{ route('blog.list') }}"
               class="bg-[#0f9cdc] text-white px-6 py-2.5 font-bold rounded-3xl shadow-lg hover:bg-[#476ba5] transition">
                Tampilkan blog lainnya
            </a>
        </div>
    </div>
</section>

    @endisset
    <!-- Footer -->
    <section id="hubungi-kami" class="bg-gray-100">
        @include('components.footer')
    </section>
@endsection
@if (request()->is('/'))
<style>
    @keyframes marquee {
  0%   { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

.animate-marquee {
  display: inline-block;
  padding-left: 100%; /* supaya start dari kanan penuh */
  animation: marquee 45s linear infinite;
  white-space: nowrap;
}


  .content-panel {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s ease-in-out;
    display: none;
  }

  .content-panel.active {
    opacity: 1;
    transform: translateY(0);
    display: block;
  }

  .dropdown-option.active {
    background-color: #3b82f6 !important;
    color: white !important;
  }

  .dropdown-option.active:hover {
    background-color: #2563eb !important;
    color: white !important;
  }
</style>

@if (request()->is('/'))
<style>
  .content-panel {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s ease-in-out;
    display: none;
  }
  .content-panel.active {
    opacity: 1;
    transform: translateY(0);
    display: block;
  }
  .dropdown-option.active {
    background-color: #3b82f6 !important;
    color: white !important;
  }
</style>

<script>
    
document.addEventListener("DOMContentLoaded", function() {
  // ✅ Guard: script hanya jalan di homepage
  if (window.location.pathname !== "/") {
    return; 
  }

  const dropdownBtn = document.getElementById("layanan-dropdown-btn");
  const dropdownMenu = document.getElementById("layanan-dropdown-menu");
  const dropdownArrow = document.getElementById("dropdown-arrow");
  const selectedService = document.getElementById("selected-service");
  const dropdownOptions = document.querySelectorAll(".dropdown-option");
  const panels = document.querySelectorAll("#layanan-content > .content-panel");

  const validKeys = [
    "foto_udara_lidar",
    "tematik",
    "software_development",
    "survey",
    "training",
    "data_software_provider"
  ];

  function showPanel(key) {
    if (!validKeys.includes(key)) return;

    // sembunyikan semua panel
    panels.forEach(panel => {
      panel.classList.add("hidden");
      panel.classList.remove("active");
    });

    // tampilkan panel sesuai key
    const targetPanel = document.getElementById("layanan-" + key);
    if (targetPanel) {
      targetPanel.classList.remove("hidden");
      targetPanel.classList.add("active");
    }

    // update teks dropdown
    dropdownOptions.forEach(option => {
      const isActive = option.getAttribute("data-key") === key;
      option.classList.toggle("active", isActive);
      if (isActive) {
        selectedService.textContent = option.getAttribute("data-label");
      }
    });

    // ✅ update hash hanya di home
    history.replaceState(null, "", "#" + key);
  }

  // toggle dropdown
  dropdownBtn?.addEventListener("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    const isOpen = !dropdownMenu.classList.contains("hidden");
    dropdownMenu.classList.toggle("hidden", isOpen);
    dropdownArrow.style.transform = isOpen ? "rotate(0deg)" : "rotate(180deg)";
  });

  // klik opsi
  dropdownOptions.forEach(option => {
    option.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      showPanel(this.getAttribute("data-key"));
      dropdownMenu.classList.add("hidden");
      dropdownArrow.style.transform = "rotate(0deg)";
    });
  });

  // klik luar → tutup
  document.addEventListener("click", function(e) {
    if (dropdownBtn && dropdownMenu &&
        !dropdownBtn.contains(e.target) &&
        !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.add("hidden");
      dropdownArrow.style.transform = "rotate(0deg)";
    }
  });

  // initial load: hanya kalau hash valid
  const initialKey = window.location.hash ? window.location.hash.slice(1) : null;
  if (validKeys.includes(initialKey)) {
    showPanel(initialKey);
  }

  // back/forward
  window.addEventListener("hashchange", function() {
    const key = window.location.hash ? window.location.hash.slice(1) : null;
    if (validKeys.includes(key)) {
      showPanel(key);
    }
  });
});
</script>
@endif

@endif
