{{-- resources/views/livewire/chat-sdi.blade.php --}}
<div x-data="chatBox({{ Js::from(!empty($history)) }})" x-init="init" class="chat-shell bg-white dark:!bg-gray-900 text-gray-900 dark:text-white rounded-lg">

@auth

  {{-- WELCOME (muncul hanya jika belum ada chat & tidak streaming) --}}
  <div class="chat-welcome bg-white dark:!bg-gray-900" x-show="!hasAnyChat">
    <h4 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-white">Selamat datang di AI Assistant!</h4>
    <p class="text-gray-600 dark:text-gray-300 text-center mb-6">Tanyakan data daerah dan dapatkan analisis yang akurat.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 justify-center">
      @php $prompts = [
        'Bagaimana tren pertumbuhan penduduk 2019–2024?',
        'Buatkan analisis perkembangan ekonomi daerah 5 tahun terakhir.',
        'Visualisasikan data kemiskinan dalam bentuk grafik.',
        'Bandingkan PDRB antar kecamatan.'
      ]; @endphp
      @foreach($prompts as $p)
        <div>
          <button type="button" class="w-full py-3 px-4 bg-gray-50 hover:bg-gray-100 dark:!bg-gray-800 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg shadow-sm transition-colors duration-200 text-left"
                  @click="$wire.input={{ Js::from($p) }}; $wire.startStream(); hasAnyChat = true;">
            {{ $p }}
          </button>
        </div>
      @endforeach
    </div>
  </div>

  {{-- CHAT BODY (muncul saat ada chat/streaming) --}}
  <div class="chat-body rounded-lg" x-ref="scrollArea" x-show="hasAnyChat">

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
              <small class="text-gray-500 dark:text-gray-400">Sumber:
                @foreach($msg['sources'] as $k => $s)
                  <a href="{{ $s['url'] ?? '#' }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline">
                    {{ $s['title'] ?? ($s['url'] ?? 'sumber') }}
                  </a>@if($k < count($msg['sources'])-1), @endif
                @endforeach
              </small>
            </div>
          @endif

          {{-- INSIGHTS (opsional) --}}
          @if(!$isUser && !empty($msg['insights']))
            <ul class="insights mt-2 ps-4 text-sm text-gray-700 dark:text-gray-300">
              @foreach($msg['insights'] as $bullet)
                <li class="mb-1">{{ $bullet }}</li>
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
            <small class="text-gray-500 dark:text-gray-400">Sumber:
              <template x-for="(s,i) in pending.sources" :key="i">
                <span><a :href="s.url" target="_blank" x-text="s.title || s.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline"></a><span x-show="i<pending.sources.length-1">, </span></span>
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
  <form wire:submit.prevent="startStream" class="chat-input w-full bg-white dark:!bg-gray-900">
    <div class="flex w-full gap-2 items-stretch rounded-lg">
      <input type="text" class="flex-1 min-w-0 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:!bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ketik pertanyaan…"
             wire:model.defer="input"
             @keydown.enter.prevent="$wire.startStream(); hasAnyChat = true;">
      <button class="px-4 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center" type="submit" @click="hasAnyChat = true">
        <i class="bi bi-send"></i>
      </button>
      <button class="px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-lg transition-colors duration-200 border border-gray-300 dark:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center" type="button" @click="stop" x-show="streaming">Stop</button>
    </div>
  </form>

@else
  <div class="p-6 text-center">
    <h3 class="text-xl font-semibold mb-4">Masuk untuk menggunakan AI Assistant</h3>
    <p class="mb-4 text-gray-600 dark:text-gray-300">Silakan login terlebih dahulu untuk memulai chat dengan OpenAI.</p>
    <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">Login via Portal HSU</a>
  </div>
@endauth

</div>

