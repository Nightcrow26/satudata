<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Dataset;
use App\Models\Skpd;
use App\Models\Aspek;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

#[Title('Dataset')]
class DatasetCrud extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public string $dataset_id = '';
    public string $deleteId = '';
    public ?Dataset $editingDataset = null;

    public string $nama = '';
    public string $status = '';
    public $excel;
    public $metadata;
    public $bukti_dukung; // <— PDF bukti dukung
    public int $tahun;
    public string $catatan_verif = '';
    public string $deskripsi = '';
    public string $keyword = '';
    public int $view = 0;
    public ?string $instansi_id = null;
    public ?string $aspek_id = null;

    public int $perPage = 10;

    public $availableSkpds = [];
    public $availableAspeks = [];

    protected array $messages = [
        'excel.file'    => 'File Excel harus berupa file.',
        'excel.mimes'   => 'Format Excel hanya boleh: xlsx, xls, csv.',
        'excel.max'     => 'Ukuran file Excel maksimal 5 MB.',
        'metadata.file' => 'File metadata harus berupa file.',
        'metadata.mimes'=> 'Format metadata hanya boleh: xlsx, xls, csv.',
        'metadata.max'  => 'Ukuran file metadata maksimal 5 MB.',
        // Pesan untuk bukti_dukung
        'bukti_dukung.file'  => 'Bukti dukung harus berupa file.',
        'bukti_dukung.mimes' => 'Bukti dukung harus berformat PDF.',
        'bukti_dukung.max'   => 'Ukuran file bukti dukung maksimal 10 MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama'         => 'required|string|min:3',
            'status'       => 'required|string',
            'excel'        => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
            'metadata'     => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
            'bukti_dukung' => 'nullable|file|mimes:pdf|max:10240', // <— validasi PDF
            'tahun'        => 'required|integer',
            'catatan_verif'=> 'nullable|string',
            'deskripsi'    => 'nullable|string',
            'keyword'      => 'nullable|string',
            'view'         => 'nullable|integer|min:0',
            'instansi_id'  => 'nullable|exists:skpd,id',
            'aspek_id'     => 'nullable|exists:aspeks,id',
        ];
    }

    public function mount(): void
    {
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

        $this->availableAspeks = Aspek::orderBy('nama')->get();
    }

    public function render()
    {
        // 1) Query dasar
        $query = Dataset::with(['skpd', 'aspek', 'user'])
            ->when($this->search !== '', fn($q) =>
                $q->where('nama', 'ilike', "%{$this->search}%")
            );

        // 2) Batasi untuk role 'produsen data' berdasarkan skpd milik user
        if (auth()->user()->hasRole('produsen data')) {
            $query->where('instansi_id', auth()->user()->skpd_uuid);
        }

        // 3) Paging & sorting
        $datasets = $query
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.admin.dataset-crud', compact('datasets'));
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();

        $this->deskripsi = '';
        $this->showModal = true;
        $this->dispatch('show-modal', id: 'dataset-modal');

        // Kosongkan trix di frontend
        $this->dispatch('clear-trix-content');
        
        // Clear Tom Select values for create mode
        $this->js("
            setTimeout(() => {
                // Clear Trix content
                window.dispatchEvent(new CustomEvent('set-trix-content', {
                    detail: { content: '' }
                }));
                
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'instansi_id',
                        value: '" . $this->instansi_id . "',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) { return ['id' => $skpd->id, 'text' => $skpd->nama]; })->values()->toArray()) . "
                    }
                }));
                
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'aspek_id',
                        value: '',
                        options: " . json_encode($this->availableAspeks->map(function($aspek) { return ['id' => $aspek->id, 'text' => $aspek->nama]; })->values()->toArray()) . "
                    }
                }));
                
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'status',
                        value: 'draft',
                        options: " . json_encode(collect(['draft' => 'Draft'] + (auth()->user()->hasRole('produsen data') ? ['revisi' => 'Revisi'] : []) + (auth()->user()->hasRole(['admin', 'verifikator']) ? ['pending' => 'Pending', 'published' => 'Published'] : []))->map(function($text, $value) { return ['id' => $value, 'text' => $text]; })->values()->toArray()) . "
                    }
                }));
            }, 300);
        ");
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $dataset = Dataset::findOrFail($id);
        $this->dataset_id    = $dataset->id;
        $this->nama          = $dataset->nama;
        $this->status        = $dataset->status;
        $this->tahun         = $dataset->tahun;
        $this->catatan_verif = $dataset->catatan_verif ?? '';
        $this->deskripsi     = $dataset->deskripsi ?? '';
        $this->keyword       = $dataset->keyword ?? '';
        $this->view          = $dataset->view ?? 0;
        $this->instansi_id   = $dataset->instansi_id;
        $this->aspek_id      = $dataset->aspek_id;
        $this->editingDataset= $dataset;
        
        $this->showModal     = true;

        // Dispatch events dengan delay untuk memastikan modal terbuka dulu
        $this->dispatch('show-modal', id: 'dataset-modal');
        
        // Update Tom Select options dan set Trix content
        $this->js("
            setTimeout(() => {
                // Update instansi options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'instansi_id',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . ",
                        value: '" . $this->instansi_id . "'
                    }
                }));
                
                // Update aspek options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'aspek_id',
                        options: " . json_encode($this->availableAspeks->map(function($aspek) {
                            return ['id' => $aspek->id, 'text' => $aspek->nama];
                        })->values()) . ",
                        value: '" . $this->aspek_id . "'
                    }
                }));
                
                // Update status Tom Select untuk edit mode
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'status',
                        options: " . json_encode(collect(['draft' => 'Draft'] + (auth()->user()->hasRole('produsen data') ? ['revisi' => 'Revisi'] : []) + (auth()->user()->hasRole(['admin', 'verifikator']) ? ['pending' => 'Pending', 'published' => 'Published'] : []))->map(function($text, $value) { return ['id' => $value, 'text' => $text]; })->values()->toArray()) . ",
                        value: '" . $this->status . "'
                    }
                }));
                
                // Update Trix Editor content untuk deskripsi
                window.dispatchEvent(new CustomEvent('trix-update', {
                    detail: {
                        target: 'deskripsi',
                        content: " . json_encode($this->deskripsi) . "
                    }
                }));
                
                // Set Trix content for edit mode
                const trixEditor = document.getElementById('dataset-deskripsi-editor');
                if (trixEditor && trixEditor.editor) {
                    trixEditor.editor.loadHTML('" . addslashes($this->deskripsi) . "');
                }
                
                // Fallback event for Trix
                window.dispatchEvent(new CustomEvent('set-trix-content', {
                    detail: { content: '" . addslashes($this->deskripsi) . "' }
                }));
            }, 500);
        ");
    }

    public function saveDataset(): void
    {
        // Ambil content dari hidden input sebelum validasi
        $this->js("
            const hiddenInput = document.getElementById('dataset-deskripsi-editor-hidden');
            if (hiddenInput) {
                window.Livewire.find('" . $this->getId() . "').set('deskripsi', hiddenInput.value);
            }
        ");
        
        // Delay sedikit untuk memastikan set deskripsi selesai
        $this->js("
            setTimeout(() => {
                window.Livewire.find('" . $this->getId() . "').call('processSaveDataset');
            }, 100);
        ");
    }

    public function processSaveDataset(): void
    {
        $validated = $this->validate();

        // Debug log untuk melihat nilai deskripsi
        \Log::info('Saving dataset', [
            'deskripsi_property' => $this->deskripsi,
            'validated_deskripsi' => $validated['deskripsi'] ?? 'NOT_SET'
        ]);

        // === Upload Excel (opsional) ===
        if ($this->excel) {
            $filename  = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->excel->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $this->excel->getClientOriginalExtension();
            $fullName  = $filename . '.' . $extension;

            $pathExcel = $this->excel->storeAs('datasets/excel', $fullName, ['disk' => 's3', 'visibility' => 'public']);
            $validated['excel'] = $pathExcel;

            if ($this->dataset_id) {
                $old = Dataset::find($this->dataset_id)?->excel;
                    delete_storage_object_if_key($old);
            }
        } else {
            unset($validated['excel']);
        }

        // === Upload Metadata (opsional) ===
        if ($this->metadata) {
            $metaName = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->metadata->getClientOriginalName(), PATHINFO_FILENAME));
            $metaExt  = $this->metadata->getClientOriginalExtension();
            $metaFull = $metaName . '.' . $metaExt;

            $pathMeta = $this->metadata->storeAs('datasets/meta', $metaFull, ['disk' => 's3', 'visibility' => 'public']);
            $validated['metadata'] = $pathMeta;

            if ($this->dataset_id) {
                $oldM = Dataset::find($this->dataset_id)?->metadata;
                    delete_storage_object_if_key($oldM);
            }
        } else {
            unset($validated['metadata']);
        }

        // === Upload Bukti Dukung (opsional, PDF) ===
        if ($this->bukti_dukung) {
            $bdName = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->bukti_dukung->getClientOriginalName(), PATHINFO_FILENAME));
            $bdExt  = $this->bukti_dukung->getClientOriginalExtension();
            $bdFull = $bdName . '.' . $bdExt;

            $pathBukti = $this->bukti_dukung->storeAs('datasets/bukti_dukung', $bdFull, ['disk' => 's3', 'visibility' => 'public']);
            $validated['bukti_dukung'] = $pathBukti;

            if ($this->dataset_id) {
                $oldBukti = Dataset::find($this->dataset_id)?->bukti_dukung;
                    delete_storage_object_if_key($oldBukti);
            }
        } else {
            unset($validated['bukti_dukung']);
        }

        // Pastikan deskripsi tidak kosong jika ada content
        if (empty($validated['deskripsi']) && !empty($this->deskripsi)) {
            $validated['deskripsi'] = $this->deskripsi;
        }

        // === Simpan ===
        if ($this->dataset_id) {
            $dataset = Dataset::findOrFail($this->dataset_id);
            $dataset->update($validated);
            
            // Verifikasi data tersimpan
            $dataset->refresh();
            \Log::info('Dataset updated', [
                'id' => $dataset->id,
                'deskripsi_saved' => $dataset->deskripsi
            ]);
        } else {
            $dataset = Dataset::create(array_merge(
                ['id' => (string) Str::uuid(), 'user_id' => auth()->id()],
                $validated
            ));
            
            \Log::info('Dataset created', [
                'id' => $dataset->id,
                'deskripsi_saved' => $dataset->deskripsi
            ]);
        }

        $message = $this->dataset_id ? 'Dataset diperbarui!' : 'Dataset ditambahkan!';
        $this->dispatch('swal',
            title: $message,
            icon: 'success',
            toast: true,
            position: 'bottom-end',
            timer: 3000
        );

        $this->closeModal();
        $this->resetPage();
    }

    // Method baru untuk mengupdate deskripsi dari JavaScript
    public function updateDeskripsi($content): void
    {
        $this->deskripsi = $content;
    }

    private function resetInput(): void
    {
        $this->reset([
            'dataset_id','nama','status','excel','tahun',
            'metadata','bukti_dukung','catatan_verif','deskripsi','keyword',
            'view','instansi_id','aspek_id'
        ]);

        // Set default values for create mode
        $this->status = 'draft';
        $this->tahun = date('Y');
        
        // Auto-set instansi_id for produsen data role
        if (auth()->user()->hasRole('produsen data')) {
            $this->instansi_id = auth()->user()->skpd_uuid;
        }
        
        $this->deskripsi = '';
        $this->editingDataset = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetInput();
        $this->dispatch('hide-modal', id: 'dataset-modal');
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
        $this->nama = Dataset::find($id)?->nama ?? ''; 
        $this->dispatch('show-modal', id: 'delete-modal');
    }

    public function deleteDatasetConfirmed(): void
    {
        $dataset = Dataset::findOrFail($this->deleteId);

            delete_storage_object_if_key($dataset->excel);
            delete_storage_object_if_key($dataset->metadata);
            delete_storage_object_if_key($dataset->bukti_dukung);

        $dataset->delete();

        $this->dispatch('swal',
            title: 'Dataset dihapus!',
            icon: 'success',
            toast: true,
            position: 'bottom-end',
            timer: 3000
        );
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->showDeleteModal = false;
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->showDeleteModal = false;
    }
}
