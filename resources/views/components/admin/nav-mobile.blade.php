@props(['title' => 'Dashboard'])

<div x-data="{ 
  sidebarOpen: false,
  openMaster: {{ request()->routeIs('admin.dataset.*') || request()->routeIs('admin.aspek') ? 'true' : 'false' }}
}">

<!-- Navbar Mobile -->
<nav class="bg-white dark:bg-gray-800 shadow-sm md:hidden rounded-lg">
  <div class="w-full px-4 py-3">
    <div class="flex items-center justify-between">
      <!-- Hamburger -->
      <button @click="sidebarOpen = true" class="p-2 text-gray-900 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
        <i class="bi bi-list text-2xl"></i>
      </button>

      <!-- Title -->
      <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</span>

      <!-- Profile Dropdown -->
      <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="p-2 text-gray-900 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
          <i class="bi bi-person-circle text-xl"></i>
        </button>
        <div
          x-show="open"
          @click.outside="open = false"
          x-transition:enter="transition ease-out duration-100"
          x-transition:enter-start="transform opacity-0 scale-95"
          x-transition:enter-end="transform opacity-100 scale-100"
          x-transition:leave="transition ease-in duration-75"
          x-transition:leave-start="transform opacity-100 scale-100"
          x-transition:leave-end="transform opacity-0 scale-95"
          class="absolute right-0 mt-2 w-48 bg-white dark:!bg-gray-800 rounded-lg shadow-lg z-50 border border-gray-200 dark:border-gray-700"
          style="display: none;"
        >
          <div class="py-1">
            {{-- User Profile Option (dispatch event to single modal instance) --}}
            <button type="button"
              class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center"
              @click="$dispatch('profile:open')"
            >
              <i class="bi bi-person-circle mr-2"></i>Profile
            </button>

            {{-- Divider --}}
            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
             <form action="{{ route('logout') }}" method="POST" class="block">
                @csrf
                <button 
                  type="submit" 
                  class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center transition-colors"
                >
                  <i class="bi bi-box-arrow-right mr-2"></i>Log Out
                </button>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Sidebar Overlay -->
<div 
  x-show="sidebarOpen" 
  @click="sidebarOpen = false"
  x-transition:enter="transition-opacity ease-linear duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition-opacity ease-linear duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
  style="display: none;"
></div>

<!-- Mobile Sidebar -->
<div 
  x-show="sidebarOpen"
  x-transition:enter="transition ease-in-out duration-300 transform"
  x-transition:enter-start="-translate-x-full"
  x-transition:enter-end="translate-x-0"
  x-transition:leave="transition ease-in-out duration-300 transform"
  x-transition:leave-start="translate-x-0"
  x-transition:leave-end="-translate-x-full"
  class="fixed inset-y-0 left-0 z-50 w-100 bg-white dark:!bg-gray-800 shadow-xl md:hidden"
  style="display: none;"
