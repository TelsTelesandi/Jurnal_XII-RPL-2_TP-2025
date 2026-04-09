@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('head')
  {{-- Chart.js untuk grafik forum --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
@php
  $m = $metrics ?? [];

  // Metrik yang sudah pasti terhubung
  $numbers = [
    'users_total'        => (int) ($m['users_total'] ?? 0),
    'articles_total'     => (int) ($m['articles_total'] ?? 0),

    'comments_pending'   => (int) ($m['comments_pending'] ?? 0),
    'forum_interactions' => (int) ($m['forum_interactions'] ?? 0),
  ];

  $maxVal = max(1, ...array_values($numbers));
  $fmt = fn($v) => number_format($v ?? 0);

  // Label komentar otomatis (pending/total)
  $labelComments = $m['comments_label'] ?? 'Total Komentar';
@endphp

<div class="space-y-6">

  {{-- HERO / GREETING --}}
  <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50">
    <div class="px-3 py-4 sm:px-6 sm:py-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
      <div class="flex items-start gap-2 sm:gap-3">
        <div class="h-8 w-8 sm:h-10 sm:w-10 shrink-0 rounded-xl bg-indigo-600 text-white grid place-content-center shadow-md">
          <i class="fas fa-user-shield text-sm sm:text-lg"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs sm:text-sm text-gray-600">Selamat datang kembali,</p>
          <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 truncate">{{ auth()->user()->name ?? 'Administrator' }}</h2>
          <p class="mt-1 text-[10px] sm:text-xs text-gray-500">
            Terakhir diperbarui:
            <span id="hero-last-updated">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
            • {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
          </p>
        </div>
      </div>

      {{-- ACTIONS: wrap & ringkas di mobile --}}
      <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 md:gap-3 w-full md:w-auto justify-center md:justify-end">
        <a href="{{ route('admin.blog.index', [], false) }}"
           class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border bg-white/70 px-2.5 py-1.5 sm:px-3 sm:py-2 text-[10px] sm:text-xs font-medium text-gray-700 hover:bg-white whitespace-nowrap">
          <i class="fas fa-plus text-xs sm:text-sm"></i>
          <span class="sm:hidden">Tulis</span>
          <span class="hidden sm:inline">Tulis Artikel</span>
        </a>

        <a href="{{ route('admin.forum.index', [], false) }}"
           class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border bg-white/70 px-2.5 py-1.5 sm:px-3 sm:py-2 text-[10px] sm:text-xs font-medium text-gray-700 hover:bg-white whitespace-nowrap">
          <i class="fas fa-comments text-xs sm:text-sm"></i>
          <span class="sm:hidden">Forum</span>
          <span class="hidden sm:inline">Kelola Forum</span>
        </a>

        <a href="{{ route('admin.comments.index', [], false) }}"
           class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border bg-white/70 px-2.5 py-1.5 sm:px-3 sm:py-2 text-[10px] sm:text-xs font-medium text-gray-700 hover:bg-white whitespace-nowrap">
          <i class="fas fa-inbox text-xs sm:text-sm"></i>
          <span class="sm:hidden">Komentar</span>
          <span class="hidden sm:inline">Review Komentar</span>
        </a>
      </div>
    </div>
  </div>

  {{-- METRIC CARDS --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
    @php
      $cards = [
        ['label'=>'Total Users',        'key'=>'users_total',        'icon'=>'fas fa-users',          'from'=>'from-indigo-500 to-blue-500'],
        ['label'=>'Total Artikel',      'key'=>'articles_total',     'icon'=>'fas fa-newspaper',      'from'=>'from-green-500 to-emerald-500'],
     
        ['label'=> $labelComments,      'key'=>'comments_pending',   'icon'=>'fas fa-clipboard-list', 'from'=>'from-rose-500 to-red-500'],
        ['label'=>'Forum Interactions', 'key'=>'forum_interactions', 'icon'=>'fas fa-hand-pointer',   'from'=>'from-violet-500 to-purple-500'],
      ];
    @endphp

    @foreach ($cards as $c)
      @php $val = $numbers[$c['key']] ?? 0; @endphp

      <div class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
        <div class="hidden sm:block absolute -right-6 -top-6 h-20 w-20 rounded-full bg-gradient-to-br {{ $c['from'] }} opacity-10 blur-lg"></div>
        <div class="p-3 sm:p-4 flex items-start gap-2 sm:gap-3">
          <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-gradient-to-br {{ $c['from'] }} text-white grid place-content-center shadow-sm flex-shrink-0">
            <i class="{{ $c['icon'] }} text-sm sm:text-base"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] sm:text-xs uppercase tracking-wide text-gray-500 truncate">{{ $c['label'] }}</p>
            <div class="mt-1 flex items-baseline gap-1.5 sm:gap-2">
              <span class="text-lg sm:text-xl font-semibold text-gray-900">{{ $fmt($val) }}</span>
            </div>
          </div>
        </div>

        {{-- Footer link per kartu --}}
        @switch($c['key'])
          @case('users_total')
            <div class="border-t bg-gray-50/60 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm">
              <a href="{{ route('admin.users.index', [], false) }}" class="text-indigo-700 hover:text-indigo-900 block truncate">Kelola Users</a>
            </div>
          @break
          @case('articles_total')
            <div class="border-t bg-gray-50/60 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm">
              <a href="{{ route('admin.blog.index', [], false) }}" class="text-green-700 hover:text-green-900 block truncate">Kelola Artikel</a>
            </div>
          @break
          
          @break
          @case('comments_pending')
            <div class="border-t bg-gray-50/60 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm">
              <a href="{{ route('admin.comments.index', [], false) }}" class="text-rose-700 hover:text-rose-900 block truncate">Review Komentar</a>
            </div>
          @break
        @endswitch
      </div>
    @endforeach
  </div>

  {{-- FORUM SNAPSHOT (Live) --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="px-3 py-3 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
      <div class="flex items-center gap-1.5 sm:gap-2">
        <h3 class="text-xs sm:text-sm font-semibold tracking-tight">Forum Snapshot (Live)</h3>
        <span id="forum-online-chip" class="ml-2 inline-flex items-center gap-1.5 sm:gap-2 rounded-full bg-emerald-50 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[10px] sm:text-[12px] font-medium text-emerald-700">
          <span class="inline-block h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500 animate-pulse"></span>
          Online: 0
        </span>
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm">
        <span id="forum-last-updated" class="text-gray-500 hidden">—</span>
        <button id="forum-refresh" class="inline-flex items-center gap-1 sm:gap-2 rounded-lg border bg-white px-2.5 py-1 sm:px-3 sm:py-1.5 hover:bg-gray-50 text-xs">
          <i class="fas fa-rotate-right"></i> <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <div class="px-3 sm:px-6 pb-4 sm:pb-5">
      {{-- Mini cards --}}
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
        @php
          $mini = [
            ['label'=>'Total Pesan', 'id'=>'statForumTotal'],
            ['label'=>'Pesan Hari Ini', 'id'=>'statForumToday'],
            ['label'=>'Total Pengguna', 'id'=>'statForumUsers'],
            ['label'=>'User Terban', 'id'=>'statForumBanned'],
            ['label'=>'Voice Note', 'id'=>'statForumVoice'],
            ['label'=>'Lampiran', 'id'=>'statForumFiles'],
          ];
        @endphp
        @foreach($mini as $x)
          <div class="rounded-xl border bg-gray-50/80 p-2 sm:p-3">
            <div class="text-[9px] sm:text-[11px] uppercase tracking-wide text-gray-500 truncate">{{ $x['label'] }}</div>
            <div id="{{ $x['id'] }}" class="mt-0.5 sm:mt-1 text-lg sm:text-xl font-semibold text-gray-900">—</div>
          </div>
        @endforeach
      </div>

      {{-- Chart --}}
      <div class="mt-4 sm:mt-6 rounded-xl border p-2 sm:p-3">
        <div class="flex items-center justify-between mb-1 sm:mb-2">
          <h4 class="text-xs sm:text-sm text-gray-600">Aktivitas 7 Hari Terakhir</h4>
        </div>
        <div class="h-32 sm:h-48 md:h-64 relative">
          {{-- Canvas mengikuti tinggi parent (tanpa height statis) --}}
          <canvas id="forum-7d" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
(function(){
  const ENDPOINT_STATS  = @json(url('/admin/forum/stats'));
  const ENDPOINT_ONLINE = @json(url('/forum/online-count'));

  let chart7d;

  const $  = (sel, p=document)=> p.querySelector(sel);
  const fmt= (n)=> new Intl.NumberFormat().format(n ?? 0);
  const setText = (id, v)=> { const el = $('#'+id); if (el) el.textContent = v; };

  function stampNow(){
    const now = new Date();
    const hh = String(now.getHours()).padStart(2,'0');
    const mm = String(now.getMinutes()).padStart(2,'0');
    const s1 = $('#forum-last-updated');
    const s2 = $('#hero-last-updated');
    if (s1){ s1.textContent = `Terakhir update ${hh}:${mm}`; s1.classList.remove('hidden'); }
    if (s2){ s2.textContent = `${hh}:${mm}`; }
  }

  async function loadOnline(){
    try{
      const res = await fetch(ENDPOINT_ONLINE, {credentials:'same-origin', headers:{Accept:'application/json'}});
      const data = await res.json();
      const chip = $('#forum-online-chip');
      if (!chip) return;
      chip.classList.remove('hidden');
      chip.innerHTML = `<span class="inline-block h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500 animate-pulse"></span> Online: ${fmt(data?.count ?? 0)}`;
    }catch(e){}
  }

  async function loadForumStats(){
    try{
      const res = await fetch(ENDPOINT_STATS, {credentials:'same-origin', headers:{Accept:'application/json'}});
      const json = await res.json();
      const d = json?.data || {};

      // angka
      setText('statForumTotal',  fmt(d.total_messages));
      setText('statForumToday',  fmt(d.messages_today));
      setText('statForumUsers',  fmt(d.total_users));
      setText('statForumBanned', fmt(d.banned_users));
      setText('statForumVoice',  fmt(d.voice_count ?? d.voices_count));
      setText('statForumFiles',  fmt(d.attachments_count ?? d.files_count));

      // chart 7 hari
      const labels = d.last7_labels || [];

      // Tambahan dataset: Artikel & Komentar.
      // Fallback key agar kompatibel dengan berbagai nama field dari API:
      const articlesSeries = d.last7_articles_series ?? d.last7_posts_series ?? d.last7_blog_series ?? [];
      const commentsSeries = d.last7_comments_series ?? d.last7_comment_series ?? [];

      const series = [
        {label:'Pesan',     data:d.last7_series||[],             color:'rgb(59,130,246)'},  // blue-500
        {label:'Dihapus',   data:d.last7_deleted_series||[],     color:'rgb(225,29,72)'},   // rose-600
        {label:'VN',        data:d.last7_voice_series||[],       color:'rgb(34,197,94)'},   // green-500
        {label:'Lampiran',  data:d.last7_attach_series||[],      color:'rgb(234,179,8)'},   // amber-400
        {label:'Poll',      data:d.last7_poll_series||[],        color:'rgb(99,102,241)'},  // indigo-400
        {label:'Artikel',   data:articlesSeries,                 color:'rgb(20,184,166)'},  // teal-500
        {label:'Komentar',  data:commentsSeries,                 color:'rgb(100,116,139)'}  // slate-500
      ];

      const ctx = document.getElementById('forum-7d')?.getContext('2d');
      if (!ctx) return;
      if (chart7d) chart7d.destroy();

      chart7d = new Chart(ctx, {
        type:'line',
        data:{
          labels,
          datasets: series.map(s => ({
            label:s.label,
            data:s.data,
            tension:.35,
            borderWidth:2,
            pointRadius:0,
            borderColor:s.color,
            fill:false
          }))
        },
        options:{
          responsive:true,
          maintainAspectRatio:false, // penting untuk mobile
          plugins:{
            legend:{
              display: window.innerWidth >= 640, // sembunyikan legend di mobile
              position:'top'
            },
            tooltip:{ mode:'index', intersect:false }
          },
          interaction:{ intersect:false, mode:'index' },
          scales:{
            x:{
              display:true,
              grid:{ display:false },
              ticks:{ maxRotation:45, minRotation:0 }
            },
            y:{
              display:true,
              beginAtZero:true,
              grid:{ color:'rgba(0,0,0,0.05)' },
              ticks:{ font:{ size: window.innerWidth < 640 ? 10 : 12 } }
            }
          }
        }
      });

      stampNow();
    }catch(e){
      console.error('loadForumStats failed:', e);
    }
  }

  document.getElementById('forum-refresh')?.addEventListener('click', () => {
    loadForumStats(); loadOnline();
  });

  // init
  loadForumStats();
  loadOnline();
  setInterval(loadForumStats, 30000);
  setInterval(loadOnline, 10000);

  // Respons saat orientasi/resize
  window.addEventListener('resize', () => {
    if (chart7d) { chart7d.destroy(); loadForumStats(); }
  });
})();
</script>
@endpush
