@props(['title' => 'Dashboard'])

<!-- Navbar Mobile -->
<nav class="navbar navbar-light bg-white shadow-sm d-md-none rounded">
  <div class="container-fluid">
    <!-- Hamburger -->
    <button class="btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      <i class="bi bi-list fs-3"></i>
    </button>

    <!-- Title -->
    <span class="navbar-brand mb-0 h5">{{ $title }}</span>

    <!-- Profile Dropdown -->
    <div x-data="{ open: false }" class="position-relative">
      <button @click="open = !open" class="btn">
        <i class="bi bi-person-circle fs-4"></i>
      </button>
      <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="dropdown-menu show position-absolute end-0 mt-2"
        style="display: none;"
      >
        <form action="{{ route('logout') }}" method="POST" class="m-0 p-2">
          @csrf
          <button class="dropdown-item text-danger" type="submit">
            <i class="bi bi-box-arrow-right me-2"></i>Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</nav>

<!-- Offcanvas Mobile Sidebar -->
<div 
  class="offcanvas offcanvas-start d-md-none" 
  tabindex="-1" 
  id="offcanvasSidebar"
  x-data="{ openMaster: {{ request()->routeIs('dataset.*') || request()->routeIs('aspek') ? 'true' : 'false' }} }"
>
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="form-check form-switch m-0">
    <label class="switch" for="themeSwitchMobile">
      <input type="checkbox" class="js-theme-switch" id="themeSwitchMobile" role="switch" aria-label="Toggle tema">
      <span class="slider"></span>
    </label>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="nav flex-column p-3">
      <!-- Dashboard -->
      <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
      </x-nav-link>

      <x-nav-link :active="request()->routeIs('dataset.index','dataset.*')"
                  href="{{ route('dataset.index') }}">
        <i class="bi bi-folder me-2"></i>Dataset
      </x-nav-link>
     
      <!-- Publikasi -->
      <x-nav-link :active="request()->routeIs('publikasi.index','publikasi.*')"
                  href="{{ route('publikasi.index') }}"
                  class="mt-2">
        <i class="bi bi-book me-2"></i>Publikasi
      </x-nav-link>

      <x-nav-link :active="request()->routeIs('walidata.index','walidata.*')"
                  href="{{ route('walidata.index') }}"
                  class="mt-2">
        <i class="bi bi-journal-check me-2"></i>Indikator Walidata
      </x-nav-link>

      @if (auth()->user()->hasRole('admin'))
        <!-- Admin Only Section -->
        <x-nav-link :active="request()->routeIs('skpd.index','skpd.*')"
                    href="{{ route('skpd.index') }}"
                    class="mt-2">
          <i class="bi bi-building me-2"></i>SKPD
        </x-nav-link>

        <x-nav-link :active="request()->routeIs('users.index','users.*')"
                    href="{{ route('users.index') }}"
                    class="mt-2">
          <i class="bi bi-people me-2"></i>Users
        </x-nav-link>

        {{-- Master Data Dropdown --}}
        @php
          // pre‐open when on bidang or indikator
          $isOpenOnLoad = request()->routeIs('bidang') || request()->routeIs('indikator');
        @endphp
        <li class="nav-item mb-2"
            x-data="{ open: {{ $isOpenOnLoad ? 'true' : 'false' }} }">
          <a href="#"
            @click.prevent="open = !open"
            class="nav-link d-flex justify-content-between align-items-center mb-0">
            <span><i class="bi bi-folder me-2"></i>Master Data</span>
            <i class="bi bi-chevron-down arrow"
              :class="{ 'rotated': open }"></i>
          </a>

          <ul class="nav flex-column ms-3"
              x-show="open"
              x-cloak
              style="display: none;">
            <li class="nav-item">
              <x-nav-link :active="request()->routeIs('bidang')"
                          href="{{ route('bidang') }}"
                          class="mb-1">
                Bidang
              </x-nav-link>
            </li>
            <li class="nav-item">
              <x-nav-link :active="request()->routeIs('indikator')"
                          href="{{ route('indikator') }}"
                          class="mb-1">
                Indikator
              </x-nav-link>
            </li>
            <li class="nav-item">
              <x-nav-link :active="request()->routeIs('aspek')"
                          href="{{ route('aspek') }}"
                          class="mb-1">
                Aspek
              </x-nav-link>
            </li>
            {{-- Switch Tema --}}
          </ul>
        </li>
      @endif
    </nav>
  </div>
</div>
