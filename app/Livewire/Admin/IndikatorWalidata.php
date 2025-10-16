<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Models\Walidata;
use App\Models\Skpd;
use App\Models\Aspek;
use App\Models\Indikator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Bidang;

#[Title('Walidata')]
class IndikatorWalidata extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public string $walidata_id = '';
    public string $deleteId = '';
    public ?Walidata $editingWalidata = null;

    // Fields
    public string $satuan = '';
    public string $tahun = '';          // disimpan string (regex 4 digit)
    public string $data = '';
    public ?string $skpd_id = null;     // string/uuid char
    public ?string $aspek_id = null;
    public ?string $indikator_id = null;
    public ?string $bidang_id = null;
    public string $nama = '';
    public string $indikatorSearch = '';

    public int $perPage = 10;

    public $availableSkpds = [];
    public $availableAspeks = [];
    public $availableIndikators = [];
    public $availableBidangs = [];

    protected array $messages = [
        'tahun.regex' => 'Tahun harus 4 digit.',
        'skpd_id.exists' => 'Produsen Data tidak valid.',
        'aspek_id.exists' => 'Aspek tidak valid.',
        'indikator_id.exists' => 'Indikator tidak valid.',
        'bidang_id.exists' => 'Bidang tidak valid.',
    ];

    protected function rules(): array
    {
        return [
            'satuan'       => 'required|string|min:2',
            'tahun'        => 'required|string|regex:/^[0-9]{4}$/',
            'data'         => 'required|string',
            'skpd_id'      => 'nullable|exists:skpd,id',
            'aspek_id'     => 'nullable|exists:aspeks,id',
            'indikator_id' => 'nullable|exists:indikators,id',
            'bidang_id'    => 'nullable|exists:bidangs,id',
        ];
    }

    public function mount(): void
    {
        // Dropdown master
        if (auth()->user()->hasRole('produsen data')) {
            $this->availableSkpds = Skpd::orderBy('nama')
            ->whereColumn('id', 'unor_induk_id')
            ->where('id', auth()->user()->skpd_uuid)
            ->get();
        }
        else {
            $this->availableSkpds = Skpd::orderBy('nama')
            ->whereColumn('id', 'unor_induk_id')
            ->get();
        }
        $this->availableAspeks     = Aspek::orderBy('nama')->get();
        $this->loadIndikators();
        $this->availableBidangs    = Bidang::orderBy('kode_bidang')->get();

        // Lock Produsen Data untuk role 'produsen data'
        if (auth()->user()->hasRole('produsen data')) {
            $this->skpd_id = auth()->user()->skpd_uuid ?? null;
        }
    }

    public function render()
    {
        $query = Walidata::with(['skpd', 'aspek', 'indikator', 'bidang'])
            ->when($this->search !== '', function ($q) {
                // Postgres-friendly search
                $term = "%{$this->search}%";
                $q->where(function ($x) use ($term) {
                    $x->where('satuan', 'ilike', $term)
                      ->orWhere('tahun', 'ilike', $term)
                      ->orWhere('tahun', 'ilike', $term)
                      ->orWhere('data',  'ilike', $term);
                })
                ->orWhereHas('indikator', function ($s) use ($term) {
                    $s->where('uraian_indikator', 'ilike', $term);
                })
                ->orWhereHas('bidang', function ($s) use ($term) {
                    $s->where('uraian_bidang', 'ilike', $term);
                })
                ->orWhereHas('skpd', function ($s) use ($term) {
                    $s->where('singkatan', 'ilike', $term)
                    ->orWhere('nama', 'ilike', $term);
                });
            });

        // Batasi user biasa ke Produsen Data miliknya
        if (auth()->user()->hasRole('produsen data')) {
            $query->where('skpd_id', auth()->user()->skpd_uuid);
        }

        $walidatas = $query
            ->orderBy('verifikasi_data', 'desc')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.admin.indikator-walidata', compact('walidatas'));
    }

    private function resetInput(): void
    {
        $this->reset([
            'walidata_id', 'satuan', 'tahun', 'data',
            'aspek_id', 'indikator_id', 'bidang_id', 'indikatorSearch',
        ]);

        // jaga skpd_id tetap terkunci untuk role 'produsen data'
        if (!auth()->user()->hasRole('produsen data')) {
            $this->skpd_id = null;
        }

        // Reset indikator list to show all
        $this->loadIndikators();

        $this->editingWalidata = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();

        $this->showModal = true;
        $this->dispatch('show-modal', id: 'walidata-modal');
        
        // Update Tom Select options dengan format yang benar
        $this->js("
            setTimeout(() => {
                // Update skpd options
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'skpd_id',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . "
                    }
                }));
                
                // Update aspek options
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'aspek_id',
                        options: " . json_encode($this->availableAspeks->map(function($aspek) {
                            return ['id' => $aspek->id, 'text' => $aspek->nama];
                        })->values()) . "
                    }
                }));
                
                // Update bidang options
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'bidang_id',
                        options: " . json_encode($this->availableBidangs->map(function($bidang) {
                            return ['id' => $bidang->id, 'text' => $bidang->nama];
                        })->values()) . "
                    }
                }));
            }, 100);
        ");
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $wd = Walidata::findOrFail($id);

        $this->walidata_id  = $wd->id;
        $this->satuan       = (string) $wd->satuan;
        $this->tahun        = (string) $wd->tahun;
        $this->data         = (string) $wd->data;
        $this->skpd_id      = $wd->skpd_id;
        $this->aspek_id     = $wd->aspek_id;
        $this->indikator_id = $wd->indikator_id;
        $this->bidang_id    = $wd->bidang_id;

        // Set search field dengan nama indikator yang sedang diedit
        if ($wd->indikator_id && $wd->indikator) {
           $this->indikatorSearch = trim(($wd->indikator->kode_indikator ?? '').' - '.($wd->indikator->uraian_indikator ?? ''));
        }

        $this->editingWalidata = $wd;
        $this->showModal = true;

        $this->dispatch('show-modal', id: 'walidata-modal');
        
        // Update Tom Select options dan set selected values
        $this->js("
            setTimeout(() => {
                // Update Produsen Data options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        id: 'skpd-select',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . ",
                        value: '" . $this->skpd_id . "'
                    }
                }));
                
                // Update aspek options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        id: 'aspek-select',
                        options: " . json_encode($this->availableAspeks->map(function($aspek) {
                            return ['id' => $aspek->id, 'text' => $aspek->nama];
                        })->values()) . ",
                        value: '" . $this->aspek_id . "'
                    }
                }));
                
                // Update indikator options dengan format [{id, text}] - sama dengan template blade
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        id: 'indikator-select',
                        options: " . json_encode($this->availableIndikators->map(function($indikator) {
                            return ['id' => $indikator->id, 'text' => trim(($indikator->kode_indikator ?? '').' - '.($indikator->uraian_indikator ?? ''))];
                        })->values()) . ",
                        value: '" . $this->indikator_id . "',
                        text: '" . addslashes(trim(($wd->indikator->kode_indikator ?? '').' - '.($wd->indikator->uraian_indikator ?? ''))) . "'
                    }
                }));
                
                // Update bidang options dengan format [{id, text}] - sama dengan template blade
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        id: 'bidang-select',
                        options: " . json_encode($this->availableBidangs->map(function($bidang) {
                            return ['id' => $bidang->id, 'text' => trim(($bidang->kode_bidang ? $bidang->kode_bidang.' - ' : '').($bidang->uraian_bidang ?? ''))];
                        })->values()) . ",
                        value: '" . $this->bidang_id . "'
                    }
                }));
            }, 500);
        ");
    }

    private function normalizeIndikatorId(): void
    {
        if (blank($this->indikator_id)) {
            return;
        }

        // Tom Select seharusnya sudah mengirim UUID langsung
        // Jika sudah UUID, biarkan
        if (\Illuminate\Support\Str::isUuid($this->indikator_id)) {
            return;
        }

        // Fallback: jika somehow masih berupa string dengan format "KODE - Uraian"
        // (seharusnya tidak terjadi lagi dengan fix di updatedIndikatorId)
        if (str_contains($this->indikator_id, '-')) {
            $code = trim(strtok($this->indikator_id, '-')); // ambil bagian sebelum ' - '
            if ($code !== '') {
                $found = \App\Models\Indikator::where('kode_indikator', $code)->value('id');
                $this->indikator_id = $found ?: null;
            } else {
                $this->indikator_id = null;
            }
        } else {
            // Jika bukan UUID dan bukan format "KODE - Uraian", reset
            $this->indikator_id = null;
        }
    }

    public function saveWalidata(): void
    {
        if (auth()->user()->hasRole('produsen data')) {
            $this->skpd_id = auth()->user()->skpd_uuid ?? null;
        }

        // 1) Normalisasi indikator_id (label → UUID)
        $this->normalizeIndikatorId();

        // 2) Sinkron bidang dari indikator (hanya jika indikator_id valid UUID)
        if ($this->indikator_id && \Illuminate\Support\Str::isUuid($this->indikator_id)) {
            $this->bidang_id = optional(\App\Models\Indikator::find($this->indikator_id))->bidang_id;
        } else {
            $this->bidang_id = null;
        }

        // 3) Validasi SETELAH normalisasi & sinkron
        $validated = $this->validate();

        if ($this->walidata_id) {
            \App\Models\Walidata::findOrFail($this->walidata_id)->update($validated);
            $msg = 'Walidata diperbarui!';
        } else {
            \App\Models\Walidata::create(array_merge(
                ['id' => (string) \Illuminate\Support\Str::uuid(), 'user_id' => auth()->id()],
                $validated
            ));
            $msg = 'Walidata ditambahkan!';
        }

        $this->dispatch('swal', title: $msg, icon: 'success', toast: true, position: 'bottom-end', timer: 3000);
        $this->closeModal();
        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetInput();
        $this->dispatch('hide-modal', id: 'walidata-modal');
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
        $this->nama = Walidata::find($id)?->indikator?->uraian_indikator ?? ''; // Set uraian untuk ditampilkan di modal
        $this->dispatch('show-modal', id: 'delete-modal');
    }

    public function deleteWalidataConfirmed(): void
    {
        Walidata::findOrFail($this->deleteId)->delete();

        $this->dispatch('swal', title: 'Walidata dihapus!', icon: 'success', toast: true, position: 'bottom-end', timer: 3000);
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->showDeleteModal = false;
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->showDeleteModal = false;
    }

    public function loadIndikators(): void
    {
        $query = Indikator::orderBy('kode_indikator');
        
        if (!empty($this->indikatorSearch)) {
            $search = "%{$this->indikatorSearch}%";
            $query->where(function ($q) use ($search) {
                $q->where('kode_indikator', 'ilike', $search)
                  ->orWhere('uraian_indikator', 'ilike', $search);
            });
        }
        
        // Load more items for Tom Select search functionality
        $this->availableIndikators = $query->limit(100)->get();
    }

    public function updatedIndikatorSearch(): void
    {
        $this->loadIndikators();
        // Jangan reset selection jika user sedang mencari
        // Biarkan user memilih dari hasil pencarian
    }



    public function updatedIndikatorId($value): void
    {
        if (blank($value)) {
            $this->bidang_id = null;
            return;
        }

        // Tom Select mengirim UUID langsung, tidak perlu parsing dengan strtok
        $this->indikator_id = $value;

        // Ambil bidang_id dari indikator yang dipilih
        $indikator = \App\Models\Indikator::find($this->indikator_id);
        if ($indikator) {
            $this->bidang_id = $indikator->bidang_id;
        } else {
            $this->bidang_id = null;
        }
    }

    public function sinkronWalidata()
    {
        try {
            // 1) Naikkan batas eksekusi proses PHP
            //    300 detik = 5 menit (silakan sesuaikan kebutuhan)
            @set_time_limit(300);

            // (Opsional) jika butuh, bisa juga buka limit memori:
            // @ini_set('memory_limit', '512M');

            // 2) Ambil data dari API dengan timeout & retry yang lebih aman
            $response = Http::withToken('d71e88f811fdf46c2d3afc5ab7a3c41b')
                ->connectTimeout(20)   // batas koneksi awal
                ->timeout(120)         // batas total request
                ->retry(3, 500)        // coba ulang 3x jeda 500ms bila 5xx/timeout
                ->get('https://sipd.go.id/ewalidata/serv/get_dssd_final', [
                    'kodepemda' => '6308',
                    'tahun'     => 2024,
                ]);

            if ($response->failed()) {
                throw new \Exception("API error: ".$response->status());
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \Exception("Format data tidak valid");
            }

            // 3) Cache lokal untuk menghindari firstOrCreate berulang
            $cacheBidang    = []; // ['kode_bidang' => BidangModel]
            $cacheIndikator = []; // ['kode_indikator' => IndikatorModel]

            // 4) Kumpulan rows untuk Walidata::upsert
            $rowsWalidata = [];
            $now = now();
            $sukses = 0;

            DB::beginTransaction();

            foreach ($data as $item) {
                // --- BIDANG ---
                $kodeBidang = $item['kodebidang'] ?? null;
                if (!$kodeBidang) {
                    // skip jika kode bidang tidak ada
                    continue;
                }

                if (!isset($cacheBidang[$kodeBidang])) {
                    // cari existing dulu (lebih hemat dari firstOrCreate di loop besar)
                    $existingBidang = Bidang::where('kode_bidang', $kodeBidang)->first();
                    if (!$existingBidang) {
                        $existingBidang = Bidang::create([
                            'id'            => (string) Str::uuid(),
                            'kode_bidang'   => $kodeBidang,
                            'uraian_bidang' => $item['uraibidang'] ?? null,
                        ]);
                    }
                    $cacheBidang[$kodeBidang] = $existingBidang;
                }
                $bidang = $cacheBidang[$kodeBidang];

                // --- INDIKATOR ---
                $kodeIndikator = $item['kodeindikator'] ?? null;
                if (!$kodeIndikator) {
                    // skip jika kode indikator tidak ada
                    continue;
                }

                if (!isset($cacheIndikator[$kodeIndikator])) {
                    $existingIndikator = Indikator::where('kode_indikator', $kodeIndikator)->first();
                    if (!$existingIndikator) {
                        $existingIndikator = Indikator::create([
                            'id'               => (string) Str::uuid(),
                            'kode_indikator'   => $kodeIndikator,
                            'uraian_indikator' => $item['uraian_indikator'] ?? null,
                            'bidang_id'        => $bidang->id,
                        ]);
                    } else {
                        // pastikan relasi bidang terjaga (optional)
                        if ($existingIndikator->bidang_id !== $bidang->id) {
                            $existingIndikator->update(['bidang_id' => $bidang->id]);
                        }
                    }
                    $cacheIndikator[$kodeIndikator] = $existingIndikator;
                }
                $indikator = $cacheIndikator[$kodeIndikator];

                // --- Timestamp dari API ---
                $apiTimestamp = $item['tanggal_verifikasi_pembinadata']
                    ?? $item['tanggal_verifikasi_walidata']
                    ?? null;

                $parsedTimestamp = $apiTimestamp ? Carbon::parse($apiTimestamp) : $now;

                // --- Siapkan row untuk upsert Walidata ---
                $rowsWalidata[] = [
                    'id'              => $item['idtransaksi'],                 // unique key
                    'satuan'          => $item['satuan'] ?? '',
                    'tahun'           => (string) ($item['tahun'] ?? '2024'),
                    'data'            => (string) ($item['data'] ?? '0'),
                    'indikator_id'    => $indikator->id,
                    'bidang_id'       => $bidang->id,
                    // timestamps
                    'created_at'      => $parsedTimestamp, // dipakai ketika insert
                    'updated_at'      => $now,             // dipakai ketika update
                    'verifikasi_data' => $parsedTimestamp,
                ];

                $sukses++;
            }

            // 5) Upsert Walidata sekaligus (sangat cepat)
            if (!empty($rowsWalidata)) {
                // kolom keunikan: 'id'
                // kolom yang di-update jika sudah ada:
                $updateCols = [
                    'satuan','tahun','data','indikator_id','bidang_id',
                    'verifikasi_data','updated_at'
                ];
                Walidata::upsert($rowsWalidata, ['id'], $updateCols);
            }

            DB::commit();

            // 6) Notifikasi UI
            $this->dispatch('swal', 
                title   : "Sinkronisasi selesai ($sukses data)",
                icon    : 'success',
                toast   : true,
                position: 'bottom-end',
                timer   : 5000
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sinkron walidata error', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            $this->dispatch('swal',
                title   : "Gagal sinkron: ".$e->getMessage(),
                icon    : 'error',
                toast   : true,
                position: 'bottom-end',
                timer   : 5000
            );
        }
    }
}