>
  <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
    <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Menu</h5>
    <button @click="sidebarOpen = false" class="p-2 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
      <i class="bi bi-x-lg text-lg"></i>
    </button>
  </div>
  
  <!-- Theme Switch -->
  <div class="p-4 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</span>
      <!-- Dark Mode Toggle Button -->
      <button type="button" x-data="darkModeToggle()" @click="toggle()"
          class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:focus-visible:ring-indigo-400 transition-colors duration-200"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          title="Toggle dark/light mode">
          <!-- Sun icon (light mode) -->
          <svg x-show="!isDark" class="h-4 w-4 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" />
          </svg>
          <!-- Moon icon (dark mode) -->
          <svg x-show="isDark" x-cloak class="h-4 w-4 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd" />
          </svg>
      </button>
    </div>
  </div>
  
  <div class="flex-1 overflow-y-auto">
    <nav class="flex flex-col p-4 space-y-1">
      <!-- Dashboard -->
      <x-admin.nav-link :active="request()->routeIs('admin.dashboard')" 
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center">
        <i class="bi bi-house-door mr-3"></i>Dashboard
      </x-admin.nav-link>

      <x-admin.nav-link :active="request()->routeIs('admin.dataset.index','admin.dataset.*')"
                        href="{{ route('admin.dataset.index') }}"
                        class="flex items-center">
        <i class="bi bi-folder mr-3"></i>Dataset
      </x-admin.nav-link>

      <!-- Publikasi -->
      <x-admin.nav-link :active="request()->routeIs('admin.publikasi.index','admin.publikasi.*')"
                        href="{{ route('admin.publikasi.index') }}"
                        class="flex items-center">
        <i class="bi bi-book mr-3"></i>Publikasi
      </x-admin.nav-link>

      <x-admin.nav-link :active="request()->routeIs('admin.walidata.index','admin.walidata.*')"
                        href="{{ route('admin.walidata.index') }}"
                        class="flex items-center">
        <i class="bi bi-journal-check mr-3"></i>Indikator Walidata
      </x-admin.nav-link>

      @if (auth()->check())
        @if (auth()->user()->hasAnyRole(['admin','verifikator']))
          <x-admin.nav-link :active="request()->routeIs('admin.skpd.index','admin.skpd.*')"
                            href="{{ route('admin.skpd.index') }}"
                            class="flex items-center">
            <i class="bi bi-building mr-3"></i>Produsen Data
          </x-admin.nav-link>
        @endif

        @if (auth()->user()->hasRole('admin'))
          <x-admin.nav-link :active="request()->routeIs('admin.users.index','admin.users.*')"
                            href="{{ route('admin.users.index') }}"
                            class="flex items-center">
            <i class="bi bi-people mr-3"></i>Users
          </x-admin.nav-link>
        @endif

        {{-- Master Data Dropdown: admin + verifikator (exclude Survey Pengguna for verifikator) --}}
        @php
          // pre‐open when on bidang or indikator
          $isOpenOnLoad = request()->routeIs('admin.bidang') || request()->routeIs('admin.indikator') || request()->routeIs('admin.aspek');
        @endphp
        @if (auth()->user()->hasAnyRole(['admin','verifikator']))
          <div class="mt-2">
            <button
              @click.prevent="openMaster = !openMaster"
              class="w-full flex items-center justify-between px-3 py-2 text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white rounded-md transition-colors"
            >
              <span class="flex items-center">
                <i class="bi bi-building-gear mr-3"></i>Master Data
              </span>
              <i class="bi bi-chevron-down transition-transform duration-200"
                :class="{ 'rotate-180': openMaster }"></i>
            </button>

            <div class="ml-6 mt-1 space-y-1"
                x-show="openMaster"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                style="display: none;">
              <x-admin.nav-link :active="request()->routeIs('admin.bidang')"
                                href="{{ route('admin.bidang') }}"
                                class="flex items-center py-2">
                <i class="bi bi-circle-square mr-3"></i>Bidang
              </x-admin.nav-link>
              <x-admin.nav-link :active="request()->routeIs('admin.indikator')"
                                href="{{ route('admin.indikator') }}"
                                class="flex items-center py-2">
                <i class="bi bi-clipboard2-data mr-3"></i>Indikator
              </x-admin.nav-link>
              <x-admin.nav-link :active="request()->routeIs('admin.aspek')"
                                href="{{ route('admin.aspek') }}"
                                class="flex items-center py-2">
                <i class="bi bi-columns mr-3"></i>Aspek
              </x-admin.nav-link>
              @if (auth()->user()->hasRole('admin'))
                <x-admin.nav-link :active="request()->routeIs('admin.survey')"
                                  href="{{ route('admin.survey') }}"
                                  class="flex items-center py-2">
                  <i class="bi bi-columns mr-3"></i>Survey Pengguna
                </x-admin.nav-link>
              @endif
            </div>
          </div>
        @endif
      @endif
    </nav>
  </div>
</div>

</div> <!-- Close wrapper x-data -->
