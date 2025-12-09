// resources/js/forum-admin.js

function escapeHtml(s) {
  return String(s || '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function downloadBlob(filename, mime, content) {
  const blob = new Blob([content], { type: mime });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

// ===== Download handlers (opsional) =====
function wireDownloadButtons() {
  const pre = document.getElementById('export-result');
  const btnJson = document.getElementById('btn-download-json');
  const btnCsv  = document.getElementById('btn-download-csv');

  const hasData = !!(pre && pre.textContent.trim());
  if (!btnJson || !btnCsv) return;

  // enable/disable sesuai data
  btnJson.disabled = !hasData;
  btnCsv.disabled  = !hasData;

  if (!hasData) return;

  // bersihkan listener lama (hindari duplikasi)
  btnJson.replaceWith(btnJson.cloneNode(true));
  btnCsv.replaceWith(btnCsv.cloneNode(true));

  const btnJsonNew = document.getElementById('btn-download-json');
  const btnCsvNew  = document.getElementById('btn-download-csv');

  // JSON download
  btnJsonNew.addEventListener('click', () => {
    const content = pre.textContent;
    if (!content.trim()) return;
    downloadBlob(`forum-export-${Date.now()}.json`, 'application/json;charset=utf-8', content);
  });

  // CSV download
  btnCsvNew.addEventListener('click', () => {
    let data;
    try { data = JSON.parse(pre.textContent); } catch (_) { return; }

    const rows = data?.messages || [];
    if (!rows.length) return;

    const headers = ['id','user','message','type','reply_to','created_at','is_deleted','deleted_by'];
    const toCell = (v) => {
      const s = (v ?? '').toString();
      return `"${s.replace(/"/g,'""')}"`;
    };
    const csv = [
      headers.map(toCell).join(','),
      ...rows.map(r => headers.map(h => toCell(r[h])).join(','))
    ].join('\n');

    downloadBlob(`forum-export-${Date.now()}.csv`, 'text/csv;charset=utf-8', csv);
  });
}

// Panggil sekali saat load awal (kalau pre sudah terisi dari server — biasanya kosong)
document.addEventListener('DOMContentLoaded', wireDownloadButtons);

// ===== History helpers (DROP-IN) =====
function normalizeType(t) {
  const x = String(t || '').toLowerCase();
  if (['image','document','video','file','attachment'].includes(x)) return 'attachment';
  if (['voice','audio'].includes(x)) return 'voice';
  if (['poll','polling','vote'].includes(x)) return 'poll';
  if (!x) return 'text';
  return x;
}

function initialsOf(name = '') {
  const parts = String(name).trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0].slice(0,2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function colorFrom(str = '') {
  const palette = ['#2563eb','#059669','#dc2626','#7c3aed','#ea580c','#16a34a','#d97706','#0ea5e9','#db2777','#374151'];
  let h = 0; for (let i=0;i<str.length;i++) h = (h*31 + str.charCodeAt(i)) >>> 0;
  return palette[Math.abs(h) % palette.length];
}

function avatarHTML(name, url) {
  if (url) {
    const safe = escapeHtml(url);
    return `<img class="w-10 h-10 rounded-full object-cover bg-gray-100" src="${safe}" alt="">`;
  }
  const initials = initialsOf(name);
  const bg = colorFrom(name);
  return `
    <div class="w-10 h-10 rounded-full grid place-items-center text-white text-xs font-semibold"
         style="background:${bg}">${escapeHtml(initials)}</div>
  `;
}

function renderBody(m) {
  const t = (m?.raw_type || m?.type || '').toLowerCase();
  const hasFile = !!m?.attachment_url;
  const fname   = m?.attachment_name || '';

  // base text (caption/teks)
  const baseText = m?.content
    ? `<div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap break-words">${escapeHtml(m.content)}</div>`
    : '';

  if (t === 'image' && hasFile) {
    return `
      <a href="${escapeHtml(m.attachment_url)}" target="_blank" class="block mt-2">
        <img src="${escapeHtml(m.attachment_url)}"
             alt="${escapeHtml(fname || 'image')}"
             class="max-h-48 rounded-lg border">
      </a>
      ${fname ? `<div class="text-xs text-gray-500 mt-1">File: ${escapeHtml(fname)}</div>` : ''}
      ${baseText}
      ${renderLocation(m)}
    `;
  }
  if (t === 'document' && hasFile) {
    return `
      <a href="${escapeHtml(m.attachment_url)}" target="_blank"
         class="inline-flex items-center gap-2 mt-2 px-3 py-2 rounded-lg border hover:bg-gray-50">
        <span>📎</span>
        <span class="text-sm">${escapeHtml(fname || 'Lampiran')}</span>
      </a>
      ${baseText}
      ${renderLocation(m)}
    `;
  }
  if (t === 'voice' && hasFile) {
    return `
      <audio class="mt-2 w-full" controls src="${escapeHtml(m.attachment_url)}"></audio>
      ${baseText}
      ${renderLocation(m)}
    `;
  }
  if (t === 'video' && hasFile) {
    return `
      <video class="mt-2 max-h-60 rounded-lg border" controls src="${escapeHtml(m.attachment_url)}"></video>
      ${fname ? `<div class="text-xs text-gray-500 mt-1">File: ${escapeHtml(fname)}</div>` : ''}
      ${baseText}
      ${renderLocation(m)}
    `;
  }
  if (t === 'poll') {
    return `
      <div class="mt-1 text-sm text-gray-700 whitespace-pre-wrap break-words">
        ${escapeHtml(m?.content || '[Poll]')}
      </div>
      ${renderLocation(m)}
    `;
  }
  // default (text/unknown)
  return `
    <div class="mt-1 text-sm text-gray-700 whitespace-pre-wrap break-words">
      ${escapeHtml(m?.content || '')}
    </div>
    ${renderLocation(m)}
  `;
}

function renderLocation(m) {
  const loc = m?.location || {};
  const hasName = !!loc.name;
  const hasLatLng = (loc.lat != null && loc.lng != null);
  if (!hasName && !hasLatLng) return '';

  const label = hasName ? escapeHtml(loc.name) : 'Lokasi';
  const q     = hasLatLng ? `${loc.lat},${loc.lng}` : encodeURIComponent(label);
  const url   = `https://maps.google.com/?q=${q}`;

  return `
    <div class="mt-2 text-xs text-gray-600">
      📍 <a class="underline hover:no-underline" href="${url}" target="_blank">${label}</a>
      ${hasLatLng ? ` <span class="text-gray-400">(${loc.lat}, ${loc.lng})</span>` : ''}
    </div>
  `;
}

// ===== History init (DROP-IN) =====
function initHistory() {
  const listEl       = document.getElementById('history-list');
  const skeletonEl   = document.getElementById('history-skeleton');
  const emptyEl      = document.getElementById('history-empty');
  const loadMoreWrap = document.getElementById('history-loadmore-wrap');
  const loadMoreBtn  = document.getElementById('history-loadmore');
  const searchInput  = document.getElementById('history-search');
  const searchBtn    = document.getElementById('history-search-btn');
  const filterForm   = document.getElementById('history-filter-form');
  const chipBtns     = Array.from(document.querySelectorAll('.chip-quick'));

  // ambil helper dari bootstrap awal
  const fa = window.forumAdmin || {};
  const api = fa.api;
  const endpoints = fa.endpoints || {};

  if (!api || !endpoints.history || !listEl || !skeletonEl || !emptyEl) return;

  // Hilangkan efek hover hanya pada chip riwayat
  const stripHoverClasses = (el) => {
    if (!el || !el.className) return;
    el.className = String(el.className)
      .split(/\s+/)
      .filter(c => !c.startsWith('hover:'))
      .join(' ');
  };
  chipBtns.forEach(stripHoverClasses);

  // State
  let nextCursor = null;
  let firstLoaded = false;
  // DEFAULT include_deleted eksplisit '0' (tidak menyertakan yang dihapus)
  let params = { include_deleted: '0' }; // q, start_date, end_date, include_deleted, type

  const qs = (obj) => {
    const u = new URLSearchParams();
    Object.entries(obj || {}).forEach(([k,v]) => {
      if (v !== undefined && v !== null && String(v) !== '') u.append(k, v);
    });
    return u.toString() ? ('?' + u.toString()) : '';
  };

  async function fetchHistory({ append = false } = {}) {
    if (!append) {
      skeletonEl.classList.remove('hidden');
      listEl.classList.add('hidden');
      emptyEl.classList.add('hidden');
      loadMoreWrap?.classList.add('hidden');
      listEl.innerHTML = '';
      nextCursor = null;
    }

    const p = { ...params };
    if (append && nextCursor) p.cursor = nextCursor;

    try {
      const res = await api(endpoints.history + qs(p));
      const items = res?.data || [];
      nextCursor = res?.next_cursor || null;

      skeletonEl.classList.add('hidden');

      if (!items.length && !append) {
        emptyEl.classList.remove('hidden');
        listEl.classList.add('hidden');
        return;
      }

      const frag = document.createDocumentFragment();

      for (const m of items) {
        const name = m?.user?.name || 'Anon';
        const type = normalizeType(m?.type);
        const deletedBadge = m?.deleted
          ? `<span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[11px]">Dihapus</span>`
          : ``;
        const typeBadge = type
          ? `<span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[11px]">${escapeHtml(type)}</span>`
          : ``;

        const row = document.createElement('div');
        row.className = 'p-4 rounded-xl border bg-white';

        // gunakan renderBody() untuk membuat isi pesan
        const bodyHtml = renderBody(m);

        row.innerHTML = `
          <div class="flex items-start gap-3">
            ${avatarHTML(name, m?.user?.avatar)}
            <div class="flex-1">
              <div class="text-sm font-medium">
                ${escapeHtml(name)}
                <span class="ml-2 text-xs text-gray-500">
                  ${m?.created_at ? new Date(m.created_at).toLocaleString() : ''}
                </span>
                ${deletedBadge}
                ${typeBadge}
              </div>
              ${bodyHtml}
            </div>
          </div>
        `;
        frag.appendChild(row);
      }

      listEl.appendChild(frag);
      listEl.classList.remove('hidden');

      if (nextCursor) loadMoreWrap?.classList.remove('hidden');
      else loadMoreWrap?.classList.add('hidden');
    } catch (e) {
      skeletonEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      emptyEl.textContent = 'Gagal memuat riwayat.';
      console.error('fetchHistory error:', e);
    }
  }

  // Search click
  searchBtn?.addEventListener('click', () => {
    params.q = (searchInput?.value || '').trim();
    fetchHistory({ append: false });
  });

  // Filter submit
  filterForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(filterForm);
    params.start_date      = fd.get('start_date') || '';
    params.end_date        = fd.get('end_date') || '';
    // FIX: selalu kirim '0' atau '1'
    params.include_deleted = fd.get('include_deleted') ? '1' : '0';
    params.type            = fd.get('type') || '';
    fetchHistory({ append: false });
  });

  // Quick chips
  chipBtns.forEach(chip => {
    chip.addEventListener('click', () => {
      // reset UI state (tanpa efek hover — sudah distrip)
      chipBtns.forEach(c => c.classList.remove('bg-blue-600','text-white'));
      chip.classList.add('bg-blue-600','text-white');

      const kind = chip.dataset.chip;
      const today = new Date();
      const d = (n)=> new Date(today.getFullYear(), today.getMonth(), today.getDate() + n);
      // format tanggal lokal (hindari toISOString shift UTC)
      const toYMD = (dt) => {
        const y = dt.getFullYear();
        const m = String(dt.getMonth() + 1).padStart(2, '0');
        const dd = String(dt.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
      };

      // ambil state checkbox "sertakan yang dihapus"
      const inclDeleted = document.querySelector('#history-filter-form input[name="include_deleted"]')?.checked ? '1' : '0';

      if (kind === 'today') {
        params = { q:'', start_date:toYMD(d(0)), end_date:toYMD(d(0)), include_deleted:inclDeleted, only_deleted:'', type:'' };
      } else if (kind === 'week') {
        params = { q:'', start_date:toYMD(d(-6)), end_date:toYMD(d(0)), include_deleted:inclDeleted, only_deleted:'', type:'' };
      } else if (kind === 'month') {
        params = { q:'', start_date:toYMD(new Date(today.getFullYear(), today.getMonth(), 1)), end_date:toYMD(d(0)), include_deleted:inclDeleted, only_deleted:'', type:'' };
      } else if (kind === 'deleted') {
        params = { q:'', start_date:'', end_date:'', include_deleted:inclDeleted, only_deleted:'1', type:'' };
      } else if (kind === 'poll') {
        params = { q:'', start_date:'', end_date:'', include_deleted:inclDeleted, only_deleted:'', type:'poll' };
      } else if (kind === 'attach') {
        params = { q:'', start_date:'', end_date:'', include_deleted:inclDeleted, only_deleted:'', type:'attachment' };
      } else if (kind === 'voice') {
        params = { q:'', start_date:'', end_date:'', include_deleted:inclDeleted, only_deleted:'', type:'voice' };
      }

      fetchHistory({ append: false });
    });
  });

  // Load more
  loadMoreBtn?.addEventListener('click', () => {
    if (!nextCursor) return;
    fetchHistory({ append: true });
  });

  // Lazy-load saat tab Riwayat pertama kali dibuka
  const historyTabBtn = Array.from(document.querySelectorAll('.tab-btn'))
    .find(b => b.dataset.tab === 'tab-history');
  historyTabBtn?.addEventListener('click', () => {
    // strip hover lagi jika ada chip dinamis
    Array.from(document.querySelectorAll('#tab-history .chip-quick')).forEach(stripHoverClasses);
    if (!firstLoaded) { firstLoaded = true; fetchHistory(); }
  });

  // Kalau sudah aktif by default
  const historyPanel = document.getElementById('tab-history');
  if (historyPanel && !historyPanel.classList.contains('hidden')) {
    firstLoaded = true; fetchHistory();
  }
}

// ===== Main app =====
(function () {
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  onReady(() => {
    // ===== Root guard =====
    const ROOT = document.getElementById('forum-admin-root');
    if (!ROOT) return;

    // ===== Safe selectors =====
    const $  = (sel, parent = document) => parent && parent.querySelector ? parent.querySelector(sel) : null;
    const $$ = (sel, parent = document) => parent && parent.querySelectorAll ? Array.from(parent.querySelectorAll(sel)) : [];

    // ===== CSRF & baseUrl =====
    const CSRF = $('meta[name="csrf-token"]')?.content || '';

    // Bisa override lewat: <script data-admin-forum-endpoints='@json(url("/admin/forum"))'></script>
    const baseUrl = (() => {
      const el = $('script[data-admin-forum-endpoints]');
      if (el?.dataset?.adminForumEndpoints) {
        try { return JSON.parse(el.dataset.adminForumEndpoints); } catch (_) {}
        return el.dataset.adminForumEndpoints;
      }
      return window.location.origin + '/admin/forum';
    })();

    const endpoints = {
      toggle:     baseUrl + '/toggle',
      stats:      baseUrl + '/stats',
      banned:     baseUrl + '/banned',
      unban:     (userId) => baseUrl + '/unban/' + userId,
      logs:       baseUrl + '/moderator-logs',
      settings:   baseUrl + '/settings',
      broadcast:  baseUrl + '/broadcast',
      clear:      baseUrl + '/clear',
      exportChat: baseUrl + '/export',
      history:    baseUrl + '/history',
    };

    // ===== Helpers =====
    function toast(msg, ok = true) {
      const el = document.createElement('div');
      el.className = `fixed bottom-5 right-5 z-50 px-3 py-2 rounded-xl shadow-lg text-sm ${ok ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'}`;
      el.textContent = msg;
      document.body.appendChild(el);
      setTimeout(() => el.remove(), 2200);
    }

    // API (versi dengan error detail)
    async function api(url, opts = {}) {
      const res = await fetch(url, {
        headers: {
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        ...opts,
      });

      const text = await res.text();
      let data = null;
      try { data = JSON.parse(text); } catch(_) {}

      if (!res.ok) {
        const msg = (data && (data.message || (data.errors && Object.values(data.errors).flat().join('\n'))))
          || text
          || `HTTP ${res.status}`;
        const err = new Error(msg);
        err.status = res.status;
        err.body = data || text;
        throw err;
      }
      return data ?? {};
    }

    const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };

    window.forumAdmin = { api, endpoints, escapeHtml };

    // ===== Online heartbeat + counter =====
    async function pingHeartbeat() {
      try {
        await fetch('/forum/online-heartbeat', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
      } catch (_) {}
    }

    async function loadOnline() {
      try {
        const res = await fetch('/forum/online-count', {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        const data = await res.json();
        const count = Number(data?.count ?? 0);

        const chip = document.getElementById('online-count');
        if (chip) {
          chip.textContent = `• Online: ${count}`;
          chip.classList.remove('hidden');
        }
        setText('stat-online-hint', `Online: ${count}`);
      } catch (_) {
        const chip = document.getElementById('online-count');
        if (chip) chip.textContent = '• Online: —';
        setText('stat-online-hint', 'Online: —');
      }
    }

    // Run & intervals
    pingHeartbeat();
    loadOnline();
    setInterval(pingHeartbeat, 25 * 1000); // < TTL 60s
    setInterval(loadOnline, 10 * 1000);

    // ===== Tabs =====
    const tabBtns = $$('.tab-btn', ROOT);
    const panelIds = ['tab-overview','tab-history','tab-moderation','tab-settings','tab-export'];
    const panels = panelIds.map(id => document.getElementById(id)).filter(Boolean);

    tabBtns.forEach(btn => btn.addEventListener('click', () => {
      tabBtns.forEach(b => {
        b.setAttribute('aria-selected','false');
        b.classList.remove('bg-blue-100','text-blue-700');
        b.classList.add('text-gray-600','hover:bg-gray-100');
      });
      btn.setAttribute('aria-selected','true');
      btn.classList.add('bg-blue-100','text-blue-700');
      btn.classList.remove('text-gray-600','hover:bg-gray-100');

      panels.forEach(p => p.classList.add('hidden'));
      const dst = document.getElementById(btn.dataset.tab);
      if (dst) dst.classList.remove('hidden');
    }));

  // ===== Toggle forum =====
$('#btn-toggle')?.addEventListener('click', async () => {
  try {
    const result = await api(endpoints.toggle, { method: 'POST' });

    // Terima kedua bentuk: {status:"success", data:{is_open}} atau {status:"ok", is_open}
    const ok = (result?.status === 'success' || result?.status === 'ok');
    if (!ok) throw new Error(result?.message || 'Gagal toggle');

    const isOpen = (result?.data?.is_open ?? result?.is_open ?? false);

    const chip = $('#forum-status-chip');
    const btn  = $('#btn-toggle');
    if (isOpen) {
      chip && (chip.textContent = 'Terbuka',
               chip.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700');
      btn  && (btn.textContent  = 'Tutup Forum');
    } else {
      chip && (chip.textContent = 'Tertutup',
               chip.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700');
      btn  && (btn.textContent  = 'Buka Forum');
    }

    toast(result?.message || 'Status forum diubah');
    loadStats();
    loadStatsOverview();
  } catch (e) {
    toast(String(e.message || e), false);
  }
});


    // ===== Clear chat (modal) =====
    const modal = $('#clear-confirm-modal');
    $('#btn-clear')?.addEventListener('click', () => { modal?.classList.remove('hidden'); });
    $('#clear-cancel')?.addEventListener('click', () => { modal?.classList.add('hidden'); });
    $('#clear-confirm')?.addEventListener('click', async () => {
      try {
        const res = await api(endpoints.clear, { method: 'POST' });
        toast(res.message || 'Chat dibersihkan');
        modal?.classList.add('hidden');
        loadStats();
        loadStatsOverview(); // Update overview
      } catch (e) { toast(String(e.message || e), false); }
    });

    // ===== Settings =====
    $('#settings-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);

      try {
        const res = await api(endpoints.settings, { method: 'POST', body: fd });
        const hint = $('#settings-hint');
        if (hint) { hint.textContent = 'Tersimpan ✓'; setTimeout(() => hint.textContent = '', 1500); }
        toast(res.message || 'Pengaturan disimpan');
        loadStats();
        loadStatsOverview(); // Update overview
      } catch (e) { toast(String(e.message || e), false); }
    });

    // ===== Broadcast (auto reset) =====
    $('#broadcast-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.currentTarget;
      const btn  = form.querySelector('button[type="submit"]');
      const fd   = new FormData(form);

      if (btn) {
        btn.disabled = true;
        btn.dataset._origText = btn.textContent;
        btn.textContent = 'Mengirim…';
      }

      try {
        const res = await api(endpoints.broadcast, { method: 'POST', body: fd });

        if (typeof form.reset === 'function') form.reset();
        const ta = form.querySelector('textarea[name="message"]');
        if (ta) ta.value = '';

        const hint = $('#broadcast-hint');
        if (hint) { hint.textContent = 'Terkirim ✓'; setTimeout(() => hint.textContent = '', 1500); }
        toast(res.message || 'Broadcast terkirim');
      } catch (err) {
        toast(String(err.message || err), false);
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.textContent = btn.dataset._origText || 'Kirim Pengumuman';
        }
      }
    });

    // ===== Export =====
    $('#export-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const params = new URLSearchParams();
      for (const [k, v] of fd.entries()) {
        if (v !== '' && !(k === 'include_deleted' && v === 'on')) params.append(k, v);
      }
      if (fd.get('include_deleted')) params.append('include_deleted', '1');

      try {
        const res = await api(endpoints.exportChat + (params.toString() ? '?' + params.toString() : ''));
        const pre = $('#export-result');
        if (pre) pre.textContent = JSON.stringify(res.data, null, 2);
        toast('Export siap');

        // enable tombol download + pasang handler
        wireDownloadButtons();
      } catch (e) {
        toast(String(e.message || e), false);
      }
    });

    // ===== Stats - Ringkasan =====
    async function loadStats() {
      try {
        const res = await api(endpoints.stats);
        const s = res.data || {};

        const setAnyText = (ids, v) => {
          const val = (v ?? 0);
          ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
          });
        };

        // angka umum
        setText('stat-total-messages', s.total_messages ?? '0');
        setText('stat-messages-today', s.messages_today ?? '0');
        setText('stat-total-users',    s.total_users ?? '0');
        setText('stat-banned-users',   s.banned_users ?? '0');

        // ===== DIPISAH: Poll, Voice, Attachment =====
        setAnyText(['stat-polls','stat-poll-count','stat-active-polls'], s.polls_count ?? s.active_polls ?? 0);
        setAnyText(['stat-voices','stat-voice-count'], s.voice_count ?? s.voices_count ?? 0);
        setAnyText(['stat-attachments','stat-attachment-count'], s.attachments_count ?? s.files_count ?? 0);
      } catch (e) {
        console.warn('loadStats error:', e);
      }
    }

    // ===== Stats Overview (grafik 7 hari, 1 bulan, 1 tahun) =====
    async function loadStatsOverview() {
      try {
        const res = await api(endpoints.stats);
        const data = res.data || {};
        const container = document.getElementById('stats-overview');
        if (!container) return;

        // Buat struktur untuk tiga grafik
        container.innerHTML = `
          <div class="space-y-6">
            <div class="p-4 rounded-xl bg-white shadow-sm border">
              <h3 class="text-lg font-semibold mb-4">Grafik 7 Hari Terakhir</h3>
              <canvas id="chart-7d" width="400" height="200"></canvas>
            </div>
            <div class="p-4 rounded-xl bg-white shadow-sm border">
              <h3 class="text-lg font-semibold mb-4">Grafik 1 Bulan Terakhir (Per Minggu)</h3>
              <canvas id="chart-month" width="400" height="200"></canvas>
            </div>
            <div class="p-4 rounded-xl bg-white shadow-sm border">
              <h3 class="text-lg font-semibold mb-4">Grafik 1 Tahun Terakhir</h3>
              <canvas id="chart-365d" width="400" height="200"></canvas>
            </div>
          </div>
        `;

        // Load grafik setelah DOM diupdate
        setTimeout(() => {
          loadChart7d(data);
          loadChartMonth(data);
          loadChart365d(data);
        }, 0);
      } catch (err) {
        console.error('loadStatsOverview error:', err);
        toast('Gagal memuat stats overview', false);
      }
    }

   
   // ===== Chart 7 Hari ===== (UPDATED: + Artikel & Komentar)
let chart7dInstance = null;
function loadChart7d(d) {
  const labels   = d.last7_labels || [];
  const messages = d.last7_series || [];
  const deleted  = d.last7_deleted_series || [];

  // Tambahan seri
  const voices   = d.last7_voice_series   || [];
  const files    = d.last7_attach_series  || [];
  const polls    = d.last7_poll_series    || [];

  // NEW: Artikel & Komentar (fallback beberapa nama field)
  const articles = d.last7_articles_series ?? d.last7_posts_series ?? d.last7_blog_series ?? [];
  const comments = d.last7_comments_series ?? d.last7_comment_series ?? [];

  const canvas = document.getElementById('chart-7d');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (chart7dInstance) chart7dInstance.destroy();
  chart7dInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Pesan',     data: messages, borderColor: 'rgb(59,130,246)',  tension: 0.4 },
        { label: 'Dihapus',   data: deleted,  borderColor: 'rgb(225,29,72)',   tension: 0.4 },
        { label: 'VN',        data: voices,   borderColor: 'rgb(34,197,94)',   tension: 0.4 },
        { label: 'Lampiran',  data: files,    borderColor: 'rgb(234,179,8)',   tension: 0.4 },
        { label: 'Poll',      data: polls,    borderColor: 'rgb(99,102,241)',  tension: 0.4 },
        { label: 'Artikel',   data: articles, borderColor: 'rgb(20,184,166)',  tension: 0.4 }, // teal-500
        { label: 'Komentar',  data: comments, borderColor: 'rgb(100,116,139)', tension: 0.4 }, // slate-500
      ],
    },
    options: {
      plugins: { legend: { display: true } },
      scales: { x: { display: true }, y: { display: true, beginAtZero: true } },
    },
  });
}

   // ===== Chart 30 Hari (agregasi per minggu) ===== (UPDATED: + Artikel & Komentar)
let chartMonthInstance = null;
function loadChartMonth(d) {
  const labelsDaily   = d.last30_labels || [];
  const msgDaily      = d.last30_series || [];
  const delDaily      = d.last30_deleted_series || [];

  const voiceDaily    = d.last30_voice_series  || [];
  const fileDaily     = d.last30_attach_series || [];
  const pollDaily     = d.last30_poll_series   || [];

  // NEW: Artikel & Komentar harian (fallback)
  const artDaily      = d.last30_articles_series ?? d.last30_posts_series ?? d.last30_blog_series ?? [];
  const cmtDaily      = d.last30_comments_series ?? d.last30_comment_series ?? [];

  // Agregasi ke 4 minggu
  const weeksLabels   = ['Minggu ke-1', 'Minggu ke-2', 'Minggu ke-3', 'Minggu ke-4'];
  const wMsg   = [0,0,0,0], wDel  = [0,0,0,0], wVoice = [0,0,0,0], wFile = [0,0,0,0], wPoll = [0,0,0,0];
  const wArt   = [0,0,0,0], wCmt  = [0,0,0,0];

  const numDays = Math.min(labelsDaily.length, 30);
  for (let i = 0; i < numDays; i++) {
    const w = Math.min(Math.floor(i / 7), 3);
    wMsg[w]   += Number(msgDaily[i]   || 0);
    wDel[w]   += Number(delDaily[i]   || 0);
    wVoice[w] += Number(voiceDaily[i] || 0);
    wFile[w]  += Number(fileDaily[i]  || 0);
    wPoll[w]  += Number(pollDaily[i]  || 0);
    wArt[w]   += Number(artDaily[i]   || 0); // NEW
    wCmt[w]   += Number(cmtDaily[i]   || 0); // NEW
  }

  const canvas = document.getElementById('chart-month');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (chartMonthInstance) chartMonthInstance.destroy();
  chartMonthInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: weeksLabels,
      datasets: [
        { label: 'Pesan (per minggu)',    data: wMsg,  borderColor: 'rgb(59,130,246)',  tension: 0.4 },
        { label: 'Dihapus (per minggu)',  data: wDel,  borderColor: 'rgb(225,29,72)',   tension: 0.4 },
        { label: 'VN (per minggu)',       data: wVoice,borderColor: 'rgb(34,197,94)',   tension: 0.4 },
        { label: 'Lampiran (per minggu)', data: wFile, borderColor: 'rgb(234,179,8)',   tension: 0.4 },
        { label: 'Poll (per minggu)',     data: wPoll, borderColor: 'rgb(99,102,241)',  tension: 0.4 },
        { label: 'Artikel (per minggu)',  data: wArt,  borderColor: 'rgb(20,184,166)',  tension: 0.4 }, // NEW
        { label: 'Komentar (per minggu)', data: wCmt,  borderColor: 'rgb(100,116,139)', tension: 0.4 }, // NEW
      ],
    },
    options: {
      plugins: { legend: { display: true } },
      scales: { x: { display: true }, y: { display: true, beginAtZero: true } },
    },
  });
}


   // ===== Chart 12 Bulan ===== (UPDATED: + Artikel & Komentar)
