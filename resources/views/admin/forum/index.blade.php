@extends('admin.layouts.app')

@section('title', 'Kelola Forum')
@section('page-title', 'Kelola Forum')

@push('head')
  {{-- Alpine untuk interaksi ringan --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  {{-- Chart.js untuk mini-graphs ringkas --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div id="forum-admin-root" class="mx-auto max-w-7xl space-y-6">

    {{-- Header: Aksi cepat --}}
    <div class="bg-white rounded-2xl shadow p-5 sm:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
          <h2 class="text-lg font-semibold">Panel Forum</h2>
          <span id="online-count" class="text-xs text-gray-500 hidden sm:inline">• Memuat pengguna online…</span>
        </div>

        <div class="flex flex-wrap gap-2">
          <button id="btn-clear"
            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition disabled:opacity-50"
            data-loading-text="Menghapus…">
            Hapus Semua Chat
          </button>
        </div>
      </div>
    </div>

    {{-- Overview Stats + Mini Charts --}}
    {{-- dari 4 kartu → tambah 3 kartu baru (VN, Lampiran, Poll) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Total Pesan</div>
        <div class="mt-1 flex items-end justify-between">
          <div class="text-2xl font-semibold" id="stat-total-messages">—</div>
        </div>
        <canvas id="spark-msg" class="mt-3 h-10"></canvas>
      </div>

      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Pesan Hari Ini</div>
        <div class="mt-1 flex items-end justify-between">
          <div class="text-2xl font-semibold" id="stat-messages-today">—</div>
        </div>
        <canvas id="spark-today" class="mt-3 h-10"></canvas>
      </div>

      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Total Pengguna</div>
        <div class="mt-1 text-2xl font-semibold" id="stat-total-users">—</div>
        <div class="mt-2 text-xs text-gray-500" id="stat-online-hint">Online: —</div>
      </div>

      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">User Terban</div>
        <div class="mt-1 text-2xl font-semibold" id="stat-banned-users">—</div>
        <div class="mt-2 text-xs text-gray-500">Poll aktif: <span id="stat-active-polls">—</span></div>
      </div>

      {{-- NEW: Voice Note --}}
      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Voice Note</div>
        <div class="mt-1 text-2xl font-semibold" id="stat-voice-count">—</div>
        <canvas id="spark-vn" class="mt-3 h-10"></canvas>
      </div>

      {{-- NEW: Lampiran --}}
      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Lampiran</div>
        <div class="mt-1 text-2xl font-semibold" id="stat-attachment-count">—</div>
        <canvas id="spark-attach" class="mt-3 h-10"></canvas>
      </div>

      {{-- NEW: Poll dibuat --}}
      <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-gray-500">Poll Dibuat</div>
        <div class="mt-1 text-2xl font-semibold" id="stat-poll-count">—</div>
        <canvas id="spark-poll" class="mt-3 h-10"></canvas>
      </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl shadow">
      <div class="border-b p-3 sm:p-4">
        <div class="flex flex-wrap gap-2">
          {{-- gunakan data-tab untuk JS switcher --}}
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-blue-100 text-blue-700"
            data-tab="tab-overview" aria-selected="true">Ringkasan</button>
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
            data-tab="tab-history" aria-selected="false">Riwayat</button>
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
            data-tab="tab-moderation" aria-selected="false">Moderasi</button>
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
            data-tab="tab-settings" aria-selected="false">Pengaturan</button>
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
            data-tab="tab-reports" aria-selected="false">Laporan</button>
          <button class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
            data-tab="tab-export" aria-selected="false">Ekspor & Broadcast</button>
        </div>
      </div>

      {{-- TAB: Ringkasan --}}
      <div id="tab-overview" class="p-4 sm:p-6">
        <!-- Statistik Utama -->
        <div class="bg-gray-50 border rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="font-semibold">Statistik Utama</div>
            <button id="refresh-stats-overview" class="text-xs px-3 py-1 rounded border hover:bg-white">Refresh</button>
          </div>
          <!-- skeleton & konten -->
          <div id="stats-overview" class="mt-4">
            <!-- Skeleton akan diganti JS -->
            <div class="grid grid-cols-1 gap-4">
              @for ($i = 0; $i < 3; $i++)
                <div class="p-4 rounded-xl bg-white shadow-sm border">
                  <div class="h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                  <div class="h-32 bg-gray-100 rounded animate-pulse"></div>
                </div>
              @endfor
            </div>
          </div>
        </div>
      </div>

      {{-- TAB: Riwayat --}}
      <div id="tab-history" class="p-4 sm:p-6 hidden">
        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <input id="history-search" type="search" placeholder="Cari pesan, user, atau emoji…"
              class="w-full sm:w-80 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <button id="history-search-btn" class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50">Cari</button>
          </div>
          <form id="history-filter-form" class="grid grid-cols-1 sm:grid-cols-5 gap-2">
            <input type="date" name="start_date" class="border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500">
            <input type="date" name="end_date" class="border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500">
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" name="include_deleted" class="h-4 w-4 text-blue-600"> Sertakan yang dihapus
            </label>
            <select name="type" class="border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500">
              <option value="">Semua tipe</option>
              <option value="text">Teks</option>
              <option value="poll">Poll</option>
              <option value="attachment">Lampiran</option>
              <option value="voice">Voice</option>
              <option value="action">Aksi</option>
            </select>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Terapkan</button>
          </form>
        </div>

        {{-- Quick chips --}}
        <div class="mt-3 flex flex-wrap gap-2">
          <button data-chip="today" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Hari
            ini</button>
          <button data-chip="week" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Minggu
            ini</button>
          <button data-chip="month" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Bulan
            ini</button>
          <button data-chip="deleted" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Hanya
            yang dihapus</button>
          <button data-chip="poll" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Hanya
            poll</button>
          <button data-chip="attach" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Hanya
            lampiran</button>
          {{-- NEW: voice --}}
          <button data-chip="voice" class="chip-quick px-3 py-1.5 rounded-full text-xs border hover:bg-gray-50">Hanya
            voice</button>
        </div>

        {{-- List --}}
        <div id="history-wrapper" class="mt-5 space-y-4">
          <div id="history-skeleton" class="space-y-3">
            @for($i = 0; $i < 5; $i++)
              <div class="p-4 rounded-xl border bg-white flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200 animate-pulse"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-3 w-1/3 bg-gray-200 rounded animate-pulse"></div>
                  <div class="h-3 w-2/3 bg-gray-200 rounded animate-pulse"></div>
                  <div class="h-3 w-1/2 bg-gray-200 rounded animate-pulse"></div>
                </div>
              </div>
            @endfor
          </div>
          <div id="history-list" class="space-y-3 hidden"></div>
          <div id="history-empty" class="text-sm text-gray-500 text-center hidden">Tidak ada riwayat sesuai filter.</div>
          <div id="history-loadmore-wrap" class="text-center hidden">
            <button id="history-loadmore" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm">Muat lebih
              banyak</button>
          </div>
        </div>
      </div>

      {{-- TAB: Moderasi --}}
      <div id="tab-moderation" class="p-4 sm:p-6 hidden">
        <div class="flex items-center justify-between gap-3 mb-3">
          <input id="ban-search" type="text" placeholder="Cari pengguna / alasan…"
            class="border rounded-lg px-3 py-2 text-sm w-full sm:w-72 focus:ring-2 focus:ring-blue-500">
          <span id="banned-empty" class="text-sm text-gray-500 hidden">Tidak ada pengguna yang di-ban</span>
        </div>

        <div class="overflow-x-auto border rounded-xl">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr class="text-left text-gray-500 border-b">
                <th class="p-3">Pengguna</th>
                <th class="p-3">Tipe</th>
                <th class="p-3">Alasan</th>
                <th class="p-3">Berakhir</th>
                <th class="p-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody id="banned-list"></tbody>
          </table>
        </div>
      </div>

      {{-- TAB: Pengaturan --}}
      <div id="tab-settings" class="p-4 sm:p-6 hidden">

        @php
          // nilai bantu untuk form
          $mbVal = isset($settings->max_attachment_kb) ? (int) ceil($settings->max_attachment_kb / 1024) : null;

          $pdVal = '';
          $pdUnit = 'minutes';
          $m = (int) ($settings->default_poll_duration_minutes ?? 0);
          if ($m > 0) {
            if ($m % (60 * 24) === 0) {
              $pdUnit = 'days';
              $pdVal = (int) ($m / (60 * 24));
            } elseif ($m % 60 === 0) {
              $pdUnit = 'hours';
              $pdVal = (int) ($m / 60);
            } else {
              $pdUnit = 'minutes';
              $pdVal = $m;
            }
          }
        @endphp

        <form id="settings-form" method="POST" action="{{ url('/admin/forum/settings') }}" class="space-y-6">
          @csrf

          {{-- Header --}}
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-semibold">Pengaturan Forum</h3>
              <p class="text-sm text-gray-500">Kelola fitur, batasan, dan default perilaku forum.</p>
            </div>
            @if(session('success'))
              <span class="px-2.5 py-1 rounded-lg text-xs bg-emerald-50 text-emerald-700">Tersimpan ✓</span>
            @endif
          </div>

          {{-- Kartu: Fitur --}}
          <div class="rounded-2xl border bg-white p-4 sm:p-5 space-y-3">
            <div class="text-sm font-medium text-gray-700 mb-1">Fitur</div>

            {{-- TOGGLE: Izinkan lampiran --}}
            <label class="flex items-center justify-between gap-4 p-3 rounded-xl border hover:bg-gray-50">
              <div>
                <div class="font-medium">Izinkan lampiran</div>
                <div class="text-xs text-gray-500">Gambar, dokumen, video, file umum.</div>
              </div>
              <div class="shrink-0">
                <input type="hidden" name="allow_attachments" value="0">
                <input id="allow_attachments" type="checkbox" name="allow_attachments" value="1" class="sr-only peer"
                  @checked(old('allow_attachments', $settings->allow_attachments ?? false))>

                {{-- track + knob (animasi) --}}
                <span aria-hidden="true" class="relative inline-block h-6 w-11 rounded-full bg-gray-300 transition-colors
                         peer-checked:bg-blue-600
                         after:content-[''] after:absolute after:top-0.5 after:left-0.5
                         after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow
                         after:transition-transform peer-checked:after:translate-x-5"></span>
              </div>
            </label>

            {{-- TOGGLE: Izinkan polling --}}
            <label class="flex items-center justify-between gap-4 p-3 rounded-xl border hover:bg-gray-50">
              <div>
                <div class="font-medium">Izinkan polling</div>
                <div class="text-xs text-gray-500">Pengguna bisa membuat jajak pendapat.</div>
              </div>
              <div class="shrink-0">
                <input type="hidden" name="allow_polls" value="0">
                <input id="allow_polls" type="checkbox" name="allow_polls" value="1" class="sr-only peer"
                  @checked(old('allow_polls', $settings->allow_polls ?? false))>
                <span aria-hidden="true" class="relative inline-block h-6 w-11 rounded-full bg-gray-300 transition-colors
                         peer-checked:bg-blue-600
                         after:content-[''] after:absolute after:top-0.5 after:left-0.5
                         after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow
                         after:transition-transform peer-checked:after:translate-x-5"></span>
              </div>
            </label>

            {{-- TOGGLE: Izinkan voice note --}}
            <label class="flex items-center justify-between gap-4 p-3 rounded-xl border hover:bg-gray-50">
              <div>
                <div class="font-medium">Izinkan voice note</div>
                <div class="text-xs text-gray-500">Aktif/nonaktif pesan suara (voice/audio).</div>
              </div>
              <div class="shrink-0">
                <input type="hidden" name="allow_voice" value="0">
                <input id="allow_voice" type="checkbox" name="allow_voice" value="1" class="sr-only peer"
                  @checked(old('allow_voice', $settings->allow_voice ?? false))>
                <span aria-hidden="true" class="relative inline-block h-6 w-11 rounded-full bg-gray-300 transition-colors
                         peer-checked:bg-blue-600
                         after:content-[''] after:absolute after:top-0.5 after:left-0.5
                         after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow
                         after:transition-transform peer-checked:after:translate-x-5"></span>
              </div>
            </label>
          </div>

          {{-- Kartu: Batasan & Default
          <div class="rounded-2xl border bg-white p-4 sm:p-5 space-y-5">
            <div class="text-sm font-medium text-gray-700 mb-1">Batasan & Default</div>

            Batas ukuran lampiran (MB)
            <div>
              <label class="block text-sm font-medium text-gray-700">Batas ukuran lampiran</label>
              <div class="mt-1 relative">
                <input type="number" min="0" step="1" name="max_attachment_mb"
                  value="{{ old('max_attachment_mb', $mbVal) }}"
                  class="w-full border rounded-xl px-3 py-2 pr-16 focus:ring-2 focus:ring-blue-500"
                  placeholder="Kosongkan untuk tanpa batas">
                <span
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-xs bg-gray-100 border px-2 py-1 rounded">MB</span>
              </div>
              <p class="text-xs text-gray-500 mt-1">0 / kosong = tanpa batas. Dihitung per file.</p>
            </div>

            {{-- Durasi default poll
            <div>
              <label class="block text-sm font-medium text-gray-700">Durasi default poll</label>
              <div class="mt-2 flex flex-wrap items-center gap-2">
                <input type="number" min="1" step="1" name="poll_duration_value"
                  value="{{ old('poll_duration_value', $pdVal) }}"
                  class="border rounded-xl px-3 py-2 w-28 focus:ring-2 focus:ring-blue-500" placeholder="Angka">

                @php $unitOld = old('poll_duration_unit', $pdUnit); @endphp
                <div class="inline-flex rounded-xl border p-0.5">
                  <label class="cursor-pointer">
                    <input type="radio" name="poll_duration_unit" value="minutes" class="sr-only peer" {{
                      $unitOld==='minutes' ?'checked':'' }}>
                    <span
                      class="px-3 py-1.5 text-sm rounded-lg block peer-checked:bg-blue-600 peer-checked:text-white">menit</span>
                  </label>
                  <label class="cursor-pointer">
                    <input type="radio" name="poll_duration_unit" value="hours" class="sr-only peer" {{ $unitOld==='hours'
                      ?'checked':'' }}>
                    <span
                      class="px-3 py-1.5 text-sm rounded-lg block peer-checked:bg-blue-600 peer-checked:text-white">jam</span>
                  </label>
                  <label class="cursor-pointer">
                    <input type="radio" name="poll_duration_unit" value="days" class="sr-only peer" {{ $unitOld==='days'
                      ?'checked':'' }}>
                    <span
                      class="px-3 py-1.5 text-sm rounded-lg block peer-checked:bg-blue-600 peer-checked:text-white">hari</span>
                  </label>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-1">Kosongkan semua untuk tanpa tanggal kadaluwarsa otomatis.</p>
            </div>

            {{-- Maksimum Pengguna
            <div>
              <label class="block text-sm font-medium text-gray-700">Maksimum Pengguna</label>
              <input type="number" name="max_users" value="{{ old('max_users', $settings->max_users ?? '') }}"
                class="mt-1 w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                placeholder="Kosongkan jika tidak dibatasi">
              @error('max_users') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div> --}}

          {{-- Actions --}}
          <div class="flex items-center gap-3">
            <button class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">Simpan</button>
            <button type="reset" class="px-4 py-2 rounded-xl border hover:bg-gray-50">Reset</button>
            <span id="settings-hint" class="text-sm text-gray-500"></span>
          </div>
        </form>
      </div>



      {{-- TAB: Ekspor & Broadcast --}}
      <div id="tab-export" class="p-4 sm:p-6 hidden space-y-6">
        <form id="broadcast-form" class="space-y-2">
          <div class="text-sm font-medium text-gray-700">Broadcast Pengumuman</div>
          <textarea name="message" rows="4" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
            placeholder="Tulis pengumuman untuk forum…"></textarea>
          <input type="hidden" name="message_type" value="announcement">
          <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
              Kirim Pengumuman
            </button>
            <span id="broadcast-hint" class="text-sm text-gray-500"></span>
          </div>
        </form>

        <hr class="border-gray-200">

        <form id="export-form" class="space-y-3">
          <div class="text-sm font-medium text-gray-700">Ekspor Riwayat Chat</div>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="date" name="start_date" class="border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
            <input type="date" name="end_date" class="border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" name="include_deleted" class="h-4 w-4 text-blue-600"> Sertakan pesan dihapus
            </label>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Cari</button>
          </div>
        </form>
        <pre id="export-result" class="bg-gray-50 p-4 rounded-lg text-xs overflow-auto max-h-96"></pre>
        <div class="flex items-center gap-2 mt-3">
          <button id="btn-download-json"
            class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900 disabled:opacity-40" disabled>
            Download JSON
          </button>
          <button id="btn-download-csv"
            class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900 disabled:opacity-40" disabled>
            Download CSV
          </button>
        </div>

      </div>

      {{-- TAB: Laporan (Report User) --}}
      <div id="tab-reports" class="p-4 sm:p-6 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <!-- Sidebar Filter -->
          <aside class="lg:col-span-1 space-y-3">
            <div class="rounded-xl border bg-white p-4">
              <div class="text-sm font-semibold mb-2">Filter Laporan</div>
              <div class="space-y-2">
                <input type="date" id="rep-start" class="w-full border rounded-lg px-3 py-2 text-sm">
                <input type="date" id="rep-end" class="w-full border rounded-lg px-3 py-2 text-sm">
                <select id="rep-reason" class="w-full border rounded-lg px-3 py-2 text-sm">
                  <option value="">Semua alasan</option>
                  <option value="spam">Spam</option>
                  <option value="abusive">Kasar/Perundungan</option>
                  <option value="hate">Ujaran kebencian</option>
                  <option value="scam">Penipuan</option>
                  <option value="porn">Konten pornografi</option>
                  <option value="other">Lainnya</option>
                </select>
                <button id="rep-apply"
                  class="w-full px-3 py-2 rounded-lg bg-blue-600 text-white text-sm">Terapkan</button>
              </div>
            </div>
          </aside>

          <!-- List -->
          <section class="lg:col-span-3">
            <div id="rep-empty" class="hidden text-sm text-gray-500 text-center">Belum ada laporan.</div>
            <div id="rep-list" class="space-y-3"></div>
          </section>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal konfirmasi hapus semua --}}
  <div id="clear-confirm-modal" class="fixed inset-0 bg-black/50 hidden z-50">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="p-5">
          <h3 class="text-lg font-semibold">Konfirmasi</h3>
          <p class="mt-1 text-sm text-gray-600">Hapus semua riwayat chat? Tindakan ini tidak dapat dibatalkan.</p>
          <div class="mt-5 flex justify-end gap-2">
            <button id="clear-cancel" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
            <button id="clear-confirm" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    (function () {
      const tabs = document.querySelectorAll('.tab-btn');
      const panels = ['tab-overview', 'tab-history', 'tab-moderation', 'tab-settings', 'tab-export', 'tab-reports'];
      tabs.forEach(btn => {
        btn.addEventListener('click', () => {
          const target = btn.dataset.tab;
          panels.forEach(id => document.getElementById(id).classList.toggle('hidden', id !== target));
          tabs.forEach(b => b.classList.remove('bg-blue-100', 'text-blue-700'));
          btn.classList.add('bg-blue-100', 'text-blue-700');
        });
      });

      async function fetchJSON(url) {
        const res = await fetch(url, { credentials: 'same-origin' });
        const data = await res.json();
        return data?.data || [];
      }

      function itemHTML(r) {
        const t = new Date(r.created_at).toLocaleString('id-ID');
        const msg = r.message?.text || '';
        return `
        <div class="p-4 rounded-xl border bg-white flex items-start gap-3">
          <div class="w-10 h-10 rounded-full bg-gray-200 grid place-items-center text-xs font-bold text-gray-600">
            ${String(r.reporter?.name || 'U').slice(0, 1).toUpperCase()}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm text-gray-600">${t}</div>
            <div class="font-semibold text-gray-800">${(r.reporter?.name || '-')} → ${(r.target?.name || '-')}</div>
            <div class="mt-1 text-xs inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-700 border border-red-200">${r.reason}</div>
            ${msg ? `<p class="mt-2 text-sm text-gray-700 line-clamp-2">${msg}</p>` : ''}
            ${r.notes ? `<p class="mt-1 text-xs text-gray-500">Catatan: ${r.notes}</p>` : ''}
          </div>
        </div>`;
      }

      async function loadReports() {
        const list = document.getElementById('rep-list');
        const empty = document.getElementById('rep-empty');
        list.innerHTML = '';
        const data = await fetchJSON('{{ route('admin.forum.reports') }}');
        if (!data.length) { empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        list.innerHTML = data.map(itemHTML).join('');
      }

      document.getElementById('rep-apply')?.addEventListener('click', () => {
        loadReports();
      });

      // pre-load when tab opened
      document.querySelector('[data-tab="tab-reports"]')?.addEventListener('click', loadReports);
    })();
  </script>
@endpush

@push('scripts')
  {{-- JS aplikasi admin forum kamu --}}
  @vite(['resources/js/forum-admin.js'])

  <script>
    // Simple tab switcher (non-Alpine, agar universal)
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        // header state
        document.querySelectorAll('.tab-btn').forEach(b => {
          b.classList.remove('bg-blue-100', 'text-blue-700');
          b.classList.add('text-gray-600', 'hover:bg-gray-100');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('bg-blue-100', 'text-blue-700');
        btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
        btn.setAttribute('aria-selected', 'true');

        // body switch
        document.querySelectorAll('[id^="tab-"]').forEach(p => p.classList.add('hidden'));
        document.getElementById(btn.dataset.tab).classList.remove('hidden');
      });
    });

    // Modal clear all
    const modal = document.getElementById('clear-confirm-modal');
    document.getElementById('btn-clear')?.addEventListener('click', () => modal.classList.remove('hidden'));
    document.getElementById('clear-cancel')?.addEventListener('click', () => modal.classList.add('hidden'));

    // Mini sparkline helpers (optional)
    function spark(id, data = []) {
      const el = document.getElementById(id);
      if (!el) return;
      new Chart(el.getContext('2d'), {
        type: 'line',
        data: { labels: data.map((_, i) => i + 1), datasets: [{ data, tension: .4, pointRadius: 0 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
      });
    }

    // expose ke window biar dipakai forum-admin.js (opsional)
    window.spark = spark;
  </script>
@endpush