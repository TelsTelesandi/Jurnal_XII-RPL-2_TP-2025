                <!-- Mobile Overlay -->
                <div id="mobile-overlay"
                    class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-all duration-300 ease-in-out">
                </div>

                <!-- Navbar -->
                <nav
                    class="bg-white shadow-xl fixed top-0 left-0 w-full z-50 transition-all duration-300 border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">

                            <!-- Logo & Brand -->
                            <div class="flex items-center space-x-3">
                                <!-- Logo & Brand -->
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <img src="{{ asset('image/image.png') }}" alt="Logo" class="h-10 w-auto">
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 tracking-wide whitespace-nowrap">
                                        Rekan Kinerja Abadi
                                    </span>
                                </div>

                            </div>

                            <!-- Fixed Desktop Navigation -->
                            <div class="hidden lg:flex items-center space-x-2">
                              @php
    $menuItems = [
        'Beranda' => 'beranda',
        'Tentang Kami' => 'tentang-kami',
        'Layanan' => 'layanan',
        'Mitra Kerja' => 'mitra-kerja',
        'Blog' => 'blog',
        'Hubungi Kami' => 'hubungi-kami',
        'Bantuan' => 'bantuan-page', // marker, linknya pakai route()
    ];

    $isHomepage = request()->is('/') || request()->is('');
@endphp

@foreach ($menuItems as $menu => $section)
  @php $isHelp = ($menu === 'Bantuan'); @endphp
  <div class="nav-container relative">
    @if ($isHelp)
      <!-- Selalu ke halaman bantuan -->
      <a href="{{ route('bantuan') }}"
         class="nav-link relative block px-4 py-3 text-gray-800 font-medium transition-all duration-300 hover:text-blue-600 group rounded-lg overflow-hidden">
    @else
      @if ($isHomepage)
        <!-- On homepage: smooth scroll -->
        <a href="#{{ $section }}"
           class="nav-link relative block px-4 py-3 text-gray-800 font-medium transition-all duration-300 hover:text-blue-600 group rounded-lg overflow-hidden">
      @else
        <!-- Not on homepage: redirect ke homepage + section -->
        <a href="{{ url('/') }}#{{ $section }}"
           class="nav-link relative block px-4 py-3 text-gray-800 font-medium transition-all duration-300 hover:text-blue-600 group rounded-lg overflow-hidden">
      @endif
    @endif

      <!-- Hover BG -->
      <div class="absolute inset-0 bg-blue-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg"></div>

      <!-- Text -->
      <span class="relative z-10 whitespace-nowrap {{ ($isHelp && request()->routeIs('bantuan')) ? 'text-blue-600' : '' }}">
        {{ $menu }}
      </span>

      <!-- Hover underline -->
      <div class="hover-underline absolute bottom-0 left-4 right-4 h-0.5 bg-blue-400 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rounded-full opacity-0 group-hover:opacity-100"></div>
      </a>

      <!-- Active indicator -->
      <div class="nav-active-indicator absolute bottom-0 left-4 right-4 h-0.5 bg-blue-600 {{ ($isHelp && request()->routeIs('bantuan')) ? 'scale-x-100' : 'scale-x-0' }} transition-transform duration-300 origin-left rounded-full"></div>
  </div>
