<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="{ 'dark': localStorage.getItem('darkMode') === 'true' }">
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Page Title' }}</title>
    <link rel="icon" href="{{ asset('logo-hsu.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    /* Pastikan carousel tetap overflow visible dan ada padding untuk panah */
    #carouselDataTerbaru {
      position: relative;
      overflow: visible;
      padding: 0 20px; /* cukup ruang agar panah tidak tumpang tindih */
    }

    /* Hapus background & border-radius tombol */
    #carouselDataTerbaru .carousel-control-prev,
    #carouselDataTerbaru .carousel-control-next {
      width: auto;
      height: auto;
      background: none;
      border-radius: 0;
      top: 50%;
      transform: translateY(-50%);
    }

    /* Sembunyikan ikon bawaan */
    #carouselDataTerbaru .carousel-control-prev-icon,
    #carouselDataTerbaru .carousel-control-next-icon {
      display: none;
    }

    /* Tampilkan hanya ikon chevron */
    #carouselDataTerbaru .carousel-control-prev i,
    #carouselDataTerbaru .carousel-control-next i {
      font-size: 2rem;
      color: #9DE0D8; /* sesuaikan warna panah */
    }

    /* Posisi panah kiri/kanan relatif ke container */
    #carouselDataTerbaru .carousel-control-prev {
      left: 0;
    }
    #carouselDataTerbaru .carousel-control-next {
      right: 0;
    }
  </style>

  </head>
  <body>

    <main>
              {{ $slot}}
              <x-admin.chat-fab/> 
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>window.SDI_STREAM_URL = @json(route('chatbot.stream'));</script>
    @if(session('swal'))
    <script>
      Swal.fire(@json(session('swal')));
    </script>
    @endif
    @stack('scripts')


  </body>
</html>