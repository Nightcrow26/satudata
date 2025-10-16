@props([
'org' => 'Dinas Komunikasi, Informatika dan Persandian Kab. HSU',
'year' => now()->year,
])

<footer class="bg-gradient-to-br from-teal-700 via-teal-600 to-teal-800 dark:from-teal-800 dark:via-teal-700 dark:to-teal-900 text-white transition-colors duration-300">
    <!-- Decorative top border -->
    <div class="h-1 bg-gradient-to-r from-teal-400 via-teal-300 to-teal-500"></div>
    
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Main content grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            
            <!-- Logo and Organization Info -->
            <div class="text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start">
                    <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                        <img src="{{ asset('logo-hsu.png') }}" alt="Logo Satu Data" class="w-10 h-10 object-contain">
                    </div>
                    <div class="leading-none">
                        <h3 class="text-lg font-bold text-teal-100 leading-none -mb-1">SATU DATA</h3>
                        <h4 class="text-sm text-teal-200 leading-none">KABUPATEN HULU SUNGAI UTARA</h4>
                    </div>
                </div>
            </div>

            <!-- Center - Copyright -->
            <div class="text-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <p class="text-sm font-medium text-teal-100 mb-1">
                        © {{ $year }}
                    </p>
                    <p class="text-xs text-teal-200 leading-relaxed">
                        {{ $org }}
                    </p>
                </div>
            </div>

            <!-- Support & Links -->
            <div class="text-center md:text-right">
                <div class="space-y-3">
                    <!-- Helpdesk Link -->
                    <a href="https://helpdesk.hsu.go.id/" 
                       target="_blank"
                       class="inline-flex items-center justify-center md:justify-end px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg transition-all duration-200 hover:scale-105 group">
                        <i class="bi bi-question-octagon text-teal-100 group-hover:text-white mr-2"></i>
                        <span class="text-sm font-medium text-teal-100 group-hover:text-white">
                            Bantuan & Dukungan
                        </span>
                        <svg class="w-3 h-3 ml-1 text-teal-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    
                    <!-- Additional info -->
                    <p class="text-xs text-teal-300">
                        Butuh bantuan? Hubungi helpdesk kami
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
