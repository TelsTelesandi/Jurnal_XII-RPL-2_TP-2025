<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - PT Rekan Kinerja Abadi</title>

    <link rel="icon" type="image/x-icon" href="/image/image.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.tailwindcss.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.tailwindcss.min.js"></script>

    <style>
        /* ─── Sidebar scroll isolation ─── */
        #sidebar {
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
        }

        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        /* ─── DataTables global overrides ─── */
        /* Top controls (length + search) */
        div.dt-layout-row {
            margin-bottom: 0 !important;
        }

        div.dt-layout-row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0.5rem 0 0.75rem;
        }

        div.dt-length label {
            font-size: 13px;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        div.dt-length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 28px 4px 8px;
            font-size: 13px;
            background: #fff;
            color: #111827;
        }

        div.dt-search label {
            font-size: 13px;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        div.dt-search input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 13px;
            color: #111827;
            min-width: 200px;
            outline: none;
            transition: border-color 0.15s;
        }

        div.dt-search input:focus {
            border-color: #3b82f6;
        }

        /* Pagination */
        div.dt-paging {
            margin-top: 0.5rem;
        }

        div.dt-info {
            font-size: 13px;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        nav.dt-paging-button-container {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        button.dt-paging-button {
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
            background: #fff;
            cursor: pointer;
            transition: all 0.15s;
        }

        button.dt-paging-button:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        button.dt-paging-button.current {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
            font-weight: 700;
        }

        button.dt-paging-button.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Column header – sort icons */
        table.dataTable thead th {
            position: relative;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            padding: 10px 14px;
            border-bottom: 2px solid #e5e7eb;
            background: #f9fafb;
            cursor: pointer;
            user-select: none;
        }

        table.dataTable thead th.dt-ordering-asc::after {
            content: " ↑";
            opacity: 0.6;
            font-size: 11px;
        }

        table.dataTable thead th.dt-ordering-desc::after {
            content: " ↓";
            opacity: 0.6;
            font-size: 11px;
        }

        table.dataTable thead th:not(.dt-ordering-asc):not(.dt-ordering-desc):not(.dt-no-sort)::after {
            content: " ↕";
            opacity: 0.3;
            font-size: 11px;
        }

        /* Column filter row */
        table.dataTable thead tr.dt-filter-row th {
            padding: 6px 8px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            cursor: default;
        }

        table.dataTable thead tr.dt-filter-row th input,
        table.dataTable thead tr.dt-filter-row th select {
            width: 100%;
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
            color: #374151;
            outline: none;
            transition: border-color 0.15s;
        }

        table.dataTable thead tr.dt-filter-row th input:focus,
        table.dataTable thead tr.dt-filter-row th select:focus {
            border-color: #3b82f6;
            background: #fff;
        }

        /* Body rows */
        table.dataTable tbody td {
            font-size: 13px;
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        table.dataTable tbody tr:hover td {
            background: #f8fafc;
        }

        /* Responsive expand/collapse arrow — let DataTables handle natively */
        table.dataTable tbody td.dtr-control {
            cursor: pointer;
            padding-left: 16px !important;
            width: 30px;
            text-align: center;
        }

        /* Responsive child row */
        table.dataTable tbody tr.child td {
            background: #f0f7ff;
            padding: 12px 16px;
        }

        table.dataTable tbody tr.child ul.dtr-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 0;
            list-style: none;
        }

        table.dataTable tbody tr.child ul.dtr-details li {
            display: flex;
            flex-direction: column;
            font-size: 12px;
        }

        table.dataTable tbody tr.child ul.dtr-details li span.dtr-title {
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }

        table.dataTable tbody tr.child ul.dtr-details li span.dtr-data {
            color: #111827;
        }

        /* Bottom tfoot repeat */
        table.dataTable tfoot th {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            padding: 10px 14px;
            border-top: 2px solid #e5e7eb;
            background: #f9fafb;
        }
    </style>

    @stack('styles')

    {{-- Optional: kirim base URL API admin forum ke JS hanya di halaman forum --}}
    @if (Route::is('admin.forum.*'))
        <script data-admin-forum-endpoints type="application/json">
              "{{ url('/admin/forum') }}"
            </script>
    @endif
</head>

<body class="h-full">
    <div class="min-h-full flex" style="overflow:hidden">

        <!-- Backdrop (mobile only) -->
        <div id="sidebar-backdrop"
            class="fixed inset-0 z-40 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
            aria-hidden="true"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-3/4 sm:w-80 lg:w-72
           -translate-x-full lg:translate-x-0
           transition-transform duration-300 ease-in-out
           bg-gradient-to-b from-blue-700 to-blue-600 shadow-xl ring-1 ring-black/5
           h-screen overflow-y-auto overscroll-contain
           lg:fixed lg:inset-y-0" aria-label="Sidebar" aria-hidden="true">
            <div class="flex h-full flex-col">
                <!-- Header / Brand + Close (mobile) -->
                <div class="flex items-center justify-between px-4 h-14 lg:h-16
                            border-b border-blue-600/50 bg-blue-700 flex-shrink-0">
                    <h1 class="flex items-center gap-2.5 font-bold tracking-tight text-white">
                        <span class="flex-shrink-0 h-9 w-9 rounded-full bg-white flex items-center justify-center shadow-sm">
                            <img class="w-7 h-7 object-contain" src="{{ asset('image/image.png') }}" alt="logo">
                        </span>
                        <span class="text-sm">Admin PT RKA</span>
                    </h1>
                    <button id="sidebar-close"
                        class="lg:hidden flex items-center justify-center rounded-lg w-8 h-8
                               text-white/60 hover:text-white hover:bg-white/10 transition"
                        aria-label="Tutup sidebar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>


                <!-- Scroll area -->
                <div class="flex-1 overflow-y-auto px-4 py-3 pb-24 lg:py-4 lg:pb-4">
                    <nav>
                        <ul class="space-y-4 lg:space-y-6">
                            <!-- Dashboard -->
                            <li>
                                <a href="{{ route('admin.dashboard') }}"
                                    class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                        {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                    <i class="fas fa-home w-5 shrink-0"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            <!-- Blog Management -->
                            <li>
                                <div
                                    class="text-[11px] font-semibold uppercase tracking-wider text-blue-200/80 select-none">
                                    Blog Management</div>
                                <div class="mt-2 space-y-1.5">
                                    <a href="{{ route('admin.blog.index') }}"
                                        class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                          {{ request()->routeIs('admin.blog.*') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                        <i class="fas fa-newspaper w-5"></i> <span>Blog</span>
                                    </a>
                                    <a href="{{ route('admin.comments.index') }}"
                                        class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                          {{ request()->routeIs('admin.comments.*') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                        <i class="fas fa-comments w-5"></i> <span>Komentar</span>
                                        @isset($pendingCommentsCount)
                                            @if ($pendingCommentsCount > 0)
                                                <span
                                                    class="ml-auto inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 leading-none">
                                                    {{ $pendingCommentsCount }}
                                                </span>
                                            @endif
                                        @endisset
                                    </a>
                                </div>
                            </li>

                            <!-- Forum Management -->
                            <li>
                                <div
                                    class="text-[11px] font-semibold uppercase tracking-wider text-blue-200/80 select-none">
                                    Forum Management</div>
                                <div class="mt-2 space-y-1.5">
                                    <a href="{{ route('admin.forum.index') }}"
                                        class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                          {{ request()->routeIs('admin.forum.*') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                        <i class="fas fa-users w-5"></i> <span>Forum</span>
                                    </a>
                                </div>
                            </li>

                            <!-- User Management -->
                            <li>
                                <div
                                    class="text-[11px] font-semibold uppercase tracking-wider text-blue-200/80 select-none">
                                    User Management</div>
                                <a href="{{ route('admin.users.index') }}"
                                    class="group mt-2 flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                        {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                    <i class="fas fa-user-friends w-5"></i> <span>Users</span>
                                </a>
                            </li>

                            <!-- System -->
                            <li>
                                <div
                                    class="text-[11px] font-semibold uppercase tracking-wider text-blue-200/80 select-none">
                                    System</div>
                                <div class="mt-2 space-y-1.5">
                                    <a href="{{ route('admin.settings.index') }}"
                                        class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                          {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                        <i class="fas fa-cog w-5"></i> <span>Pengaturan</span>
                                    </a>
                                    <a href="{{ url('/') }}" target="_blank"
                                        class="group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold text-blue-100 hover:bg-white/10 hover:text-white">
                                        <i class="fas fa-external-link-alt w-5"></i> <span>Lihat Website</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- User & Logout -->
                <div class="mt-auto border-t border-white/10 px-4 py-4">
                    @php
                        $user = Auth::user();
                        $name = $user?->name ?? '';
                        $inisial = collect(explode(' ', trim($name)))
                            ->filter()
                            ->map(fn($kata) => strtoupper(substr($kata, 0, 1)))
                            ->join('');
                        if ($inisial === '') {
                            $inisial = 'U';
                        }
                    @endphp

                    <div class="flex items-center gap-x-3 rounded-lg px-2 py-2 text-sm font-semibold text-white">
                        {{-- Avatar Inisial --}}
                        <div
                            class="h-9 w-9 rounded-full bg-blue-700 border border-white/60 flex items-center justify-center text-white font-bold">
                            {{ $inisial }}
                        </div>

                        {{-- Nama --}}
                        <div class="min-w-0">
                            <div class="truncate">{{ $name }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold
                         text-blue-100 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30">
                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <!-- The margin-left equals the sidebar width (lg:w-72 = 18rem) so the scrollbar never overlaps -->
        <div class="flex-1 min-w-0 lg:ml-72" style="overflow-y:auto; height:100vh;">
            <!-- Top Navbar -->
            <div
                class="sticky top-0 z-30 flex h-16 items-center gap-x-4 border-b bg-white px-4 shadow-sm sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button type="button" id="sidebar-open" class="-m-2.5 p-2.5 text-gray-700 lg:hidden">
                    <span class="sr-only">Buka sidebar</span>
                    <i class="fas fa-bars h-6 w-6"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            </div>

            <!-- Page Content -->
            <main class="py-6">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- JS sidebar -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openBtn = document.getElementById('sidebar-open');
        const closeBtn = document.getElementById('sidebar-close');
        const mq = window.matchMedia('(min-width: 1024px)'); // lg

        function enableTransitions() {
            sidebar.classList.remove('transition-none');
            backdrop.classList.remove('transition-none');
        }

        function disableTransitions() {
            sidebar.classList.add('transition-none');
            backdrop.classList.add('transition-none');
        }

        function setState(state) {
            const isOpen = state === 'open';
            sidebar.dataset.state = state;

            if (isOpen) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.setAttribute('aria-hidden', 'false');
                sidebar.removeAttribute('inert');
                backdrop.classList.remove('pointer-events-none', 'opacity-0');
                backdrop.classList.add('opacity-100');
                document.documentElement.classList.add('overflow-hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.setAttribute('aria-hidden', 'true');
                sidebar.setAttribute('inert', '');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100');
                document.documentElement.classList.remove('overflow-hidden');
            }
        }

        function openSidebar() {
            setState('open');
        }

        function closeSidebar() {
            setState('closed');
        }

        // Event listeners
        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        backdrop?.addEventListener('click', closeSidebar);
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });

        // Sync state on breakpoint change
        function syncState(e) {
            disableTransitions();
            if (e.matches) {
                // Desktop: sidebar always visible, backdrop off
                sidebar.classList.remove('-translate-x-full');
                sidebar.setAttribute('aria-hidden', 'false');
                sidebar.removeAttribute('inert');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.documentElement.classList.remove('overflow-hidden');
            } else {
                // Mobile: start closed
                setState('closed');
            }
            // Re-enable transitions after initial render
            requestAnimationFrame(() => requestAnimationFrame(enableTransitions));
        }

        // Initialize state on load
        document.addEventListener('DOMContentLoaded', () => {
            disableTransitions();
            syncState(mq);
            requestAnimationFrame(() => requestAnimationFrame(enableTransitions));
        });

        mq.addEventListener('change', syncState);
    </script>

    @stack('scripts')
</body>

</html>