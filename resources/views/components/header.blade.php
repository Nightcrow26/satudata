@props(['title' => 'Dashboard'])
<div class="d-none d-md-flex justify-content-between align-items-center p-3 mt-3 mx-3 bg-white shadow sticky-top rounded">
  <h4 class="mb-0">{{ $title ?? 'Dashboard' }}</h4>

  {{-- RIGHT: theme switch + user dropdown (inline) --}}
  <div x-data="{ open: false }" class="position-relative d-flex align-items-center gap-2">

    {{-- Switch Tema --}}
    <div class="form-check form-switch m-0">
      <label class="switch" for="themeSwitchDesktop">
        <input type="checkbox" class="js-theme-switch" id="themeSwitchDesktop" role="switch" aria-label="Toggle tema">
        <span class="slider"></span>
      </label>
    </div>


    {{-- Tombol Akun --}}
    <button
      type="button"
      class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
      @click="open = !open"
      aria-expanded="false"
      aria-haspopup="true"
    >
      <i class="bi bi-person-circle fs-4"></i>
    </button>

    {{-- Dropdown Akun --}}
    <div
      x-show="open"
      x-transition
      @click.outside="open = false"
      class="dropdown-menu dropdown-menu-end show position-absolute end-0 top-100 mt-2 z-3"
      style="display: none;"
      role="menu"
    >
      <livewire:userprofile />
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="m-0 p-2">
        @csrf
        <button class="dropdown-item text-danger" type="submit">
          <i class="bi bi-box-arrow-right me-2"></i>Log Out
        </button>
      </form>
    </div>
  </div>
</div>

