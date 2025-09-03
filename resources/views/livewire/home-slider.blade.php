<div class="flex flex-col min-h-screen">

    <nav class="bg-teal-600 text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center">
                    <img src="{{ asset('logo-hsu.png') }}" alt="Logo HSU" class="w-9 h-10 mr-3">
                    <span class="font-bold text-lg">Satu Data Hulu Sungai Utara</span>
                </div>
                <button class="lg:hidden" type="button" id="navToggle">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="hidden lg:flex items-center space-x-6" id="navMenu">
                    <ul class="flex space-x-6">
                        <li><a class="hover:text-teal-200 border-b-2 border-white pb-1" href="#">Home</a></li>
                        <li><a class="hover:text-teal-200" href="#">Data</a></li>
                        <li><a class="hover:text-teal-200" href="#">Publikasi</a></li>
                        <li><a class="hover:text-teal-200" href="#">Instansi</a></li>
                        <li><a class="hover:text-teal-200" href="#">Statistik Dasar</a></li>
                    </ul>

                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="toggleTheme" class="sr-only">
                        </div>
                        <a href="/login" class="bg-white text-teal-600 px-4 py-2 rounded hover:bg-gray-100">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Banner Utama --}}
    <section class="relative h-96 bg-cover bg-center" style="background-image: url('{{ asset('banner-hsu.jpg') }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative flex flex-col items-center justify-center h-full text-center text-white px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Satu <span class="text-teal-400">Data</span><br>Hulu Sungai Utara
            </h1>
            <p class="text-lg md:text-xl mb-8 max-w-2xl">
                Kebutuhan Data Terintegrasi dalam Satu Wadah<br>
                Pencarian Data HSU Lebih Mudah!
            </p>

            {{-- Statistika di Bawah Banner --}}
            <div class="flex flex-col sm:flex-row bg-white rounded-full shadow-lg overflow-hidden mb-6">
                <div class="flex items-center justify-center py-3 px-6 border-r border-gray-200">
                    <span class="text-2xl font-bold text-teal-600">10</span>
                    <span class="ml-2 text-gray-600">Data</span>
                </div>
                <div class="flex items-center justify-center py-3 px-6 border-r border-gray-200">
                    <span class="text-2xl font-bold text-teal-600">10</span>
                    <span class="ml-2 text-gray-600">Publikasi</span>
                </div>
                <div class="flex items-center justify-center py-3 px-6">
                    <span class="text-2xl font-bold text-teal-600">10</span>
                    <span class="ml-2 text-gray-600">Instansi</span>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="w-full max-w-md">
                <div class="relative">
                    <input type="text" class="w-full px-4 py-3 rounded-full text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Cari data yang anda butuhkan">
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-teal-500 text-white p-2 rounded-full hover:bg-teal-600">
                        🔍
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Konten Utama --}}
    <main class="container mx-auto px-4 my-12 flex-grow">

        {{-- 1. DATA TERBARU --}}
        <section class="mb-16">
            <h2 class="text-3xl font-semibold text-center text-teal-600 mb-8">Data Terbaru</h2>

            <div class="relative">
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-300" id="dataCarousel">
                        @foreach(array_chunk($dataTerbaru, 4) as $i => $chunk)
                            <div class="w-full flex-shrink-0">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                    @foreach($chunk as $item)
                                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                            <div class="flex items-center justify-center py-8 bg-gray-50 rounded-t-lg">
                                                <span class="text-4xl">{{ $item['icon'] }}</span>
                                            </div>
                                            <div class="p-4">
                                                <div class="text-center mb-3">
                                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">{{ $item['kategori'] }}</span>
                                                </div>
                                                <h5 class="font-semibold text-gray-900 mb-2">{{ $item['judul'] }}</h5>
                                                <p class="text-gray-600 text-sm mb-2">
                                                    <strong>{{ $item['instansi'] }}</strong> · {{ $item['tanggal'] }}
                                                </p>
                                                <p class="text-gray-500 text-sm mb-3">
                                                    {{ $item['sub_indikator'] }} Sub Indikator
                                                </p>
                                                <p class="text-gray-400 text-sm">
                                                    {{ $item['deskripsi'] ?? 'Data ini merupakan data terbaru untuk ' . strtolower($item['kategori']) . '.' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Carousel Controls --}}
                <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 hover:bg-gray-50" id="prevData">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 hover:bg-gray-50" id="nextData">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <div class="text-center mt-8">
                <a href="#" class="bg-teal-500 text-white px-8 py-3 rounded-full hover:bg-teal-600 transition-colors duration-300">Lihat Lebih Banyak</a>
            </div>
        </section>

        {{-- 2. PUBLIKASI TERBARU --}}
        <section class="mb-16">
            <h2 class="text-3xl font-semibold text-teal-600 mb-8">Publikasi Terbaru</h2>

            <div class="relative">
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-300" id="publikasiCarousel">
                        @php
                            $chunksPublikasi = array_chunk($publikasiTerbaru, 4);
                        @endphp

                        @foreach($chunksPublikasi as $i => $chunk)
                            <div class="w-full flex-shrink-0">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                    @foreach($chunk as $item)
                                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                            <div class="flex items-center justify-center h-40 bg-gray-50 rounded-t-lg">
                                                <span class="text-4xl">{{ $item['icon'] }}</span>
                                            </div>
                                            <div class="p-4">
                                                <span class="inline-block bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded-full mb-2">{{ $item['kategori'] }}</span>
                                                <h5 class="font-semibold text-gray-900 mb-2">{{ $item['judul'] }}</h5>
                                                <p class="text-gray-600 text-sm mb-2">
                                                    <strong>{{ $item['instansi'] }}</strong> · {{ $item['tanggal'] }}
                                                </p>
                                                <p class="text-gray-500 text-sm">
                                                    {{ $item['sub_indikator'] }} Sub Indikator
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Carousel Controls --}}
                <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 hover:bg-gray-50" id="prevPublikasi">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 hover:bg-gray-50" id="nextPublikasi">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <div class="text-center mt-8">
                <a href="#" class="bg-teal-500 text-white px-8 py-3 rounded-full hover:bg-teal-600 transition-colors duration-300">Lihat Lebih Banyak</a>
            </div>
        </section>

        {{-- 3. JADWAL RILIS DATA DAN PUBLIKASI --}}
        <section class="mb-16">
            <h2 class="text-3xl font-semibold text-teal-600 text-center mb-8">Jadwal Rilis Data dan Publikasi</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Tabel Data 2025 --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-teal-500 text-white px-6 py-4">
                        <h5 class="text-xl font-semibold">Data 2025</h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Rilis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($jadwalData2025 as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['judul'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row['rilis'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center py-4">
                        <a href="#" class="text-teal-600 border border-teal-600 px-6 py-2 rounded-full hover:bg-teal-50 transition-colors duration-300">Lihat Lebih Banyak</a>
                    </div>
                </div>

                {{-- Tabel Publikasi 2025 --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-teal-500 text-white px-6 py-4">
                        <h5 class="text-xl font-semibold">Publikasi 2025</h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publikasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Rilis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($jadwalPublikasi2025 as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['judul'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row['rilis'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center py-4">
                        <a href="#" class="text-teal-600 border border-teal-600 px-6 py-2 rounded-full hover:bg-teal-50 transition-colors duration-300">Lihat Lebih Banyak</a>
                    </div>
                </div>
            </div>
        </section>
        {{-- contoh: resources/views/user/home.blade.php --}}
        {{-- ... konten halaman Anda ... --}}

        <x-chat-fab/> 
        @stack('scripts')

    </main>

    {{-- Footer --}}
    <footer class="bg-teal-600 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            © 2025 - Dinas Komunikasi, Informatika dan Persandian Kab. HSU
        </div>
    </footer>

</div>

<script>
// Mobile navigation toggle
document.getElementById('navToggle').addEventListener('click', function() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('hidden');
});

// Carousel functionality
let currentDataSlide = 0;
let currentPublikasiSlide = 0;
const dataSlides = document.querySelectorAll('#dataCarousel > div');
const publikasiSlides = document.querySelectorAll('#publikasiCarousel > div');

function updateDataCarousel() {
    const carousel = document.getElementById('dataCarousel');
    carousel.style.transform = `translateX(-${currentDataSlide * 100}%)`;
}

function updatePublikasiCarousel() {
    const carousel = document.getElementById('publikasiCarousel');
    carousel.style.transform = `translateX(-${currentPublikasiSlide * 100}%)`;
}

document.getElementById('nextData').addEventListener('click', function() {
    currentDataSlide = (currentDataSlide + 1) % dataSlides.length;
    updateDataCarousel();
});

document.getElementById('prevData').addEventListener('click', function() {
    currentDataSlide = (currentDataSlide - 1 + dataSlides.length) % dataSlides.length;
    updateDataCarousel();
});

document.getElementById('nextPublikasi').addEventListener('click', function() {
    currentPublikasiSlide = (currentPublikasiSlide + 1) % publikasiSlides.length;
    updatePublikasiCarousel();
});

document.getElementById('prevPublikasi').addEventListener('click', function() {
    currentPublikasiSlide = (currentPublikasiSlide - 1 + publikasiSlides.length) % publikasiSlides.length;
    updatePublikasiCarousel();
});

// Auto slide for carousels
setInterval(function() {
    currentDataSlide = (currentDataSlide + 1) % dataSlides.length;
    updateDataCarousel();
}, 5000);

setInterval(function() {
    currentPublikasiSlide = (currentPublikasiSlide + 1) % publikasiSlides.length;
    updatePublikasiCarousel();
}, 6000);
</script>