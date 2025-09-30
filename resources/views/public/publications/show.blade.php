{{-- resources/views/public/publications/show.blade.php --}}
<x-layouts.public :title="$publication->title">
    <section class="pt-6 pb-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header + aksi --}}
            <x-public.publications.header :publication="$publication" />

            {{-- Grid konten (3/9) --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">
                {{-- Panel Info (kiri di desktop) --}}
                <aside class="order-2 md:order-1 md:col-span-3">
                    <x-public.publications.info-panel :publication="$publication" :file-size="$fileSize" />
                </aside>

                {{-- Konten utama (kanan) --}}
                <div class="order-1 md:order-2 md:col-span-9">
                    {{-- Abstrak / ringkasan (CSS line-clamp; tanpa JS) --}}
                    @if(filled($publication->abstract))
                    <article class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sm:p-6
                                    dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                            Abstrak
                        </h2>

                        {{-- Mobile: 6 baris --}}
                        <div class="sm:hidden mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            <div
                                style="-webkit-line-clamp:6;-webkit-box-orient:vertical;display:-webkit-box;overflow:hidden;">
                                {{ $publication->abstract }}
                            </div>
                        </div>

                        {{-- ≥sm: 10 baris --}}
                        <div class="hidden sm:block mt-2 text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                            <div
                                style="-webkit-line-clamp:10;-webkit-box-orient:vertical;display:-webkit-box;overflow:hidden;">
                                {{ $publication->abstract }}
                            </div>
                        </div>
                    </article>
                    @endif

                    {{-- Card kecil Baca PDF --}}
                    @if($publication->file_path)
                    <div id="read-card" class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sm:p-5
                                dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
                        <div class="flex items-center justify-between gap-3">
                            <button id="btn-read-pdf-card" type="button" class="inline-flex items-center gap-2 rounded-full bg-teal-600 px-4 py-2
                                           text-white text-sm font-semibold hover:bg-teal-700 focus-visible:ring-2
                                           focus-visible:ring-teal-600 dark:hover:bg-teal-600/90">
                                Baca PDF
                            </button>

                            <a href="{{ asset('storage/'.$publication->file_path) }}" target="_blank" rel="noopener"
                                class="text-xs sm:text-sm text-gray-600 hover:text-gray-800 hover:underline
                                      dark:text-gray-300 dark:hover:text-gray-200 transition-colors duration-200">
                                Buka di tab baru
                            </a>
                        </div>
                    </div>

                    {{-- Viewer (klik salah satu tombol untuk menampilkan & inisialisasi) --}}
                    <div id="viewer" class="mt-3 hidden rounded-2xl border border-gray-200 bg-white
                                           dark:border-gray-700 dark:bg-gray-900 transition-colors duration-200">
                        <x-public.publications.pdfjs-viewer id="pdf-viewer"
                            :src="asset('storage/'.$publication->file_path)" :title="$publication->title" />
                    </div>
                    @endif

                    {{-- Trigger JS: tombol header + tombol card, dengan toggle di tombol card --}}
                    @if($publication->file_path)
                    <script>
                        (function() {
                            // Elemen
                            const root = document.getElementById('pdf-viewer');
                            const viewerEl = document.getElementById('viewer');
                            const btnHdr = document.getElementById('btn-read-pdf-header');
                            const btnCard = document.getElementById('btn-read-pdf-card');
                            const card = document.getElementById('read-card');

                            if (!root || !viewerEl) return;

                            // State
                            let initializing = false;
                            let ready = root.dataset.__ready === '1';
                            let open = !viewerEl.classList.contains('hidden');

                            // Helper: set varian tombol card (primary/secondary) dgn dukungan dark mode
                            function setBtnCardVariant(primary) {
                                if (!btnCard) return;

                                // Reset kelas yang mungkin saling bertentangan dulu
                                btnCard.classList.remove(
                                    'bg-teal-600','text-white','hover:bg-teal-700','dark:hover:bg-teal-600/90',
                                    'border','border-gray-300','bg-white','text-gray-800',
                                    'dark:border-gray-700','dark:bg-gray-800','dark:text-gray-100'
                                );

                                if (primary) {
                                    // Primary (teal solid)
                                    btnCard.classList.add(
                                        'bg-teal-600','text-white','hover:bg-teal-700','dark:hover:bg-teal-600/90'
                                    );
                                } else {
                                    // Secondary (ghost/outline yang ramah dark)
                                    btnCard.classList.add(
                                        'border','border-gray-300','bg-white','text-gray-800',
                                        'dark:border-gray-700','dark:bg-gray-800','dark:text-gray-100'
                                    );
                                }
                            }

                            // Update label dan style tombol card
                            function updateBtnCardLabel() {
                                if (!btnCard) return;
                                btnCard.textContent = open ? 'Tutup Viewer' : 'Baca PDF';
                                // Saat viewer terbuka, tombol menjadi sekunder
                                setBtnCardVariant(!open);
                            }

                            // Loading state (hanya efek visual)
                            function setLoading(on) {
                                const apply = (btn, baseLabel) => {
                                    if (!btn) return;
                                    btn.textContent = on ? 'Memuat…' : baseLabel;
                                    btn.classList.toggle('opacity-80', on);
                                    btn.classList.toggle('cursor-wait', on);
                                };
                                apply(btnHdr, 'Baca di Viewer');
                                apply(btnCard, open ? 'Tutup Viewer' : 'Baca PDF');
                            }

                            // Tampilkan viewer PDF
                            function showViewer(init = true) {
                                viewerEl.classList.remove('hidden');
                                open = true;
                                updateBtnCardLabel();

                                // Scroll halus ke viewer
                                viewerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

                                // Inisialisasi PDF.js jika belum ready
                                if (init && !ready && !initializing) {
                                    initializing = true;
                                    setLoading(true);
                                    root.dispatchEvent(new Event('init-pdf-viewer'));
                                }
                            }

                            // Sembunyikan viewer PDF
                            function hideViewer() {
                                viewerEl.classList.add('hidden');
                                open = false;
                                updateBtnCardLabel();

                                // Scroll balik ke card tombol
                                if (card) {
                                    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            }

                            // Event dari tombol header
                            btnHdr && btnHdr.addEventListener('click', () => {
                                open ? hideViewer() : showViewer(true);
                            });

                            // Event dari tombol card (toggle)
                            btnCard && btnCard.addEventListener('click', () => {
                                open ? hideViewer() : showViewer(true);
                            });

                            // Viewer siap
                            root.addEventListener('pdf-viewer-ready', () => {
                                ready = true;
                                initializing = false;
                                setLoading(false);
                                updateBtnCardLabel();
                            });

                            // Sinkronkan label bila reload dan viewer sudah siap
                            if (ready) updateBtnCardLabel();
                        })();
                    </script>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
