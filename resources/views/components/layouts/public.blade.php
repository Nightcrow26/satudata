@props(['title' => 'Satu Data'])

<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/public/hsu_logo.png') }}">

    {{-- Dependencies for chat functionality --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
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
</head>

<body class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-inter">
    <x-ui.navbar />

    {{-- Konten isi halaman --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer dipusatkan di layout --}}
    <x-ui.footer />
    <x-admin.chat-fab/>

    {{-- Survey Modal Component --}}
    @livewire('public.download-survey-modal')

    {{-- Scripts --}}
    @livewireScripts
    
    {{-- External dependencies for public chat functionality --}}
    <script>window.SDI_STREAM_URL = @json(route('chatbot.stream'));</script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
    
</body>

</html>
