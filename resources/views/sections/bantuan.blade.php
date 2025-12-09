@extends('layout.app')

@section('title', $title ?? 'User Manual RKA')

@section('content')
<br>
<div class="max-w-6xl mx-auto p-8 bg-white rounded-2xl shadow-lg border border-gray-200">

  <!-- Header -->
  <header class="text-center mb-10">
    <h1 class="text-3xl font-extrabold text-blue-700 tracking-tight mb-2">
      {{ $title ?? 'User Manual Website PT. Rekan Kinerja Abadi (RKA)' }}
    </h1>
    <p class="text-gray-600 text-lg">
      {{ $subtitle ?? 'Panduan penggunaan situs: Navigasi, Layanan, Blog, Forum Diskusi, dan Autentikasi' }}
    </p>
    <p class="text-sm text-gray-500 italic mt-3">
      Disusun pada {{ $today ?? now()->translatedFormat('d F Y') }}
    </p>
    <div class="mt-4 h-1 w-24 bg-blue-600 mx-auto rounded-full"></div>
  </header>

  <!-- Ringkasan -->
  <section class="mb-10">
    <h2 class="text-2xl font-semibold text-blue-700 mb-4 border-b-2 border-blue-100 pb-2">Ringkasan</h2>
    <p class="text-gray-700 leading-relaxed mb-3">
      Manual ini menjelaskan fitur utama website PT. Rekan Kinerja Abadi (RKA),
      meliputi navigasi menu utama, halaman layanan, blog, forum diskusi, dan alur autentikasi.
    </p>
    <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
      <li>Navigasi menu utama, layanan, blog, forum diskusi.</li>
      <li>Autentikasi login, pendaftaran akun, lupa kata sandi.</li>
      <li>Dilengkapi gambar antarmuka pengguna (UI).</li>
    </ul>
  </section>

  <!-- Langkah Operasional -->
  <section class="mb-10 bg-blue-50 p-6 rounded-xl border border-blue-100">
    <h2 class="text-2xl font-semibold text-blue-800 mb-4">Langkah Operasional</h2>
    <ol class="list-decimal list-inside text-gray-700 space-y-2 ml-4">
      <li><strong>Navigasi Dasar:</strong> Gunakan navbar untuk berpindah antar-halaman.</li>
      <li><strong>Forum Diskusi:</strong> Klik tombol hijau di kanan bawah untuk membuka chat.</li>
      <li><strong>Login & Pendaftaran:</strong> Buat akun untuk mengakses fitur penuh.</li>
    </ol>
  </section>

  <!-- Gambar Panduan -->
  <section class="mb-10">
    <h2 class="text-2xl font-semibold text-blue-700 mb-4 border-b-2 border-blue-100 pb-2">
      Panduan Penggunaan Website PT. Rekan Kinerja Abadi
    </h2>

    @php
        $steps = [
            // 1–10: halaman utama & layanan
            'Halaman Beranda – Tampilan awal website RKA.',
            'Halaman Tentang Kami – Informasi profil perusahaan dan kegiatan utama.',
            'Layanan – Pemetaan Foto Udara & Lidar.',
            'Layanan – Pemetaan Tematik.',
            'Layanan – Pengembangan Software & WebGIS.',
            'Layanan – Survey (Hydrographi & Terestrial).',
            'Layanan – Training (Program Pelatihan Geomatika).',
            'Layanan – Data & Software Provider (Citra Satelit & Software GIS).',
            'Halaman Mitra Kerja – Daftar lembaga & universitas mitra.',

            // 11–13: blog detail
             'Halaman Blog & Artikel – Tampilan awal daftar artikel perusahaan.',
             'Halaman Blog – Daftar artikel kegiatan perusahaan.',
            'Detail Artikel Blog – Menampilkan isi artikel lengkap.',
            'Komentar Artikel – Tampilan kolom komentar pengguna.',

            // 15–19: autentikasi
              'Forum – Akses terkunci jika belum login.',
            'Halaman Login – Form masuk pengguna.',
            'Halaman Login (Form Terisi) – Contoh login sebelum masuk.',
            'Halaman Pendaftaran – Formulir buat akun baru.',
            'Contoh Pendaftaran – Pengisian form dengan data pengguna.',
           
            'Halaman Lupa Kata Sandi – Reset kata sandi melalui email.',

            // 20–23: forum
            'Ikon Forum – Tombol hijau untuk membuka forum diskusi.',
            'Forum Diskusi – Tampilan awal forum.',
            'Forum Diskusi – Fitur attachment ',
            'Forum Diskusi – Fitur emoji',
            'Forum Diskusi – Fitur Dokumen',
            'Forum Diskusi – Fitur Open Dokumen',
            'Forum Diskusi – Fitur Kirim Dokumen',
            'Forum Diskusi – Fitur Tampilkan Dokumen Terkirim',
            'Forum Diskusi – Fitur Image',
            'Forum Diskusi – Fitur Open Image',
            'Forum Diskusi – Fitur Kirim Image',
            'Forum Diskusi – Fitur Tampilkan Image Terkirim',
            'Forum Diskusi – Fitur Kamera',
            'Forum Diskusi – Fitur Take Foto',
            'Forum Diskusi – Fitur Konfirmasi Foto',
            'Forum Diskusi – Fitur Kirim Foto',
            'Forum Diskusi – Fitur Tampilkan Foto Terkirim',
            'Forum Diskusi – Fitur Take Video',
            'Forum Diskusi – Fitur Stop Video',
            'Forum Diskusi – Fitur Konfirmasi Video',
            'Forum Diskusi – Fitur Kirim Video',
            'Forum Diskusi – Fitur Tampilkan Video Terkirim',
            'Forum Diskusi – Fitur Lokasi',
            'Forum Diskusi – Fitur Share Lokasi',
            'Forum Diskusi – Fitur Share Lokasi yang sudah terisi',
            'Forum Diskusi – Fitur Kirim Lokasi',
            'Forum Diskusi – Fitur Tampilkan Lokasi Terkirim',
            'Forum Diskusi – Fitur Polling',
            'Forum Diskusi – Fitur Buat Polling',
            'Forum Diskusi – Fitur Buat Polling (Sudah Terisi)',
            'Forum Diskusi – Fitur Tampilkan Polling Terkirim',
            'Forum Diskusi – Fitur Hasil Polling',
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
      @php $imageIndex = 1; @endphp
      @for ($i = 1; $i <= 52; $i++)
          @continue($i === 14) {{-- lewati gambar ke-14 --}}
          <figure class="bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all duration-300 p-3 flex flex-col justify-between">
            <div class="flex justify-center items-center overflow-hidden rounded-lg bg-gray-50" style="min-height: 250px;">
              <img 
                src="{{ asset('image/1 (' . $i . ').png') }}" 
                alt="Langkah {{ $imageIndex }}" 
                class="max-h-[380px] w-auto object-contain">
            </div>
            <figcaption class="text-sm text-gray-700 text-center pt-4 leading-relaxed">
              <span class="font-semibold text-blue-700 block mb-1">Gambar {{ $imageIndex }}</span>
              {{ $steps[$imageIndex - 1] ?? 'Langkah tidak diketahui' }}
            </figcaption>
          </figure>
          @php $imageIndex++; @endphp
      @endfor
    </div>
  </section>

 
</div>
<br>

 <!-- Footer -->
    <section id="hubungi-kami" class="bg-gray-100">
        @include('components.footer')
    </section>

@endsection
