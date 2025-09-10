<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use App\Models\Publikasi;
use App\Models\Skpd;
use Illuminate\Support\Str;
use App\Models\Aspek;
use Illuminate\Support\Facades\Storage;

#[Title('Publikasi')]
class PublikasiCrud extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';
    public int    $perPage         = 10;
    public bool   $showModal       = false;
    public bool   $showDeleteModal = false;
    public string $publikasi_id    = '';
    public string $deleteId        = '';
    public ?Publikasi $editingPublikasi = null;

    // kolom migrasi
    public string $nama            = '';
    public string $status          = 'draft';
    public $pdf                    = null;
    public $foto                   = null;
    public int    $tahun           = 2025;
    public string $catatan_verif   = '';
    public string $deskripsi       = '';
    public string $keyword         = '';
    public ?string $instansi_id    = null;
    public ?string $aspek_id       = null;

    public $availableSkpds = [];
    public $availableAspeks = [];

    protected string $paginationTheme = 'bootstrap';

    protected array $messages = [
        'foto.image' => 'File yang diunggah harus berupa gambar.',
        'foto.mimes' => 'Format gambar hanya boleh: jpg, jpeg, png, webp.',
        'foto.max'   => 'Ukuran gambar maksimal 2 MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama'           => 'required|string|max:255',
            'status'         => 'required|in:draft,pending,published',
            'pdf'            => 'nullable|file|mimes:pdf|max:5120',
            'foto'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tahun'          => 'required|digits:4|integer',
            'catatan_verif'  => 'nullable|string',
            'deskripsi'      => 'nullable|string|max:65535',
            'keyword'        => 'nullable|string|max:255',
            'instansi_id'    => 'nullable|exists:skpd,id',
            'aspek_id'       => 'nullable|exists:aspeks,id',
        ];
    }

    public function mount(): void
    {
        // Role-based SKPD loading
        if (auth()->user()->hasRole('user')) {
            $this->availableSkpds = Skpd::orderBy('nama')
                ->whereColumn('id', 'unor_induk_id')
                ->where('id', auth()->user()->skpd_uuid)
                ->get();
        } else {
            $this->availableSkpds = Skpd::orderBy('nama')
                ->whereColumn('id', 'unor_induk_id')
                ->get();
        }

        $this->availableAspeks = Aspek::orderBy('nama')->get();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Publikasi::with(['skpd', 'aspek', 'user'])
            ->when($this->search !== '', fn($q) =>
                $q->where('nama', 'ilike', "%{$this->search}%")
            );

        // Role-based filtering
        if (auth()->user()->hasRole('user')) {
            $query->where('instansi_id', auth()->user()->skpd_uuid);
        }

        $publikasis = $query
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.admin.publikasi', compact('publikasis'));
    }

    private function resetInput(): void
    {
        $this->reset([
            'publikasi_id','nama','status','pdf','foto',
            'tahun','catatan_verif','deskripsi','keyword',
            'instansi_id','aspek_id'
        ]);
        $this->deskripsi = '';
        $this->editingPublikasi = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        
        $this->deskripsi = '';
        $this->showModal = true;
        $this->dispatch('show-modal', id: 'publikasi-modal');
        
        // Kosongkan summernote di frontend
        $this->dispatch('clear-summernote-content');
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();
        
        $pub = Publikasi::findOrFail($id);
        $this->publikasi_id   = $pub->id;
        $this->nama           = $pub->nama;
        $this->status         = $pub->status;
        $this->tahun          = $pub->tahun;
        $this->catatan_verif  = $pub->catatan_verif ?? '';
        $this->deskripsi      = $pub->deskripsi ?? '';
        $this->keyword        = $pub->keyword ?? '';
        $this->instansi_id    = $pub->instansi_id;
        $this->aspek_id       = $pub->aspek_id;
        $this->editingPublikasi = $pub;

        $this->showModal = true;
        $this->dispatch('show-modal', id: 'publikasi-modal');
        
        // Delay untuk set content setelah modal terbuka dan summernote ter-initialize
        $this->js("
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('set-summernote-content', {
                    detail: { 
                        content: '" . addslashes($this->deskripsi) . "',
                        target: 'publikasi'
                    }
                }));
            }, 500);
        ");
    }

    public function savePublikasi(): void
    {
        // Ambil content dari hidden input sebelum validasi
        $this->js("
            const hiddenInput = document.getElementById('publikasi-deskripsi-editor-hidden');
            if (hiddenInput) {
                window.Livewire.find('" . $this->getId() . "').set('deskripsi', hiddenInput.value);
            }
        ");
        
        // Delay sedikit untuk memastikan set deskripsi selesai
        $this->js("
            setTimeout(() => {
                window.Livewire.find('" . $this->getId() . "').call('processSavePublikasi');
            }, 100);
        ");
    }

    public function processSavePublikasi(): void
    {
        $validated = $this->validate();

        // Debug log untuk melihat nilai deskripsi
        \Log::info('Saving publikasi', [
            'deskripsi_property' => $this->deskripsi,
            'validated_deskripsi' => $validated['deskripsi'] ?? 'NOT_SET'
        ]);

        // Handle Foto
        if ($this->foto) {
            $fotoName = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->foto->getClientOriginalName(), PATHINFO_FILENAME));
            $fotoExt = $this->foto->getClientOriginalExtension();
            $fotoFull = $fotoName . '.' . $fotoExt;
            $pathFoto = $this->foto->storeAs('publikasi-fotos', $fotoFull, 's3');
            $validated['foto'] = $pathFoto;

            if ($this->publikasi_id) {
                $oldFoto = Publikasi::find($this->publikasi_id)->foto;
                $oldFoto && Storage::disk('s3')->delete($oldFoto);
            }
        } else {
            unset($validated['foto']);
        }

        // Handle PDF
        if ($this->pdf) {
            $pdfName = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->pdf->getClientOriginalName(), PATHINFO_FILENAME));
            $pdfExt = $this->pdf->getClientOriginalExtension();
            $pdfFull = $pdfName . '.' . $pdfExt;
            $pathPdf = $this->pdf->storeAs('publikasi-pdfs', $pdfFull, 's3');
            $validated['pdf'] = $pathPdf;

            if ($this->publikasi_id) {
                $oldPdf = Publikasi::find($this->publikasi_id)->pdf;
                $oldPdf && Storage::disk('s3')->delete($oldPdf);
            }
        } else {
            unset($validated['pdf']);
        }

        // Pastikan deskripsi tidak kosong jika ada content
        if (empty($validated['deskripsi']) && !empty($this->deskripsi)) {
            $validated['deskripsi'] = $this->deskripsi;
        }

        $validated['user_id'] = auth()->id();

        if ($this->publikasi_id) {
            $publikasi = Publikasi::findOrFail($this->publikasi_id);
            $publikasi->update($validated);
            
            // Verifikasi data tersimpan
            $publikasi->refresh();
            \Log::info('Publikasi updated', [
                'id' => $publikasi->id,
                'deskripsi_saved' => $publikasi->deskripsi
            ]);
            
            $msg = 'Publikasi diperbarui!';
        } else {
            $validated['id'] = (string) Str::uuid();
            $publikasi = Publikasi::create($validated);
            
            \Log::info('Publikasi created', [
                'id' => $publikasi->id,
                'deskripsi_saved' => $publikasi->deskripsi
            ]);
            
            $msg = 'Publikasi dibuat!';
        }

        $this->dispatch('swal', title: $msg, icon: 'success', toast: true, position: 'bottom-end', timer: 3000);
        $this->closeModal();
        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetInput();
        $this->dispatch('hide-modal', id: 'publikasi-modal');
    }

    // Method baru untuk mengupdate deskripsi dari JavaScript
    public function updateDeskripsi($content): void
    {
        $this->deskripsi = $content;
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('show-modal', id: 'delete-modal');
    }

    public function deletePublikasiConfirmed(): void
    {
        $pub = Publikasi::findOrFail($this->deleteId);
        // optionally hapus file:
        Storage::disk('s3')->delete([$pub->pdf, $pub->foto]);
        $pub->delete();

        $this->dispatch('swal',
            title: 'Publikasi dihapus!',
            icon: 'success',
            toast: true, position: 'bottom-end', timer: 3000
        );
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->dispatch('hide-modal', id: 'delete-modal');
    }
}