let chart365dInstance = null;
function loadChart365d(d) {
  const labels   = d.last365_labels  || [];
  const messages = d.last365_series  || [];
  const deleted  = d.last365_deleted_series || [];

  const voices   = d.last365_voice_series  || [];
  const files    = d.last365_attach_series || [];
  const polls    = d.last365_poll_series   || [];

  // NEW: Artikel & Komentar (fallback)
  const articles = d.last365_articles_series ?? d.last365_posts_series ?? d.last365_blog_series ?? [];
  const comments = d.last365_comments_series ?? d.last365_comment_series ?? [];

  const canvas = document.getElementById('chart-365d');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (chart365dInstance) chart365dInstance.destroy();
  chart365dInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Pesan',     data: messages, borderColor: 'rgb(59,130,246)', tension: 0.4 },
        { label: 'Dihapus',   data: deleted,  borderColor: 'rgb(225,29,72)',  tension: 0.4 },
        { label: 'VN',        data: voices,   borderColor: 'rgb(34,197,94)',  tension: 0.4 },
        { label: 'Lampiran',  data: files,    borderColor: 'rgb(234,179,8)',  tension: 0.4 },
        { label: 'Poll',      data: polls,    borderColor: 'rgb(99,102,241)', tension: 0.4 },
        { label: 'Artikel',   data: articles, borderColor: 'rgb(20,184,166)', tension: 0.4 }, // NEW
        { label: 'Komentar',  data: comments, borderColor: 'rgb(100,116,139)',tension: 0.4 }, // NEW
      ],
    },
    options: {
      plugins: { legend: { display: true } },
      scales: { x: { display: true }, y: { display: true, beginAtZero: true } },
    },
  });
}


    // ===== Top Users =====
    async function loadTopUsers() {
      const wrapSkeleton = document.getElementById('top-users-skeleton');
      const table = document.getElementById('top-users-table');
      const tbody = table?.querySelector('tbody');
      const empty = document.getElementById('top-users-empty');

      if (!table || !tbody) return;

      wrapSkeleton?.classList.remove('hidden');
      table?.classList.add('hidden');
      empty?.classList.add('hidden');

      try {
        const res = await api(baseUrl + '/top-users');
        const users = res.data || [];
        if (!users.length) {
          empty.classList.remove('hidden');
          return;
        }
        tbody.innerHTML = users.map(u => `
          <tr class="border-t">
            <td class="py-1">${escapeHtml(u.name)}</td>
            <td class="py-1 text-center">${escapeHtml(String(u.count))}</td>
            <td class="py-1 text-center">${u.banned ? '<span class="text-red-500">Banned</span>' : '-'}</td>
          </tr>
        `).join('');
        table.classList.remove('hidden');
      } catch (err) {
        empty.textContent = 'Gagal memuat';
        empty.classList.remove('hidden');
        console.error('loadTopUsers error:', err);
      } finally {
        wrapSkeleton?.classList.add('hidden');
      }
    }

    // ===== Latest Polls =====
    async function loadLatestPolls() {
      const sk = document.getElementById('latest-polls-skeleton');
      const ul = document.getElementById('latest-polls-list');
      const empty = document.getElementById('latest-polls-empty');

      if (!ul) return;

      sk?.classList.remove('hidden');
      ul?.classList.add('hidden');
      empty?.classList.add('hidden');

      try {
        const res = await api(baseUrl + '/latest-polls');
        const polls = res.data || [];
        if (!polls.length) {
          empty.classList.remove('hidden');
          return;
        }
        ul.innerHTML = polls.map(p => `
          <li class="p-3 rounded-lg bg-white shadow flex justify-between">
            <div>
              <div class="font-medium">${escapeHtml(p.question || 'Pertanyaan')}</div>
              <div class="text-xs text-gray-500">${p.options_count} opsi • ${p.votes_count} vote</div>
            </div>
            <div class="text-xs text-gray-400">${p.created_at ? new Date(p.created_at).toLocaleDateString() : ''}</div>
          </li>
        `).join('');
        ul.classList.remove('hidden');
      } catch (err) {
        empty.textContent = 'Gagal memuat';
        empty.classList.remove('hidden');
        console.error('loadLatestPolls error:', err);
      } finally {
        sk?.classList.add('hidden');
      }
    }

    // ===== Banned list =====
    async function loadBanned() {
      const tbody = $('#banned-list'); if (!tbody) return;
      tbody.innerHTML = `<tr><td colspan="5" class="p-6"><div class='h-6 bg-gray-200 rounded animate-pulse'></div></td></tr>`;
      try {
        const res = await api(endpoints.banned);
        const items = res.data || [];
        $('#banned-empty')?.classList.toggle('hidden', items.length !== 0);
        if (!items.length) { tbody.innerHTML = ''; return; }
        tbody.innerHTML = items.map((b) => {
          const exp = b.expires_at ? new Date(b.expires_at).toLocaleDateString() : '-';
          const type = b.ban_type === 'permanent'
            ? '<span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs">permanent</span>'
            : `<span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs">${escapeHtml(b.ban_type || '-')}</span>`;
          return `<tr class="border-t">
            <td class="p-3">
              <div class="font-medium">${escapeHtml(b.user?.name || '—')} <span class='text-xs text-gray-400'>#${escapeHtml(b.user?.id)}</span></div>
              <div class="text-xs text-gray-500">${escapeHtml(b.user?.email || '')}</div>
            </td>
            <td class="p-3 text-center">${type}</td>
            <td class="p-3">${escapeHtml(b.reason || '-')}</td>
            <td class="p-3 text-center">${exp}</td>
            <td class="p-3 text-right">
              <button data-user="${escapeHtml(b.user?.id)}" class="btn-unban px-2.5 py-1.5 rounded-xl border hover:bg-gray-50">Unban</button>
            </td>
          </tr>`;
        }).join('');

        $$('.btn-unban', tbody).forEach(btn => {
          btn.addEventListener('click', async () => {
            const uid = btn.getAttribute('data-user');
            if (!uid) return;
            try { await api(endpoints.unban(uid), { method: 'POST' }); toast('User di-unban'); loadBanned(); loadStats(); loadStatsOverview(); }
            catch (e) { toast(String(e.message || e), false); }
          });
        });

        const search = ($('#ban-search')?.value || '').toLowerCase();
        if (search) {
          $$('.border-t', tbody).forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(search) ? '' : 'none';
          });
        }
      } catch (e) {
        tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-gray-500">Gagal memuat</td></tr>`;
      }
    }
    $('#ban-search')?.addEventListener('input', () => {
      // Debounce sederhana
      clearTimeout(window.banSearchTimeout);
      window.banSearchTimeout = setTimeout(loadBanned, 300);
    });
    // ===== Ban / Kick buttons =====
