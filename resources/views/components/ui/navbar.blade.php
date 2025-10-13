@php
$links = [
['label' => 'Home', 'href' => route('public.home'), 'active' => request()->routeIs('public.home')],
['label' => 'Data', 'href' => route('public.data.index'), 'active' => request()->routeIs('public.data.*')],
['label' => 'Indikator Walidata', 'href' => route('public.walidata.index'), 'active' => request()->routeIs('public.walidata.*')],
['label' => 'Publikasi', 'href' => route('public.publications.index'), 'active' =>
request()->routeIs('public.publications.*')],
['label' => 'Perangkat Daerah', 'href' => route('public.agencies.index'), 'active' => request()->routeIs('public.agencies.*')],
['label' => 'Statistik Dasar', 'href' => 'https://hulusungaiutarakab.bps.go.id/', 'active' => false],
];
@endphp

<nav x-data="{ open: false }"
    class="sticky top-0 z-50 bg-teal-700 dark:bg-teal-800 text-white border-b border-teal-800/40 dark:border-teal-700/40 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Bar atas --}}
        <div class="h-16 flex items-center justify-between relative" @keydown.escape.window="open=false">
            <!-- Kiri: Logo + Judul -->
            <a href="{{ route('public.home') }}" class="flex items-center gap-3" wire:navigate>
                <div class="h-9 w-9 rounded-full bg-white/10 grid place-items-center ring-1 ring-white/20">
                    <img src="{{ resolve_media_url(asset('images/public/hsu_logo.png'), ['temporary'=>false, 'fallback'=>asset('logo-hsu.png')]) }}" alt="Logo HSU" class="h-7 w-7 object-contain"
                        loading="lazy">
                </div>
                <div class="leading-tight">
                    <div class="font-semibold">Satu <span class="text-teal-200">Data</span></div>
                    <div class="text-xs text-teal-100/80">Hulu Sungai Utara</div>
                </div>
            </a>

            <!-- Tengah: Menu (desktop) -->
            <div class="hidden md:flex items-center gap-6">
                @foreach ($links as $l)
                @if($l['label'] === 'Statistik Dasar')
                {{-- Link eksternal khusus untuk Statistik Dasar --}}
                <a href="{{ $l['href'] }}" @class([ 'text-sm transition' , 'opacity-90 hover:opacity-100'=> !
                    $l['active'],
                    'font-semibold border-b-2 border-teal-200' => $l['active'],
                    ]) target="_blank" rel="noopener noreferrer">
                    {{ $l['label'] }}
                </a>
                @else
                {{-- Link internal --}}
                <a href="{{ $l['href'] }}" @class([ 'text-sm transition' , 'opacity-90 hover:opacity-100'=> !
                    $l['active'],
                    'font-semibold border-b-2 border-teal-200' => $l['active'],
                    ]) wire:navigate>
                    {{ $l['label'] }}
                </a>
                @endif
                @endforeach
            </div>

            <!-- Kanan: Dark Mode Toggle + Login + Hamburger (mobile) -->
            <div class="flex items-center gap-2">
                <!-- Dark Mode Toggle Button -->
                <button type="button" x-data="darkModeToggle()" @click="toggle()"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-white/10 hover:bg-white/15 ring-1 ring-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 transition-colors duration-200"
                    :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                    title="Toggle dark/light mode">
                    <!-- Sun icon (light mode) -->
                    <svg x-show="!isDark" class="h-5 w-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" />
                    </svg>
                    <!-- Moon icon (dark mode) -->
                    <svg x-show="isDark" x-cloak class="h-5 w-5 text-blue-200" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                @auth
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center h-9 px-4 rounded-full text-sm font-medium
           bg-white text-teal-800 hover:bg-teal-50
           dark:bg-white/10 dark:text-white dark:hover:bg-white/15
           ring-1 ring-white/20 dark:ring-white/20
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70
           transition-colors duration-200">
                        Logout
                    </button>
                </form>
                @else
                <a href="/login" class="hidden sm:inline-flex items-center h-9 px-4 rounded-full text-sm font-medium
           bg-white text-teal-800 hover:bg-teal-50
           dark:bg-white/10 dark:text-white dark:hover:bg-white/15
           ring-1 ring-white/20 dark:ring-white/20
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70
           transition-colors duration-200">
                    Login
                </a>
                @endauth


                <!-- Tombol hamburger -->
                <button type="button"
                    class="inline-flex md:hidden items-center justify-center h-9 w-9 rounded-md bg-white/10 hover:bg-white/15 ring-1 ring-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Open menu" :aria-expanded="open.toString()" @click.stop="open = !open">
                    <!-- Icon burger -->
                    <svg x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Icon close -->
                    <svg x-show="open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M6 6l12 12M18 6l-12 12" />
                    </svg>
                </button>
            </div>

            <!-- Panel dropdown (mobile) -->
            <div class="absolute left-0 right-0 top-full md:hidden" @click.outside="open=false">
                <div x-cloak x-show="open" x-transition.origin.top
                    class="mt-2 bg-white dark:!bg-gray-800 text-teal-900 dark:!text-gray-200 rounded-xl shadow-xl ring-1 ring-black/10 dark:!ring-white/10 overflow-hidden transition-colors duration-300">

                    {{-- Bar kecil: hanya Login (tanpa toggle) --}}
                    <div class="px-3 py-2 sm:hidden flex items-center justify-end">
                        @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center h-9 px-4 rounded-full text-sm font-medium
               bg-teal-600 text-white hover:bg-teal-700
               dark:bg-teal-500 dark:hover:bg-teal-600
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 dark:focus-visible:ring-teal-400
               transition-colors duration-200">
                                Logout
                            </button>
                        </form>
                        @else
                        <a href="/login" class="inline-flex items-center h-9 px-4 rounded-full text-sm font-medium
               bg-teal-600 text-white hover:bg-teal-700
               dark:bg-teal-500 dark:hover:bg-teal-600
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 dark:focus-visible:ring-teal-400
               transition-colors duration-200">
                            Login
                        </a>
                        @endauth
                    </div>


                    <nav class="py-1">
                        @foreach ($links as $l)
                        @if($l['label'] === 'Statistik Dasar')
                        {{-- Link eksternal khusus untuk Statistik Dasar --}}
                        <a href="{{ $l['href'] }}"
                            class="block px-4 py-3 text-base {{ $l['active'] ? 'font-semibold text-teal-700 dark:text-teal-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                            @click="open=false" target="_blank" rel="noopener noreferrer">
                            {{ $l['label'] }}
                        </a>
                        @else
                        {{-- Link internal --}}
                        <a href="{{ $l['href'] }}"
                            class="block px-4 py-3 text-base {{ $l['active'] ? 'font-semibold text-teal-700 dark:text-teal-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                            @click="open=false" wire:navigate>
                            {{ $l['label'] }}
                        </a>
                        @endif
                        @endforeach
                    </nav>
                </div>
            </div>

        </div>
    </div>
</nav>
