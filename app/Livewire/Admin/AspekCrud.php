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

        if ($this->foto) {
            try {
                // Generate unique filename
                $filename  = now()->format('YmdHis') . '-' . Str::slug(pathinfo($this->foto->getClientOriginalName(), PATHINFO_FILENAME));
                $extension = $this->foto->getClientOriginalExtension();
                $fullName  = $filename . '.' . $extension;

                // Upload to S3 with explicit path
                $path = $this->foto->storeAs('aspek-fotos', $fullName, 's3');
                
                if (!$path) {
                    throw new \Exception('Failed to upload file to S3');
                }

                // Verify upload succeeded by checking if file exists
                if (!Storage::disk('s3')->exists($path)) {
                    throw new \Exception('File upload verification failed - file not found on S3');
                }

                \Log::info('Aspek foto uploaded successfully', [
                    'path' => $path,
                    'original_name' => $this->foto->getClientOriginalName(),
                    'size' => $this->foto->getSize()
                ]);

                $validated['foto'] = $path;

                // Only delete old file AFTER successful upload
                if ($this->aspek_id) {
                    $old = Aspek::find($this->aspek_id)?->foto;
                    if ($old) {
                        delete_storage_object_if_key($old);
                        \Log::info('Old aspek foto deleted', ['old_path' => $old]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Aspek foto upload failed', [
                    'error' => $e->getMessage(),
                    'aspek_id' => $this->aspek_id,
                    'file_name' => $this->foto->getClientOriginalName() ?? 'unknown'
                ]);

                $this->dispatch('swal',
                    title: 'Gagal mengunggah foto!',
                    text: 'Silakan coba lagi atau hubungi administrator.',
                    icon: 'error',
                    toast: true,
                    position: 'bottom-end',
                    timer: 5000
                );
                return;
            }
        } else {
            // Jangan override 'foto' saat tidak ada file baru
            unset($validated['foto']);
        }

        try {
            if ($this->aspek_id) {
                $aspek = Aspek::findOrFail($this->aspek_id);
                $aspek->update($validated);
                
                // Verify data saved
                $aspek->refresh();
                \Log::info('Aspek updated successfully', [
                    'id' => $aspek->id,
                    'nama' => $aspek->nama,
                    'foto_path' => $aspek->foto
                ]);
            } else {
                $aspek = Aspek::create(array_merge(
                    ['id' => (string) Str::uuid()],
                    $validated
                ));
                
                \Log::info('Aspek created successfully', [
                    'id' => $aspek->id,
                    'nama' => $aspek->nama,
                    'foto_path' => $aspek->foto
                ]);
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
        } catch (\Exception $e) {
            \Log::error('Aspek save failed', [
                'error' => $e->getMessage(),
                'aspek_id' => $this->aspek_id,
                'validated_data' => $validated
            ]);

            $this->dispatch('swal',
                title: 'Gagal menyimpan aspek!',
                text: 'Silakan coba lagi atau hubungi administrator.',
                icon: 'error',
                toast: true,
                position: 'bottom-end',
                timer: 5000
            );
        }
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
