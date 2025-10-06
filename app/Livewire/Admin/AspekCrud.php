<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Aspek;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

#[Title('Aspek')]
class AspekCrud extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';

    public bool   $showModal        = false;
    public bool   $showDeleteModal  = false;
    public string $aspek_id         = '';
    public ?Aspek $editingAspek     = null;

    public string $nama             = '';
    public string $warna            = '#000000';
    public $foto;

    public string $deleteId         = '';

    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage(); // reset ke halaman pertama
    }

    protected array $messages = [
        'foto.image' => 'File yang diunggah harus berupa gambar.',
        'foto.mimes' => 'Format gambar hanya boleh: jpg, jpeg, png, webp.',
        'foto.max'   => 'Ukuran gambar maksimal 2 MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama'   => 'required|string|min:2',
            'warna'  => 'required|string',
            'foto'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function render()
    {
        $aspeks = Aspek::query()
            ->when($this->search !== '', fn($q) =>
                $q->where('nama','ilike',"%{$this->search}%")
            )
            ->orderBy('nama')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.admin.aspek', compact('aspeks'));
    }

    private function resetInput(): void
    {
        $this->reset(['aspek_id','nama','warna','foto']);
        $this->editingAspek = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        $this->showModal = true;
        $this->dispatch('show-modal',  id:'aspek-modal');
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $aspek = Aspek::findOrFail($id);
        $this->aspek_id    = $aspek->id;
        $this->nama        = $aspek->nama;
        $this->warna       = $aspek->warna;
        $this->editingAspek= $aspek;
        $this->showModal   = true;

        $this->dispatch('show-modal', id:'aspek-modal');
    }

   public function saveAspek(): void
    {
        $validated = $this->validate();

        $oldPath = $this->aspek_id ? (Aspek::find($this->aspek_id)?->foto) : null;

        if ($this->foto) {
            // Logging awal upload
            $original = null; $size = null; $mime = null;
            try {
                $original = $this->foto->getClientOriginalName();
                $size = $this->foto->getSize();
                $mime = $this->foto->getMimeType();
            } catch (\Throwable $t) { /* ignore */ }
            \Log::info('Aspek upload start', [
                'aspek_id' => $this->aspek_id,
                'original' => $original,
                'size' => $size,
                'mime' => $mime,
            ]);

            // Simpan file baru ke S3
            try {
                $path = $this->foto->store('aspek-fotos', 's3');
            } catch (\Throwable $e) {
                \Log::error('Aspek upload failed at store', [
                    'error' => $e->getMessage(),
                    'aspek_id' => $this->aspek_id,
                ]);
                $this->dispatch('swal',
                    title: 'Gagal mengunggah foto',
                    icon: 'error',
                    toast: true,
                    position: 'bottom-end',
                    timer: 4000
                );
                return;
            }

            $validated['foto'] = $path;

            // Verifikasi object ada dan log URL contoh
            $exists = false; $tmpUrl = null; $tmpErr = null;
            try { $exists = \Storage::disk('s3')->exists($path); } catch (\Throwable $e) { $tmpErr = $e->getMessage(); }
            try { $tmpUrl = \Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5)); } catch (\Throwable $eTmp) { $tmpUrl = null; $tmpErr = ($tmpErr ? $tmpErr.' | ' : '').$eTmp->getMessage(); }

            \Log::info('Aspek upload stored', [
                'aspek_id' => $this->aspek_id,
                'path' => $path,
                'exists' => $exists,
                'temporary_url_sample' => $tmpUrl,
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'region' => config('filesystems.disks.s3.region'),
                'use_path_style' => config('filesystems.disks.s3.use_path_style_endpoint'),
                'old' => $oldPath,
                'errors' => $tmpErr,
            ]);

            if (!$exists) {
                // Jangan hapus file lama; batalkan update foto
                unset($validated['foto']);
                \Log::error('Aspek upload exists check failed', [
                    'aspek_id' => $this->aspek_id,
                    'path' => $path,
                ]);
                $this->dispatch('swal',
                    title: 'Gagal menyimpan foto (file tidak ditemukan di storage)',
                    icon: 'error',
                    toast: true,
                    position: 'bottom-end',
                    timer: 4500
                );
                return;
            }

            // Hapus file lama hanya setelah file baru dipastikan ada
            if ($oldPath) {
                delete_storage_object_if_key($oldPath);
            }
        } else {
            // Jangan override 'foto' saat tidak ada file baru
            unset($validated['foto']);
        }

        try {
            if ($this->aspek_id) {
                Aspek::findOrFail($this->aspek_id)->update($validated);
                \Log::info('Aspek updated', [
                    'id' => $this->aspek_id,
                    'foto' => $validated['foto'] ?? 'UNCHANGED',
                ]);
            } else {
                $created = Aspek::create(array_merge(
                    ['id' => (string) Str::uuid()],
                    $validated
                ));
                \Log::info('Aspek created', [
                    'id' => $created->id,
                    'foto' => $validated['foto'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Aspek save failed', [
                'aspek_id' => $this->aspek_id,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('swal',
                title: 'Gagal menyimpan data aspek',
                icon: 'error',
                toast: true,
                position: 'bottom-end',
                timer: 4500
            );
            return;
        }

        $message = $this->aspek_id
            ? 'Aspek berhasil diperbarui!'
            : 'Aspek berhasil dibuat!';

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
    
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->dispatch('hide-modal', id:'aspek-modal');
        $this->resetInput();
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId        = $id;
        $aspek = Aspek::findOrFail($id);
        $this->nama = $aspek->nama; // Set nama untuk ditampilkan di modal
        $this->showDeleteModal = true;
    }

    public function deleteAspekConfirmed(): void
    {
    $aspek = Aspek::findOrFail($this->deleteId);
    delete_storage_object_if_key($aspek->foto);
        $aspek->delete();

         $this->dispatch('swal', 
            title   :'Aspek berhasil dihapus!',
            icon    :'success',
            toast   :true,
            position:'bottom-end',
            timer   : 3000
        );

        $this->showDeleteModal = false;
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }
}
