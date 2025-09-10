<nav class="sidebar d-none d-md-block shadow p-3 m-2 rounded transition-all duration-300"
     x-data="{ 
       collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
       toggle() {
         this.collapsed = !this.collapsed;
         localStorage.setItem('sidebar-collapsed', this.collapsed.toString());
       }
     }"
     :class="{ 'sidebar-collapsed': collapsed }">
     
  <!-- Toggle Button -->
  <div class="d-flex justify-content-end mb-3">
    <button @click="toggle()"
            class="btn btn-sm btn-outline-secondary border-0">
      <i class="bi bi-list" x-show="!collapsed"></i>
      <i class="bi bi-chevron-right" x-show="collapsed"></i>
    </button>
  </div>

  <!-- Logo dan Title -->
  <div class="text-center mb-5" x-show="!collapsed" x-transition>
    <img src="{{ asset('logo-hsu.png') }}"
         alt="Logo"
         class="mx-auto max-h-20 w-auto">
    <h6 class="mt-2">Satu Data<br>Hulu Sungai Utara</h6>
  </div>

  <!-- Logo kecil untuk collapsed state -->
  <div class="text-center mb-4" x-show="collapsed" x-transition>
    <img src="{{ asset('logo-hsu.png') }}"
         alt="Logo"
         class="mx-auto"
         style="height: 30px; width: auto;">
  </div>

  <ul class="nav flex-column">

    {{-- Home --}}
    <li class="nav-item">
      <x-nav-link :active="request()->routeIs('dashboard')"
                  href="{{ route('dashboard') }}"
                  class="d-flex align-items-center mb-2"
                  data-bs-toggle="tooltip" title="Dashboard">
        <i class="bi bi-house-door me-2"></i>
        <span x-show="!collapsed" x-transition>Dashboard</span>
      </x-nav-link>
    </li>

    <li class="nav-item">
      <x-nav-link :active="request()->routeIs('dataset.index','dataset.*')"
                  href="{{ route('dataset.index') }}"
                  class="d-flex align-items-center mb-2"
                  data-bs-toggle="tooltip" title="Dataset">
        <i class="bi bi-folder me-2"></i>
        <span x-show="!collapsed" x-transition>Dataset</span>
      </x-nav-link>
    </li>

    {{-- Publikasi --}}
    <li class="nav-item">
      <x-nav-link :active="request()->routeIs('publikasi.index','publikasi.*')"
                  href="{{ route('publikasi.index') }}"
                  class="d-flex align-items-center mb-2"
                  data-bs-toggle="tooltip" title="Publikasi">
        <i class="bi bi-book me-2"></i>
        <span x-show="!collapsed" x-transition>Publikasi</span>
      </x-nav-link>
    </li>

    {{-- Indikator Walidata --}}
    <li class="nav-item">
      <x-nav-link :active="request()->routeIs('walidata.index','walidata.*')"
                  href="{{ route('walidata.index') }}"
                  class="d-flex align-items-center mb-2"
                  data-bs-toggle="tooltip" title="Indikator Walidata">
        <i class="bi bi-journal-check me-2"></i>
        <span x-show="!collapsed" x-transition>Indikator Walidata</span>
      </x-nav-link>
    </li>

    {{-- SKPD --}}
    <li class="nav-item">
      <x-nav-link :active="request()->routeIs('skpd.index','skpd.*')"
                  href="{{ route('skpd.index') }}"
                  class="d-flex align-items-center mb-2"
                  data-bs-toggle="tooltip" title="SKPD">
        <i class="bi bi-building me-2"></i>
        <span x-show="!collapsed" x-transition>SKPD</span>
      </x-nav-link>
    </li>
    
    @if (auth()->user()->hasRole('admin'))
      {{-- Users --}}
      <li class="nav-item">
        <x-nav-link :active="request()->routeIs('users.index','users.*')"
                    href="{{ route('users.index') }}"
                    class="d-flex align-items-center mb-2"
                    data-bs-toggle="tooltip" title="Users">
          <i class="bi bi-people me-2"></i>
          <span x-show="!collapsed" x-transition>Users</span>
        </x-nav-link>
      </li>

      {{-- Master Data Dropdown --}}
      @php
        $isOpenOnLoad = request()->routeIs('bidang') || request()->routeIs('indikator') || request()->routeIs('aspek');
      @endphp
      <li class="nav-item mb-2"
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
           class="nav-link d-flex align-items-center mb-2"
           :class="{ 'justify-content-center': collapsed, 'justify-content-between': !collapsed }"
            data-bs-toggle="tooltip" title="Master Data">
          <span class="d-flex align-items-center">
            <i class="bi bi-building-gear me-2" :class="{ 'me-0': collapsed }"></i>
            <span x-show="!collapsed" x-transition>Master Data</span>
          </span>
          <i class="bi bi-chevron-down arrow"
             :class="{ 'rotated': open }"
             x-show="!collapsed"></i>
        </a>

        <ul class="nav flex-column ms-3"
            x-show="open && !collapsed"
            x-cloak
            style="display: none;">
          <li class="nav-item">
            <x-nav-link :active="request()->routeIs('bidang')"
                        href="{{ route('bidang') }}"
                        class="mb-1">
              <i class="bi bi-circle-square me-2"></i>Bidang
            </x-nav-link>
          </li>
          <li class="nav-item">
            <x-nav-link :active="request()->routeIs('indikator')"
                        href="{{ route('indikator') }}"
                        class="mb-1">
              <i class="bi bi-clipboard2-data me-2"></i>Indikator
            </x-nav-link>
          </li>
          <li class="nav-item">
            <x-nav-link :active="request()->routeIs('aspek')"
                        href="{{ route('aspek') }}"
                        class="mb-1">
              <i class="bi bi-columns me-2"></i>Aspek
            </x-nav-link>
          </li>
        </ul>
      </li>
    @endif
  </ul>
</nav>