{{-- resources/views/components/public/publications/pdfjs-viewer.blade.php --}}
@props([
'src', // URL PDF, contoh: asset('storage/'.$publication->file_path) atau route preview
'title' => '',
])

@php
$viewerId = $attributes->get('id') ?: 'pdfjs-'.\Illuminate\Support\Str::uuid();
@endphp

<div id="{{ $viewerId }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm
            dark:border-gray-700 dark:bg-gray-900 transition-colors duration-200" data-src="{{ $src }}">
  {{-- Toolbar --}}
  <div class="px-4 sm:px-5 py-3 border-b border-gray-200 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      {{-- Judul --}}
      <div class="min-w-0">
        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</p>
      </div>

      {{-- Controls (wrap di mobile) --}}
      <div class="flex flex-wrap items-center gap-x-1.5 gap-y-2 sm:justify-end">
        {{-- Zoom --}}
        <button type="button" data-action="zoom-out" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                       sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                       focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
          −
        </button>

        <span data-role="zoom-label"
          class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-300 w-9 sm:w-10 text-center select-none">100%</span>

        <button type="button" data-action="zoom-in" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                       sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                       focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
          +
        </button>

        <span class="hidden sm:inline mx-2 h-4 w-px bg-gray-200 dark:bg-gray-700"></span>

        {{-- Navigasi halaman --}}
        <button type="button" data-action="prev" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                       sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                       focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
          aria-label="Halaman sebelumnya">
          <span class="sm:hidden">‹</span><span class="hidden sm:inline">Prev</span>
        </button>

        <div class="inline-flex items-center gap-1">
          <input data-role="page-input" type="number" min="1" value="1" class="w-12 sm:w-16 rounded-md border-gray-300 text-[11px] sm:text-xs
                        focus:ring-teal-600 focus:border-teal-600
                        dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" aria-label="Nomor halaman">
          <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">/ <span
              data-role="page-count">1</span></span>
        </div>

        <button type="button" data-action="next" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                       sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                       focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
          aria-label="Halaman berikutnya">
          <span class="sm:hidden">›</span><span class="hidden sm:inline">Next</span>
        </button>

        <span class="hidden sm:inline mx-2 h-4 w-px bg-gray-200 dark:bg-gray-700"></span>

        {{-- Fit width: tampil ≥ md --}}
        <button type="button" data-action="fit-width" class="hidden md:inline-flex items-center rounded-full border border-gray-300 bg-white
                       px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50
                       focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
          Fit width
        </button>

        {{-- Buka Tab: ikon di mobile, teks di ≥sm --}}
        <a href="{{ $src }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                  sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                  focus-visible:ring-2 focus-visible:ring-teal-600
                  dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
          <span class="sm:hidden">↗︎</span><span class="hidden sm:inline">Buka Tab</span>
        </a>
      </div>
    </div>
  </div>

  {{-- Placeholder area (sebelum dimuat) --}}
  <div class="relative">
    <div
      class="h-[70vh] md:h-[78vh] bg-gray-50 dark:bg-gray-900 grid place-content-center text-center transition-colors duration-200">
      <div class="space-y-2">
        <div
          class="mx-auto h-10 w-10 rounded-full border-2 border-gray-300 dark:border-gray-600 border-t-transparent animate-spin">
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">Menunggu perintah untuk memuat PDF…</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Tekan tombol <span class="font-semibold">Baca PDF</span> di atas untuk mulai memuat.
        </p>
      </div>
    </div>
  </div>

  {{-- Script init saat menerima event "init-pdf-viewer" --}}
  <script>
    (function(){
      const root = document.getElementById(@json($viewerId));
      if (!root) return;

      function ensurePdfJs(){
        if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
        if (window.__pdfjsLoading) return window.__pdfjsLoading;
        window.__pdfjsLoading = new Promise((resolve, reject) => {
          const s = document.createElement('script');
          s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js';
          s.async = true;
          s.onload = () => {
            try {
              window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
              resolve(window.pdfjsLib);
            } catch(e){ reject(e); }
          };
          s.onerror = reject;
          document.head.appendChild(s);
        });
        return window.__pdfjsLoading;
      }

      function initViewer(){
        if (root.dataset.__inited) return;
        root.dataset.__inited = '1';

        const src = root.getAttribute('data-src');

        // Markup viewer penuh dengan varian dark:
        root.innerHTML = `
      <div class="px-4 sm:px-5 py-3 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</p>
          </div>

          <div class="flex flex-wrap items-center gap-x-1.5 gap-y-2 sm:justify-end">
            <button type="button" data-action="zoom-out"
              class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                     sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
              aria-label="Perkecil">−</button>

            <span data-role="zoom-label"
              class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-300 w-9 sm:w-10 text-center select-none">100%</span>

            <button type="button" data-action="zoom-in"
              class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                     sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
              aria-label="Perbesar">+</button>

            <span class="hidden sm:inline mx-2 h-4 w-px bg-gray-200 dark:bg-gray-700"></span>

            <button type="button" data-action="prev"
              class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                     sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
              aria-label="Halaman sebelumnya">
              <span class="sm:hidden">‹</span><span class="hidden sm:inline">Prev</span>
            </button>

            <div class="inline-flex items-center gap-1">
              <input data-role="page-input" type="number" min="1" value="1"
                class="w-12 sm:w-16 rounded-md border-gray-300 text-[11px] sm:text-xs
                       focus:ring-teal-600 focus:border-teal-600
                       dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                aria-label="Nomor halaman">
              <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">/ <span data-role="page-count">1</span></span>
            </div>

            <button type="button" data-action="next"
              class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                     sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80"
              aria-label="Halaman berikutnya">
              <span class="sm:hidden">›</span><span class="hidden sm:inline">Next</span>
            </button>

            <span class="hidden sm:inline mx-2 h-4 w-px bg-gray-200 dark:bg-gray-700"></span>

            <button type="button" data-action="fit-width"
              class="hidden md:inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
              Fit width
            </button>

            <a href="${src}" target="_blank" rel="noopener"
              class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-[11px]
                     sm:px-3 sm:py-1.5 sm:text-xs font-semibold text-gray-800 hover:bg-gray-50
                     focus-visible:ring-2 focus-visible:ring-teal-600
                     dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80">
              <span class="sm:hidden">↗︎</span><span class="hidden sm:inline">Buka Tab</span>
            </a>
          </div>
        </div>
      </div>

      <div class="relative">
        <div data-role="spinner" class="absolute inset-0 grid place-content-center">
          <div class="h-8 w-8 animate-spin rounded-full border-2 border-teal-600 border-t-transparent dark:border-teal-400 dark:border-t-transparent"></div>
        </div>
        <div class="md:grid md:grid-cols-12">
          <aside class="hidden md:block md:col-span-3 lg:col-span-2 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 h-[70vh] md:h-[78vh] overflow-y-auto transition-colors duration-200">
            <div data-role="thumbs" class="p-3 space-y-3"></div>
          </aside>
          <div class="md:col-span-9 lg:col-span-10 h-[70vh] md:h-[78vh] overflow-auto bg-gray-50 dark:bg-gray-900 transition-colors duration-200" data-role="scroll">
            <div data-role="pages" class="py-4 sm:py-6"></div>
          </div>
        </div>
      </div>
        `;

        const $ = sel => root.querySelector(sel);
        const spinner   = $('[data-role="spinner"]');
        const pagesEl   = $('[data-role="pages"]');
        const scrollEl  = $('[data-role="scroll"]');
        const thumbsEl  = $('[data-role="thumbs"]');
        const zoomLabel = $('[data-role="zoom-label"]');
        const pageInput = $('[data-role="page-input"]');
        const pageCountEl = $('[data-role="page-count"]');

        const btnPrev    = root.querySelector('[data-action="prev"]');
        const btnNext    = root.querySelector('[data-action="next"]');
        const btnZoomIn  = root.querySelector('[data-action="zoom-in"]');
        const btnZoomOut = root.querySelector('[data-action="zoom-out"]');
        const btnFitWidth= root.querySelector('[data-action="fit-width"]');

        const LS_KEY = 'pdfjs-zoom:'+src;
        let pdfDoc = null;
        let scale  = Number(localStorage.getItem(LS_KEY) || 1); scale = Math.max(0.5, Math.min(scale, 3));
        let currentPage = 1;

        const DPR = Math.min(window.devicePixelRatio || 1, 1.75);
        const clamp = (v,a,b)=> Math.max(a, Math.min(b, v));
        const updZoomLabel = ()=> zoomLabel.textContent = Math.round(scale*100)+'%';

        function makePage(id){
          const wrap = document.createElement('div'); wrap.className='mb-4 sm:mb-6'; wrap.dataset.page=id;
          // Kanvas sengaja dibiarkan putih agar kontras tetap baik di dark mode
          const cv = document.createElement('canvas'); cv.className='mx-auto block bg-white shadow-sm rounded-lg'; cv.width=800; cv.height=1130;
          wrap.appendChild(cv); return {wrap, cv};
        }
        function makeThumb(id){
          const btn = document.createElement('button'); btn.type='button'; btn.className='w-full block text-left group'; btn.dataset.page=id;
          const cv = document.createElement('canvas'); cv.className='w-full border border-gray-200 dark:border-gray-700 rounded-md bg-white shadow-sm group-focus:outline-none';
          btn.appendChild(cv);
          const label = document.createElement('div'); label.className='mt-1 text-[10px] text-gray-600 dark:text-gray-300 text-center'; label.textContent='Hal. '+id;
          btn.appendChild(label);
          btn.addEventListener('click', ()=>{
            const target = pagesEl.querySelector('[data-page="'+id+'"]');
            if (target) target.scrollIntoView({behavior:'smooth', block:'start'});
          });
          return {btn, cv};
        }

        async function renderPage(pageNum, canvas, _scale=scale){
          const page = await pdfjsLib.getDocument(src).promise.then(doc => (pdfDoc=doc, doc.getPage(pageNum)));
          const base = (await page).getViewport({scale:1});
          if (pageNum===1 && Number(localStorage.getItem(LS_KEY)||1)===scale && scrollEl.clientWidth>680){
            scale = clamp((scrollEl.clientWidth-24)/base.width, 0.5, 3); localStorage.setItem(LS_KEY,String(scale)); updZoomLabel();
          }
          const viewport = (await page).getViewport({scale:_scale});
          canvas.width  = Math.floor(viewport.width  * DPR);
          canvas.height = Math.floor(viewport.height * DPR);
          canvas.style.width  = Math.floor(viewport.width)+'px';
          canvas.style.height = Math.floor(viewport.height)+'px';
          const ctx = canvas.getContext('2d');
          await (await page).render({canvasContext: ctx, viewport, transform: DPR!==1?[DPR,0,0,DPR,0,0]:null}).promise;
        }

        async function renderThumb(pageNum, cv){
          const page = await pdfDoc.getPage(pageNum);
          const base = page.getViewport({scale:1});
          const s = 120/base.width;
          const v = page.getViewport({scale:s});
          cv.width = Math.floor(v.width); cv.height = Math.floor(v.height);
          await page.render({ canvasContext: cv.getContext('2d'), viewport: v }).promise;
        }

        function highlightThumb(n){
          thumbsEl.querySelectorAll('button').forEach(b=>b.classList.remove('ring','ring-teal-500','dark:ring-teal-400'));
          const cur = thumbsEl.querySelector('button[data-page="'+n+'"]');
          if (cur) { cur.classList.add('ring','ring-teal-500'); if (document.documentElement.classList.contains('dark')) cur.classList.add('dark:ring-teal-400'); }
        }

        const io = new IntersectionObserver(entries=>{
          entries.forEach(async e=>{
            if (!e.isIntersecting) return;
            const wrap = e.target, id = Number(wrap.dataset.page);
            const cv = wrap.querySelector('canvas');
            if (!cv.dataset.rendered){
              await renderPage(id, cv, scale); cv.dataset.rendered='1';
            }
          });
        }, {root: scrollEl, rootMargin:'200px 0px', threshold:0.01});

        function rebuild(numPages){
          pagesEl.innerHTML=''; thumbsEl.innerHTML='';
          for (let i=1;i<=numPages;i++){
            const {wrap, cv} = makePage(i); pagesEl.appendChild(wrap); io.observe(wrap);
            const {btn, cv:tcv} = makeThumb(i); thumbsEl.appendChild(btn); setTimeout(()=> renderThumb(i, tcv), i*30);
          }
        }

        function syncCurrentOnScroll(){
          const nodes=[...pagesEl.querySelectorAll('[data-page]')];
          let best=1, bestTop=Infinity, top=scrollEl.scrollTop;
          nodes.forEach(el=>{ const d=Math.abs(el.offsetTop-top); if(d<bestTop){bestTop=d; best=Number(el.dataset.page);} });
          if(best!==currentPage){ currentPage=best; pageInput.value=currentPage; highlightThumb(currentPage); }
        }

        let zoomTimer=null;
        function rerenderVisible(){
          clearTimeout(zoomTimer);
          zoomTimer=setTimeout(()=>{
            const nodes=pagesEl.querySelectorAll('[data-page]');
            nodes.forEach(el=>{
              const r=el.getBoundingClientRect();
              if(r.bottom>0 && r.top<window.innerHeight){
                const cv=el.querySelector('canvas');
                renderPage(Number(el.dataset.page), cv, scale).then(()=> cv.dataset.rendered='1');
              }
            });
          },80);
        }

        function clampPage(n){ return Math.max(1, Math.min(n, pdfDoc.numPages)); }
        function zoomIn(){ scale=Math.min(scale+0.1,3); localStorage.setItem(LS_KEY,String(scale)); updZoomLabel(); rerenderVisible(); }
        function zoomOut(){ scale=Math.max(scale-0.1,0.5); localStorage.setItem(LS_KEY,String(scale)); updZoomLabel(); rerenderVisible(); }
        function fitWidth(){
          pdfDoc.getPage(currentPage).then(page=>{
            const base = page.getViewport({scale:1});
            scale = Math.max(0.3, Math.min((scrollEl.clientWidth-24)/base.width, 5));
            localStorage.setItem(LS_KEY,String(scale)); updZoomLabel(); rerenderVisible();
          });
        }
        function prev(){ const n=clampPage(currentPage-1); pagesEl.querySelector('[data-page="'+n+'"]').scrollIntoView({behavior:'smooth'}); }
        function next(){ const n=clampPage(currentPage+1); pagesEl.querySelector('[data-page="'+n+'"]').scrollIntoView({behavior:'smooth'}); }
        function gotoPage(e){ const n=clampPage(parseInt(e.target.value||'1',10)); pagesEl.querySelector('[data-page="'+n+'"]').scrollIntoView({behavior:'smooth'}); }

        btnZoomIn.addEventListener('click', zoomIn);
        btnZoomOut.addEventListener('click', zoomOut);
        btnFitWidth && btnFitWidth.addEventListener('click', fitWidth);
        btnPrev.addEventListener('click', prev);
        btnNext.addEventListener('click', next);
        root.addEventListener('change', e=>{ if(e.target && e.target.matches('[data-role="page-input"]')) gotoPage(e); });
        scrollEl.addEventListener('scroll', ()=>{ syncCurrentOnScroll(); }, {passive:true});
        window.addEventListener('resize', ()=>{ clearTimeout(window.__pdfrs); window.__pdfrs=setTimeout(fitWidth,150); });

        pdfjsLib.getDocument(src).promise.then(doc=>{
          pdfDoc = doc;
          pageCountEl.textContent = doc.numPages;
          updZoomLabel();
          rebuild(doc.numPages);
          spinner.style.display='none';
          rerenderVisible();
          highlightThumb(1);
          root.dataset.__ready = '1';
          root.dispatchEvent(new CustomEvent('pdf-viewer-ready', { bubbles: true }));
        }).catch(err=>{
          spinner.innerHTML = '<p class="text-sm text-red-600 dark:text-red-400 px-4 py-3">Gagal memuat PDF.</p>';
          console.error('PDF.js error:', err);
        });
      }

      // Dengarkan event dari tombol "Baca PDF"
      root.addEventListener('init-pdf-viewer', () => {
        if (root.dataset.__inited) return;
        ensurePdfJs().then(initViewer).catch(err => {
          console.error('Gagal memuat pdf.js', err);
          alert('Gagal memuat komponen pembaca PDF.');
        });
      });
    })();
  </script>
</div>
