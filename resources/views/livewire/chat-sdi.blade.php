{{-- resources/views/livewire/chat-sdi.blade.php --}}
<div x-data="chatBox({{ Js::from(!empty($history)) }})" x-init="init" class="chat-shell">

  {{-- WELCOME (muncul hanya jika belum ada chat & tidak streaming) --}}
  <div class="chat-welcome" x-show="!hasAnyChat">
    <h4 class="fw-bold mb-2 text-center">Selamat datang di AI Assistant!</h4>
    <p class="text-muted text-center mb-3">Tanyakan data daerah dan dapatkan analisis yang akurat.</p>
    <div class="row g-3 justify-content-center">
      @php $prompts = [
        'Bagaimana tren pertumbuhan penduduk 2019–2024?',
        'Buatkan analisis perkembangan ekonomi daerah 5 tahun terakhir.',
        'Visualisasikan data kemiskinan dalam bentuk grafik.',
        'Bandingkan PDRB antar kecamatan.'
      ]; @endphp
      @foreach($prompts as $p)
        <div class="col-12 col-md-6">
          <button type="button" class="btn btn-light w-100 py-3 shadow-sm"
                  @click="$wire.input={{ Js::from($p) }}; $wire.startStream(); hasAnyChat = true;">
            {{ $p }}
          </button>
        </div>
      @endforeach
    </div>
  </div>

  {{-- CHAT BODY (muncul saat ada chat/streaming) --}}
  <div class="chat-body" x-ref="scrollArea" x-show="hasAnyChat">

    {{-- Riwayat permanen --}}
    @foreach($history as $i => $msg)
      @php $isUser = ($msg['role'] === 'user'); @endphp

      <div class="chat-msg {{ $isUser ? 'user' : 'ai' }}">
        <div class="chat-bubble {{ $isUser ? 'user' : 'ai' }}">
          <div class="role-tag">{{ $isUser ? 'USER' : 'ASISTEN' }}</div>

          {{-- teks utama --}}
          <div class="content">{!! nl2br(e($msg['content'])) !!}</div>

          {{-- sumber (bisa banyak) --}}
          @if(!$isUser && !empty($msg['sources']))
            <div class="sources mt-1">
              <small class="text-muted">Sumber:
                @foreach($msg['sources'] as $k => $s)
                  <a href="{{ $s['url'] ?? '#' }}" target="_blank">
                    {{ $s['title'] ?? ($s['url'] ?? 'sumber') }}
                  </a>@if($k < count($msg['sources'])-1), @endif
                @endforeach
              </small>
            </div>
          @endif

          {{-- INSIGHTS (opsional) --}}
          @if(!$isUser && !empty($msg['insights']))
            <ul class="insights mt-2 ps-4 text-sm">
              @foreach($msg['insights'] as $bullet)
                <li>{{ $bullet }}</li>
              @endforeach
            </ul>
          @endif

          {{-- VISUALISASI: dukung single atau banyak grafik --}}
          @php
            // viz bisa object atau array
            $vizRaw  = $msg['viz'] ?? [];
            $vizList = isset($vizRaw[0]) ? $vizRaw : (empty($vizRaw) ? [] : [$vizRaw]);

            // data_preview bisa langsung array baris,
            // atau array of { source, rows:[…] }
            $previewRaw = $msg['data_preview'] ?? [];
          @endphp

          @foreach($vizList as $vi => $vz)
            @php
              $chartId = 'chart-'.$i.'-'.$vi;

              // Ambil rows untuk grafik ini:
              $rows = $previewRaw;
              // jika per-sumber: [{source, rows:[…]}] → ambil index yang sesuai
              if (is_array($previewRaw) && isset($previewRaw[$vi]) && isset($previewRaw[$vi]['rows'])) {
                $rows = $previewRaw[$vi]['rows'];
              }
            @endphp

            <div class="chart-wrap mt-3"
                x-data="renderChart({{ Js::from($vz) }}, {{ Js::from($rows) }}, '{{ $chartId }}')"
                x-init="render">
              <canvas id="{{ $chartId }}" height="140"></canvas>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach


    {{-- Bubble sementara saat streaming --}}
    <template x-if="streaming || pending.answer.length">
      <div class="chat-msg ai">
        <div class="chat-bubble ai">
          <div class="role-tag loading">ASISTEN</div>
          <div class="content" x-text="pending.answer"></div>

          <div class="sources" x-show="pending.sources.length">
            <small class="text-muted">Sumber:
              <template x-for="(s,i) in pending.sources" :key="i">
                <span><a :href="s.url" target="_blank" x-text="s.title || s.url"></a><span x-show="i<pending.sources.length-1">, </span></span>
              </template>
            </small>
          </div>

          <div class="chart-wrap" x-show="pending.viz && pending.data.length">
            <canvas :id="pending.canvasId" height="140"></canvas>
          </div>
        </div>
      </div>
    </template>

  </div>

  {{-- INPUT BAR --}}
  <form wire:submit.prevent="startStream" class="chat-input">
    <input type="text" class="form-control" placeholder="Ketik pertanyaan…"
           wire:model.defer="input"
           @keydown.enter.prevent="$wire.startStream(); hasAnyChat = true;">
    <button class="btn btn-primary" type="submit" @click="hasAnyChat = true">
      <i class="bi bi-send"></i>
    </button>
    <button class="btn btn-outline-secondary" type="button" @click="stop" x-show="streaming">Stop</button>
  </form>
</div>

