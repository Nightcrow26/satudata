@php
  $title = $title ?? 'Dashboard';
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('logo-hsu.png') }}" type="image/png">
    
    {{-- Livewire & Vite --}}
    @livewireStyles

    <script>
    (function () {
      try {
        var t = localStorage.getItem('bsTheme');
        if (!t) {
          t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
          localStorage.setItem('bsTheme', t);
        }
        document.documentElement.setAttribute('data-bs-theme', t);
      } catch (e) { /* ignore */ }
    })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- SweetAlert & Font --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>

    <style>
      .table-clean thead th { position: sticky; top: 0; z-index: 1; }
      .w-40 { width: 40px; } .w-80 { width: 80px; } .w-120 { width: 120px; }
      .truncate { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      @media (max-width: 991.98px){ .truncate { max-width: 180px; } }
    </style>
  </head>

  <body class="min-h-screen flex flex-col bg-gray-100 text-gray-800 font-inter">

    {{-- Nav Mobile --}}
    <x-admin.nav-mobile :title="$title" />

    {{-- Main Layout --}}
    <div class="flex flex-1 overflow-hidden">
      
      {{-- Sidebar --}}
      <x-admin.sidebar />

      {{-- Main Content Area --}}
      <div class="flex flex-col flex-1 overflow-auto">
        {{-- Header --}}
        <x-admin.header :title="$title" />

        {{-- Main Slot Content --}}
        <main class="p-4">
          {{ $slot }}
        </main>
      </div>
    </div>

    <x-admin.chat-fab/>

    {{-- Footer --}}
    <x-admin.footer/>

    {{-- Scripts --}}
    @livewireScripts
    <script>
      (function () {
        function applyTheme(theme) {
          document.documentElement.setAttribute('data-bs-theme', theme);
          try { localStorage.setItem('bsTheme', theme); } catch(e) {}

          // sinkronkan semua switch
          document.querySelectorAll('.js-theme-switch').forEach(sw => {
            sw.checked = (theme === 'dark');
          });
        }

        function bindSwitches() {
          document.querySelectorAll('.js-theme-switch').forEach(sw => {
            if (sw.dataset.bound) return;       // hindari double-binding
            sw.addEventListener('change', e => {
              applyTheme(e.target.checked ? 'dark' : 'light');
            });
            sw.dataset.bound = '1';
          });
        }

        // Inisialisasi saat halaman siap
        document.addEventListener('DOMContentLoaded', function () {
          applyTheme(localStorage.getItem('bsTheme') || 'light');
          bindSwitches();
        });

        // Re-apply setelah Livewire SPA navigation
        document.addEventListener('livewire:navigated', function () {
          applyTheme(localStorage.getItem('bsTheme') || 'light');
          bindSwitches();
        });

        // Jika offcanvas dibuka, pastikan switch di dalamnya ikut ter-bind
        document.addEventListener('shown.bs.offcanvas', function () {
          applyTheme(localStorage.getItem('bsTheme') || 'light');
          bindSwitches();
        });
      })();
    </script>

    <script>window.SDI_STREAM_URL = @json(route('chatbot.stream'));</script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
    @stack('scripts')
  </body>
</html>
