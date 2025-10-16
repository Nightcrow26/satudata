@props([
'bgImage' => null,
'title' => 'Satu',
'accent' => 'Data',
'subtitle' => null,
])

<section class="relative isolate">
    @if($bgImage)
    {{-- Gambar latar + overlay gradasi yang sedikit lebih kuat di dark mode --}}
    <img src="{{ resolve_media_url($bgImage) }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('kesehatan.png') }}'" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-r
                   from-black/80 via-transparent to-black/80
                   dark:from-black/70 dark:via-black/20 dark:to-black/70
                   transition-colors duration-200">
    </div>
    @else
    {{-- Fallback gradien brand di light, nuansa lebih gelap di dark --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-b
                   from-teal-800 to-teal-600
                   dark:from-teal-950 dark:to-teal-800
                   transition-colors duration-200">
    </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 lg:py-32 text-center">
        <div class="space-y-8">
            <!-- Main Title with Staggered Animation -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white">
                <!-- First part: Fade in from left -->
                <span class="inline-block animate-fade-in-left opacity-0 animation-delay-300">
                    {{ $title }}
                </span>
                
                <!-- Accent part: Scale and glow animation -->
                <span class="inline-block text-teal-300 dark:text-teal-400 transition-colors duration-200 
                           animate-scale-glow opacity-0 animation-delay-600
                           hover:text-teal-200 dark:hover:text-teal-300 cursor-default">
                    {{ $accent }}
                </span>
                
                <br class="hidden sm:block" />
                
                <!-- Location: Fade in from right -->
                <span class="inline-block animate-fade-in-right opacity-0 animation-delay-900
                           bg-gradient-to-r from-white via-teal-100 to-white bg-clip-text text-transparent">
                    Hulu Sungai Utara
                </span>
            </h1>

            @if($subtitle)
            <!-- Subtitle with slide up animation -->
            <p class="text-white/90 text-lg sm:text-xl max-w-3xl mx-auto
                      animate-slide-up opacity-0 animation-delay-1200
                      hover:text-white transition-colors duration-300">
                {{ $subtitle }}
            </p>
            @endif
        </div>
    </div>

    <style>
        /* Custom Keyframe Animations */
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleGlow {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                text-shadow: 0 0 5px rgba(94, 234, 212, 0.3);
            }
            50% {
                text-shadow: 0 0 20px rgba(94, 234, 212, 0.6), 0 0 30px rgba(94, 234, 212, 0.3);
            }
        }

        /* Animation Classes */
        .animate-fade-in-left {
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease-out forwards;
        }

        .animate-slide-up {
            animation: slideUp 0.8s ease-out forwards;
        }

        .animate-scale-glow {
            animation: scaleGlow 0.8s ease-out forwards, pulse-glow 3s ease-in-out infinite 1.5s;
        }

        /* Animation Delays */
        .animation-delay-300 {
            animation-delay: 0.3s;
        }

        .animation-delay-600 {
            animation-delay: 0.6s;
        }

        .animation-delay-900 {
            animation-delay: 0.9s;
        }

        .animation-delay-1200 {
            animation-delay: 1.2s;
        }

        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in-left,
            .animate-fade-in-right,
            .animate-slide-up,
            .animate-scale-glow {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }
    </style>
</section>
