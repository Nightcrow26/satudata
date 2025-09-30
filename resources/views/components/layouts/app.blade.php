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

    {{-- Anti-FOUC: set kelas .dark sedini mungkin sebelum CSS/JS dimuat --}}
    <script>
        (function () {
        try {
          var saved = localStorage.getItem('theme');
          var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          var useDark = saved ? (saved === 'dark') : prefersDark;
          document.documentElement.classList.toggle('dark', useDark);
        } catch (e) {}
      })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- SweetAlert & Font --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
    
    {{-- Tom Select CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
  </head>

  <body class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-inter">

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
    <x-ui.footer/>

    {{-- Scripts --}}
    @livewireScripts
    
    {{-- External dependencies for admin features --}}
    <script>window.SDI_STREAM_URL = @json(route('chatbot.stream'));</script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
    
    {{-- Tom Select JS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    
    @stack('scripts')
  </body>
</html>