@push('scripts')
  <style>
    .chat-shell{display:flex;flex-direction:column;gap:10px}
    .chat-welcome{padding:18px 6px}
    .chat-body{
      display:flex;flex-direction:column;gap:12px;
      min-height:260px;max-height:52vh;overflow:auto;
      background:var(--bs-body-bg);border:1px solid var(--bs-border-color);
      border-radius:.75rem;padding:12px
    }
    .chat-msg{display:flex;width:100%}
    .chat-msg.user{justify-content:flex-end}
    .chat-msg.ai{justify-content:flex-start}
    .chat-bubble{
      display:inline-block;max-width:78%;
      padding:12px 14px;border-radius:16px;
      box-shadow:0 1px 2px rgba(0,0,0,.05)
    }
    .chat-bubble.user{
      background:#0d6efd;color:#fff;border-bottom-right-radius:6px
    }
    .chat-bubble.ai{
      background:#f6f7fb;border-bottom-left-radius:6px
    }
    .role-tag{font-size:.72rem;font-weight:700;opacity:.7;margin-bottom:4px}
    .content{white-space:pre-wrap;line-height:1.55}
    .sources{margin-top:6px}
    .chart-wrap{margin-top:10px}
    .chat-input{display:flex;gap:8px;align-items:center}
    .chat-input .form-control{border-radius:.75rem}
    @media (prefers-color-scheme: dark){
      .chat-bubble.ai{background:#1f2330;color:#e8ecf7}
      .chat-body{background:#0f1320}
    }

    /* animasi titik tiga */
    .role-tag.loading::after {
      content: '...';
      animation: dots 1.5s steps(3, end) infinite;
    }

    @keyframes dots {
      0%   { content: ''; }
      33%  { content: '.'; }
      66%  { content: '..'; }
      100% { content: '...'; }
    }
  </style>


  <script>
  function encodeHistoryBase64(history){ try{ return btoa(unescape(encodeURIComponent(JSON.stringify(history)))); }catch{ return ''; } }

  // Render chart untuk bubble permanen
  window.renderChart = (viz, data, canvasId) => ({
    chart:null,
    render(){
      const el=document.getElementById(canvasId);
      if(!el || !viz || !Array.isArray(data) || !data.length) return;
      const labels=data.map(d=>d[viz.x]);
      const datasets=(viz.y||[]).map(y=>({label:y,data:data.map(d=>d[y])}));
      if(this.chart) this.chart.destroy();
      this.chart=new Chart(el,{type:viz.type||'line',data:{labels,datasets},
        options:{responsive:true,plugins:{title:{display:!!viz?.options?.title,text:viz?.options?.title||''}}}});
    }
  });

  // Controller chat (Alpine)
  window.chatBox = (hasHistoryInit=false)=>({
    es:null, streaming:false, hasAnyChat:hasHistoryInit,
    pending:{answer:'',sources:[],viz:null,data:[],canvasId:'pendingChart'},

    init(){
      // Mulai SSE dari Livewire
      window.addEventListener('chat-start',(e)=>{
        const msg=e.detail?.message||''; const h=e.detail?.history||[];
        if(!msg) return; this.start(msg,h); this.hasAnyChat=true;
      });
      // Tutup stream jika modal ditutup
      window.addEventListener('chat-modal-closed', this.stop.bind(this));
    },

    start(message,history){
      this.stop(); this.streaming=true;
      this.pending={answer:'',sources:[],viz:null,data:[],canvasId:'pendingChart'};
      this.$nextTick(()=>this.scrollBottom());

      const base=window.SDI_STREAM_URL || '/api/chatbot/stream';
      const h=encodeHistoryBase64(history||[]);
      const url=`${base}?message=${encodeURIComponent(message)}${h?('&h='+encodeURIComponent(h)):""}`;
      this.es=new EventSource(url);

      this.es.addEventListener('delta',ev=>{
        const {text}=JSON.parse(ev.data||'{}');
        if(typeof text==='string'){ this.pending.answer+=text; this.scrollBottom(); }
      });

      this.es.addEventListener('final', async (ev) => {
        let data = {};
        try { data = JSON.parse(ev.data || '{}'); } catch (e) { console.error('parse final', e); }

        // 1) viz -> array
        const viz = Array.isArray(data.viz) ? data.viz : (data.viz ? [data.viz] : []);

        // 2) data_preview -> dukung 2 bentuk:
        //    a) array of {source, rows: [...]}
        //    b) array of rows (flat)
        let preview = data.data_preview ?? [];
        if (!Array.isArray(preview)) preview = [];
        if (preview.length && !('rows' in (preview[0] || {}))) {
          // flat → bungkus sebagai satu sumber
          preview = [{ rows: preview }];
        }

        // 3) sematkan headline bila ada
        const content = data.headline
          ? `${data.headline}\n\n${data.answer || ''}`
          : (data.answer || '');

        await this.$wire.appendAssistant(
          content,
          data.sources || [],
          viz,
          preview,
          data.insights || []
        );

        // bereskan state UI
        this.streaming = false; // jika Anda pakai flag ini
        this.es?.close(); this.es = null;
        this.$nextTick(() => this.scrollBottom());
      });


      this.es.addEventListener('error',()=>{ this.streaming=false; this.es?.close(); this.es=null; });
    },

    stop(){ if(this.es){ this.es.close(); this.es=null; } this.streaming=false; },

    scrollBottom(){ const box=this.$refs.scrollArea; if(!box) return; box.scrollTop=box.scrollHeight; }
  });
  </script>
@endpush