@endforeach


                                <!-- Desktop Login/Logout -->
                                <div class="ml-6 pl-6 border-l border-gray-300 h-16 flex items-center">
                                    <!-- <= h-16 + center -->
                                    @auth
                                        <div class="relative group">
                                            <button type="button" class="flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg p-2 hover:bg-gray-100 transition">
                                                <img class="h-8 w-8 rounded-full border border-gray-200 object-cover" 
                                                     src="{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                     alt="{{ auth()->user()->name }}">
                                                <span class="text-sm font-medium text-gray-700 hidden sm:block truncate max-w-[120px] xl:max-w-[200px]">{{ auth()->user()->name }}</span>
                                                <svg class="w-4 h-4 text-gray-500 ml-1 flex-shrink-0 transform group-hover:-rotate-180 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Dropdown menu -->
                                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 ease-in-out z-50 transform origin-top-right scale-95 group-hover:scale-100">
                                                <div class="py-2">
                                                    <div class="px-4 py-3 border-b border-gray-100">
                                                        <p class="text-sm leading-5">Masuk sebagai</p>
                                                        <p class="text-sm font-medium leading-5 text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                                    </div>
                                                    
                                                    @if(auth()->user()->role_id == 1)
                                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700 text-sm w-full text-left transition flex items-center">
                                                        <i class="fas fa-solar-panel w-5 text-gray-400"></i> Admin Panel
                                                    </a>
                                                    @endif
                                                    
                                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700 text-sm w-full text-left transition flex items-center">
                                                        <i class="fas fa-user-circle w-5 text-gray-400"></i> Pengaturan Profil
                                                    </a>
                                                    
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <button type="submit" class="block px-4 py-2 hover:bg-red-50 text-red-600 text-sm w-full text-left transition flex items-center">
                                                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('login') }}"
                                            class="inline-flex h-12 items-center px-6
                  bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                  text-white font-semibold leading-none rounded-xl shadow-lg transition-all duration-300
                  translate-y-[2px]"><!-- <= juga diturunkan 2px -->
                                            <svg class="w-4 h-4 mr-2 align-middle" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1" />
                                            </svg>
                                            Login
                                        </a>
                                    @endauth
                                </div>


                            </div>


                            <!-- Mobile Menu Button -->
                            <div class="lg:hidden">
                                <button id="mobile-menu-toggle" type="button"
                                    class="relative p-2 text-gray-800 hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg transition-all duration-300 transform hover:scale-105"
                                    aria-expanded="false" aria-label="Toggle navigation menu">

                                    <!-- Burger Icon -->
                                    <svg id="burger-icon" class="w-6 h-6 block transition-all duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>

                                    <!-- Close Icon -->
                                    <svg id="close-icon" class="w-6 h-6 hidden transition-all duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Mobile Menu -->
                <div id="mobile-menu"
                    class="fixed top-0 right-0 h-full w-80 max-w-sm bg-white shadow-2xl z-50 transform translate-x-full opacity-0 transition-all duration-500 ease-in-out lg:hidden">

                    <!-- Mobile Menu Header -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('image/image.png') }}" alt="Logo" class="h-8 w-auto">
                            <span class="font-bold text-gray-800">RKA</span>
                        </div>
                        <button id="close-mobile-menu" type="button"
                            class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200 transform hover:scale-110"
                            aria-label="Close menu">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Menu Links -->
                    <div class="flex flex-col py-6">
  @foreach ($menuItems as $menu => $section)
    @php
      // Bantuan selalu route ke halaman bantuan; lainnya anchor
      $href = ($menu === 'Bantuan')
        ? route('bantuan')
        : ($isHomepage ? "#$section" : url('/') . "#$section");
    @endphp
    <div class="nav-container relative">
      <a href="{{ $href }}"
         class="nav-link relative flex items-center px-6 py-4 text-gray-800 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-300 group">

        <!-- bullet -->
        <div class="w-2 h-2 bg-gray-400 rounded-full mr-4 transition-all duration-300 group-hover:bg-blue-500 group-hover:scale-125"></div>

        <!-- text -->
        <span class="flex-1 {{ ($menu==='Bantuan' && request()->routeIs('bantuan')) ? 'text-blue-600' : '' }}">
          {{ $menu }}
        </span>

        <!-- arrow -->
        <svg class="w-4 h-4 opacity-0 transform translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>

      <!-- Active indicator -->
      <div class="mobile-nav-active-indicator absolute left-0 top-0 bottom-0 w-1 bg-blue-600 {{ ($menu==='Bantuan' && request()->routeIs('bantuan')) ? 'scale-y-100' : 'scale-y-0' }} transition-transform duration-300 origin-center rounded-r-full"></div>
    </div>
  @endforeach   

                        <!-- Mobile Login/Logout Button -->
                        <div class="px-6 pt-6 mt-6 border-t border-gray-100">
                            @auth
                                <div class="mb-4 flex items-center space-x-4 bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm">
                                    <img class="h-12 w-12 rounded-full border-2 border-white shadow-sm object-cover" 
                                         src="{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                         alt="{{ auth()->user()->name }}">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 truncate max-w-[200px]" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                                        <a href="{{ route('profile.edit') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium inline-flex items-center mt-1">
                                            <i class="fas fa-cog mr-1"></i> Edit Profil
                                        </a>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->role_id == 1)
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex items-center justify-center w-full px-6 py-3 mb-3 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                                    <i class="fas fa-solar-panel mr-2"></i> Admin Panel
                                </a>
                                @endif
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center justify-center w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                    class="flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1" />
                                    </svg>
                                    Login
                                </a>
                            @endauth
                        </div>

                    </div>
                </div>



                <script>
                    const btnOpen = document.getElementById("open-forum");
                    const btnClose = document.getElementById("close-forum");
                    const widget = document.getElementById("forum-widget");

                    if (btnOpen) {
                        btnOpen.addEventListener("click", () => {
                            widget.classList.remove("hidden");
                        });
                    }

                    if (btnClose) {
                        btnClose.addEventListener("click", () => {
                            widget.classList.add("hidden");
                        });
                    }
                </script>


                <!-- Smooth animation styles for dropdown content -->
                <style>
                    .fade-in {
                        animation: fadeIn 0.5s ease-in-out;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    #layanan-content {
                        transition: opacity 0.25s ease-in-out;
                    }
                </style>
