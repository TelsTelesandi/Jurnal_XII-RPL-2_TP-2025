<!-- resources/views/sections/mitra_kerja.blade.php -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-8 items-center justify-center">
    @php
        // Array logo (asli, tidak diubah)
        $logos = [
            // Pemerintah Daerah
            'kedudukan-dan-fungsi-pemerintah-pusat-daerah.jpg',
            'Jakarta.png',
            'ESDM.png',

            // Universitas Negeri
            'logoui.png',
            'logounj.png',
            'Logo_IPB_New.png',
            'Itenas.png',
            'ITB.png',
        ];

        // Nama perusahaan / instansi
        $namaPerusahaan = [
            'Pemerintah Pusat & Daerah',
            'Pemerintah Provinsi DKI Jakarta',
            'Kementerian ESDM',
            'Universitas Indonesia',
            'Universitas Negeri Jakarta',
            'Institut Pertanian Bogor',
            'Institut Teknologi Nasional',
            'Institut Teknologi Bandung',
        ];
    @endphp

    @foreach($logos as $index => $logo)
        <div class="flex flex-col items-center justify-between px-6 text-center">
            <!-- Kotak pembungkus logo agar tinggi seragam -->
            <div class="h-32 flex items-center justify-center">
                <img src="{{ asset('image/'.$logo) }}" 
                     alt="Logo {{ $namaPerusahaan[$index] ?? 'Mitra' }}" 
                     class="max-h-24 object-contain">
            </div>
            <!-- Nama perusahaan -->
            <p class="mt-3 text-gray-800 font-medium text-sm leading-tight h-10 flex items-center justify-center">
                {{ $namaPerusahaan[$index] ?? 'Nama Mitra' }}
            </p>
        </div>
    @endforeach
</div>
