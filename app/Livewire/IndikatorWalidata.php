<?php

namespace App\Livewire;

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

    public int $perPage = 10;

    public $availableSkpds = [];
    public $availableAspeks = [];
    public $availableIndikators = [];
    public $availableBidangs = [];

    protected array $messages = [
        'tahun.regex' => 'Tahun harus 4 digit.',
        'skpd_id.exists' => 'SKPD tidak valid.',
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
        $this->availableSkpds      = Skpd::orderBy('nama')->whereColumn('id', 'unor_induk_id')->get();
        $this->availableAspeks     = Aspek::orderBy('nama')->get();
        $this->availableIndikators = Indikator::orderBy('kode_indikator')->get();
        $this->availableBidangs    = Bidang::orderBy('kode_bidang')->get();

        // Lock SKPD untuk role 'user'
        if (auth()->user()->hasRole('user')) {
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
                      ->orWhere('data',  'ilike', $term);
                });
            });

        // Batasi user biasa ke SKPD miliknya
        if (auth()->user()->hasRole('user')) {
            $query->where('skpd_id', auth()->user()->skpd_uuid);
        }

        $walidatas = $query
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.indikator-walidata', compact('walidatas'));
    }

    private function resetInput(): void
    {
        $this->reset([
            'walidata_id', 'satuan', 'tahun', 'data',
            'aspek_id', 'indikator_id', 'bidang_id',
        ]);

        // jaga skpd_id tetap terkunci untuk role 'user'
        if (!auth()->user()->hasRole('user')) {
            $this->skpd_id = null;
        }

        $this->editingWalidata = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();

        $this->showModal = true;
        $this->dispatch('show-modal', id: 'walidata-modal');
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

        $this->editingWalidata = $wd;
        $this->showModal = true;

        $this->dispatch('show-modal', id: 'walidata-modal');
    }

    public function saveWalidata(): void
    {
        // Lock SKPD untuk role 'user'
        if (auth()->user()->hasRole('user')) {
            $this->skpd_id = auth()->user()->skpd_uuid ?? null;
        }

        // Auto-sync bidang dari indikator (jika ada)
        if ($this->indikator_id) {
            $this->bidang_id = \App\Models\Indikator::whereKey($this->indikator_id)->value('bidang_id');
        } else {
            $this->bidang_id = null;
        }

        $validated = $this->validate(); // validasi setelah sinkron

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

    public function updatedIndikatorId($value): void
    {
        // Debug log
        \Log::info('updatedIndikatorId called with value:', ['value' => $value]);
        
        if (blank($value)) {
            $this->bidang_id = null;
            
            // Dispatch event untuk clear bidang
            $this->dispatch('tom-update', 
                id: 'bidang', 
                options: [], 
                value: null,
                text: ''
            );
            
            \Log::info('Dispatched tom-update for clearing bidang');
            return;
        }

        // Get bidang_id from selected indikator
        $this->bidang_id = \App\Models\Indikator::whereKey($value)->value('bidang_id');
        
        \Log::info('Found bidang_id:', ['bidang_id' => $this->bidang_id]);

        $opt = [];
        $text = '';
        
        if ($this->bidang_id) {
            $bid = \App\Models\Bidang::select('id','kode_bidang','uraian_bidang')->find($this->bidang_id);
            if ($bid) {
                $text = trim(($bid->kode_bidang ? $bid->kode_bidang.' - ' : '').($bid->uraian_bidang ?? ''));
                $opt  = [['id' => $bid->id, 'text' => $text]];
            }
        }

        \Log::info('Prepared data for dispatch:', [
            'options' => $opt,
            'value' => $this->bidang_id,
            'text' => $text
        ]);

        // Dispatch event untuk update bidang
        $this->dispatch('tom-update', 
            id: 'bidang', 
            options: $opt, 
            value: $this->bidang_id, 
            text: $text
        );
        
        \Log::info('Dispatched tom-update for updating bidang');
    }

    public function sinkronWalidata()
    {
        try {
            $response = Http::withToken('d71e88f811fdf46c2d3afc5ab7a3c41b')
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

            $sukses = 0;
            foreach ($data as $item) {
                // 1. Sinkron bidang
                $bidang = Bidang::firstOrCreate(
                    ['kode_bidang' => $item['kodebidang']],
                    [
                        'id'            => (string) Str::uuid(),
                        'uraian_bidang' => $item['uraibidang'] ?? null,
                    ]
                );

                // 2. Sinkron indikator
                $indikator = Indikator::firstOrCreate(
                    ['kode_indikator' => $item['kodeindikator']],
                    [
                        'id'                => (string) Str::uuid(),
                        'uraian_indikator'  => $item['uraian_indikator'] ?? null,
                        'bidang_id'         => $bidang->id,
                    ]
                );

                // pilih timestamp yang tersedia dari API
                $apiTimestamp = $item['tanggal_verifikasi_pembinadata']
                    ?? $item['tanggal_verifikasi_walidata']
                    ?? null;

                $parsedTimestamp = $apiTimestamp 
                    ? Carbon::parse($apiTimestamp)
                    : now();

                $walidata = Walidata::find($item['idtransaksi']);
                if ($walidata) {
                    $walidata->update([
                        'satuan'       => $item['satuan'] ?? '',
                        'tahun'        => $item['tahun'] ?? '2024',
                        'data'         => $item['data'] ?? '0',
                        'indikator_id' => $indikator->id,
                        'bidang_id'    => $bidang->id,
                        'verifikasi_data'   => $parsedTimestamp,
                    ]);
                } else {
                    Walidata::create([
                        'id'           => $item['idtransaksi'],
                        'satuan'       => $item['satuan'] ?? '',
                        'tahun'        => $item['tahun'] ?? '2024',
                        'data'         => $item['data'] ?? '0',
                        'indikator_id' => $indikator->id,
                        'bidang_id'    => $bidang->id,
                        'created_at'   => $parsedTimestamp,
                        'verifikasi_data'   => $parsedTimestamp,
                    ]);
                }


                $sukses++;
            }

            $this->dispatch('swal', 
                title   : "Sinkronisasi selesai ($sukses data)",
                icon    : 'success',
                toast   : true,
                position: 'bottom-end',
                timer   : 5000
            );

        } catch (\Throwable $e) {
            \Log::error('Sinkron walidata error', ['msg' => $e->getMessage()]);
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
