<nav class="sidebar hidden md:block shadow-lg p-3 m-2 rounded-lg transition-all duration-300 bg-white dark:!bg-gray-800"
     x-data="{ 
       collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
       toggle() {
         this.collapsed = !this.collapsed;
         localStorage.setItem('sidebar-collapsed', this.collapsed.toString());
       }
     }"
     :class="{ 'sidebar-collapsed': collapsed }">
     
  <!-- Toggle Button -->
  <div class="flex justify-end mb-3">
    <button @click="toggle()"
            class="inline-flex items-center px-2 py-1 text-sm font-medium rounded-md text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
      <i class="bi bi-list" x-show="!collapsed"></i>
      <i class="bi bi-chevron-right" x-show="collapsed"></i>
    </button>
  </div>

  <!-- Logo dan Title -->
  <div class="text-center mb-5" x-show="!collapsed" x-transition>
    <img src="{{ resolve_media_url(asset('logo-hsu.png'), ['temporary'=>false]) }}"
         alt="Logo"
         class="mx-auto max-h-20 w-auto"
         onerror="this.onerror=null;this.src='{{ asset('logo-hsu.png') }}'">
    <h6 class="mt-2 text-gray-900 dark:text-white font-semibold">Satu Data<br>Hulu Sungai Utara</h6>
  </div>

  <!-- Logo kecil untuk collapsed state -->
  <div class="text-center mb-4" x-show="collapsed" x-transition>
    <img src="{{ resolve_media_url(asset('logo-hsu.png'), ['temporary'=>false]) }}"
         alt="Logo"
         class="mx-auto"
         style="height: 30px; width: auto;"
         onerror="this.onerror=null;this.src='{{ asset('logo-hsu.png') }}'">
  </div>

  <ul class="flex flex-col space-y-1">

    {{-- Home --}}
    <li>
      <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
        <x-admin.nav-link :active="request()->routeIs('admin.dashboard')"
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center mb-2 w-full"
                    x-bind:class="{ 'justify-center': collapsed }"
                    title="Dashboard">
          <i class="bi bi-house-door mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
          <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Dashboard</span>
        </x-admin.nav-link>
      </div>
    </li>

    <li>
      <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
        <x-admin.nav-link :active="request()->routeIs('admin.dataset.index','admin.dataset.*')"
                    href="{{ route('admin.dataset.index') }}"
                    class="flex items-center mb-2 w-full"
                    x-bind:class="{ 'justify-center': collapsed }"
                    title="Dataset">
          <i class="bi bi-folder mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
          <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Dataset</span>
        </x-admin.nav-link>
      </div>
    </li>

    {{-- Publikasi --}}
    <li>
      <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
        <x-admin.nav-link :active="request()->routeIs('admin.publikasi.index','admin.publikasi.*')"
                    href="{{ route('admin.publikasi.index') }}"
                    class="flex items-center mb-2 w-full"
                    x-bind:class="{ 'justify-center': collapsed }"
                    title="Publikasi">
          <i class="bi bi-book mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
          <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Publikasi</span>
        </x-admin.nav-link>
      </div>
    </li>

    {{-- Indikator Walidata --}}
    <li>
      <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
        <x-admin.nav-link :active="request()->routeIs('admin.walidata.index','admin.walidata.*')"
                    href="{{ route('admin.walidata.index') }}"
                    class="flex items-center mb-2 w-full"
                    x-bind:class="{ 'justify-center': collapsed }"
                    title="Indikator Walidata">
          <i class="bi bi-journal-check mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
          <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Indikator Walidata</span>
        </x-admin.nav-link>
      </div>
    </li>

    {{-- SKPD --}}
    <li>
      <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
        <x-admin.nav-link :active="request()->routeIs('admin.skpd.index','admin.skpd.*')"
                    href="{{ route('admin.skpd.index') }}"
                    class="flex items-center mb-2 w-full"
                    x-bind:class="{ 'justify-center': collapsed }"
                    title="SKPD">
          <i class="bi bi-building mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
          <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>SKPD</span>
        </x-admin.nav-link>
      </div>
    </li>
    
    @if (auth()->check())
      {{-- Users: admin only --}}
      @if (auth()->user()->hasRole('admin'))
        <li>
          <div x-bind:class="{ 'flex justify-center': collapsed, 'flex': !collapsed }">
            <x-admin.nav-link :active="request()->routeIs('admin.users.index','admin.users.*')"
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center mb-2 w-full"
                        x-bind:class="{ 'justify-center': collapsed }"
                        title="Users">
              <i class="bi bi-people mr-2" x-bind:class="{ 'mr-0': collapsed }"></i>
              <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Users</span>
            </x-admin.nav-link>
          </div>
        </li>
      @endif

      {{-- Master Data Dropdown: admin + verifikator (but hide Survey Pengguna) --}}
      @php
        $isOpenOnLoad = request()->routeIs('admin.bidang') || request()->routeIs('admin.indikator') || request()->routeIs('admin.aspek');
      @endphp
      @if (auth()->user()->hasAnyRole(['admin','verifikator']))
        <li class="mb-2"
            x-data="{ open: {{ $isOpenOnLoad ? 'true' : 'false' }} }">
          
          <a href="#"
             @click.prevent="
               if (collapsed) {
                 collapsed = false;
                 localStorage.setItem('sidebar-collapsed', 'false');
                 open = true;
               } else {
                 open = !open;
               }
             "
             class="flex items-center mb-2 px-3 py-2 rounded-md text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors cursor-pointer"
             :class="{ 'justify-center': collapsed, 'justify-between': !collapsed }"
              title="Master Data">
            <span class="flex items-center">
              <i class="bi bi-building-gear mr-2" :class="{ 'mr-0': collapsed }"></i>
              <span class="text-gray-900 dark:text-white" x-show="!collapsed" x-transition>Master Data</span>
            </span>
            <i class="bi bi-chevron-down transform transition-transform duration-200"
               :class="{ 'rotate-180': open }"
               x-show="!collapsed"></i>
          </a>

          <ul class="flex flex-col ml-3 space-y-1"
              x-show="open && !collapsed"
              x-cloak
              style="display: none;">
            <li>
              <x-admin.nav-link :active="request()->routeIs('admin.bidang')"
                          href="{{ route('admin.bidang') }}"
                          class="flex items-center mb-1">
                <i class="bi bi-circle-square mr-2"></i>Bidang
              </x-admin.nav-link>
            </li>
            <li>
              <x-admin.nav-link :active="request()->routeIs('admin.indikator')"
                          href="{{ route('admin.indikator') }}"
                          class="flex items-center mb-1">
                <i class="bi bi-clipboard2-data mr-2"></i>Indikator
              </x-admin.nav-link>
            </li>
            <li>
              <x-admin.nav-link :active="request()->routeIs('admin.aspek')"
                          href="{{ route('admin.aspek') }}"
                          class="flex items-center mb-1">
                <i class="bi bi-columns mr-2"></i>Aspek
              </x-admin.nav-link>
            </li>
            @if (auth()->user()->hasRole('admin'))
              <li>
                <x-admin.nav-link :active="request()->routeIs('admin.survey')"
                            href="{{ route('admin.survey') }}"
                            class="flex items-center mb-1">
                  <i class="bi bi-columns mr-2"></i>Survey Pengguna
                </x-admin.nav-link>
              </li>
            @endif
          </ul>
        </li>
      @endif
    @endif
  </ul>
</nav>