$$('.btn-ban').forEach(btn => {
  btn.addEventListener('click', async () => {
    const userId = btn.getAttribute('data-user');
    const type   = btn.getAttribute('data-type'); // kick, ban1h, ban24h, ban_permanent
    if (!userId || !type) return;

    try {
      await api(`/forum/ban/${userId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type })
      });
      toast('User berhasil di-ban');
      loadBanned();
      loadStats();
      loadStatsOverview();
    } catch (e) {
      toast(String(e.message || e), false);
    }
  });
});


    // ===== Logs =====
    async function loadLogs() {
      const ul = $('#logs-list'); const sk = $('#logs-skeleton'); const empty = $('#logs-empty');
      if (!ul) return;
      sk?.classList.remove('hidden'); ul.classList.add('hidden'); empty?.classList.add('hidden');
      try {
        const res = await api(endpoints.logs);
        const logs = res.data || [];
        if (!logs.length) { empty?.classList.remove('hidden'); sk?.classList.add('hidden'); return; }
        const q = ($('#logs-search')?.value || '').toLowerCase();
        const filtered = logs.filter(l => JSON.stringify(l).toLowerCase().includes(q));
        ul.innerHTML = filtered.map(l => `
          <li class="p-3 rounded-xl border">
            <div class="flex items-center justify-between">
              <div class="font-medium">${escapeHtml(String(l.type || '').replace('_',' '))}</div>
              <div class="text-xs text-gray-500">${l.created_at ? new Date(l.created_at).toLocaleString() : ''}</div>
            </div>
            <div class="text-sm text-gray-700 mt-1 space-y-0.5">
              ${l.message    ? `<div><span class='text-gray-500'>Message:</span> ${escapeHtml(l.message)}</div>` : ''}
              ${l.user       ? `<div><span class='text-gray-500'>User:</span> ${escapeHtml(l.user)}</div>` : ''}
              ${l.deleted_by ? `<div><span class='text-gray-500'>Deleted by:</span> ${escapeHtml(l.deleted_by)}</div>` : ''}
              ${l.banned_by  ? `<div><span class='text-gray-500'>Banned by:</span> ${escapeHtml(l.banned_by)}</div>` : ''}
              ${l.ban_type   ? `<div><span class='text-gray-500'>Type:</span> ${escapeHtml(l.ban_type)}</div>` : ''}
              ${l.reason     ? `<div><span class='text-gray-500'>Reason:</span> ${escapeHtml(l.reason)}</div>` : ''}
            </div>
          </li>
        `).join('');
        sk?.classList.add('hidden'); ul.classList.remove('hidden');
      } catch (e) {
        sk?.classList.add('hidden'); ul.innerHTML = '<li class="text-gray-500 text-center py-6">Gagal memuat</li>';
      }
    }
    $('#logs-search')?.addEventListener('input', () => {
      // Debounce sederhana
      clearTimeout(window.logsSearchTimeout);
      window.logsSearchTimeout = setTimeout(loadLogs, 300);
    });

    // ===== Event listeners untuk refresh buttons =====
    document.getElementById('refresh-stats-overview')?.addEventListener('click', loadStatsOverview);
    document.getElementById('refresh-top-users')?.addEventListener('click', loadTopUsers);
    document.getElementById('refresh-latest-polls')?.addEventListener('click', loadLatestPolls);

    // ===== Initial load =====
    loadStats();
    loadBanned();
    loadLogs();
    loadStatsOverview();
    loadTopUsers();
    loadLatestPolls();

    // ===== Realtime (opsional) =====
    try {
      if (window.Echo) {
        const ch = window.Echo.private('forum.global');
        ch
          .listen('.forum.message.created',  () => { loadStats(); loadStatsOverview(); })
          .listen('.forum.message.deleted',  () => { loadStats(); loadStatsOverview(); })
          .listen('.forum.toggled',          () => { loadStats(); loadStatsOverview(); })
         .listen('.forum.settings.updated', (e) => {
  // 🔔 Tampilkan notifikasi singkat
  toast('Pengaturan forum diperbarui oleh admin lain');

  // 🔄 Refresh statistik agar data terbaru tampil
  loadStats();
  loadStatsOverview();

  // 🧭 Jika tab "Settings" sedang terbuka, reload form secara halus
  const settingsTab = document.querySelector('[data-tab="tab-settings"]');
  const settingsPanel = document.getElementById('tab-settings');
  const isSettingsVisible = settingsPanel && !settingsPanel.classList.contains('hidden');

  if (isSettingsVisible) {
    // Jika tab settings sedang aktif, reload halaman agar semua nilai form update
    setTimeout(() => window.location.reload(), 1200);
  }
})
          .listen('.forum.cleared',          () => { loadStats(); loadStatsOverview(); })
          .listen('.forum.poll.created',     () => { loadStats(); loadStatsOverview(); loadLatestPolls(); })
          .listen('.forum.poll.deleted',     () => { loadStats(); loadStatsOverview(); loadLatestPolls(); })
          .listen('.forum.poll.voted',       () => { loadStats(); loadStatsOverview(); })
          .listen('.forum.ban.applied', () => { loadBanned(); loadStats(); loadStatsOverview(); })
          .listen('.forum.ban.revoked', () => { loadBanned(); loadStats(); loadStatsOverview(); });
      }
    } catch (_) {}

    // ===== Init History =====
    initHistory();
    
  });
})();
