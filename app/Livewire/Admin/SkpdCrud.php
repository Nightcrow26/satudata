<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Library\SikonSplpLibrary;

#[Title('SKPD')]
class SkpdCrud extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';

    public string     $skpd_id         = '';
    public ?Skpd      $editingSkpd     = null;      // ← instance untuk edit
    public string     $nama            = '';
    public string     $singkatan       = '';
    public string     $alamat          = '';
    public string     $telepon         = '';
    public string     $deleteId        = '';
    public $foto;                
    
    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage(); // reset ke halaman pertama
    }

    // custom messages (opsional)
    protected array $messages = [
        'foto.image'   => 'File yang diunggah harus berupa gambar.',
        'foto.mimes'   => 'Format gambar hanya boleh: jpg, jpeg, png, webp.',
        'foto.max'     => 'Ukuran gambar maksimal 2 MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama'       => 'required|string|min:3',
            'singkatan'  => 'required|string|max:50',
            'alamat'     => 'nullable|string',
            'telepon'    => 'nullable|string|max:20',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function saveSkpd(): void
    {
        $validated = $this->validate();

        // Jika ada upload baru, simpan dan gantikan foto lama
        if ($this->foto) {
            $path = $this->foto->store('skpd-fotos', 's3');
            $validated['foto'] = $path;

            if ($this->skpd_id) {
                $old = Skpd::find($this->skpd_id)->foto;
                if ($old && Storage::disk('s3')->exists($old)) {
                    Storage::disk('s3')->delete($old);
                }
            }
        } else {
            // Jika tidak ada foto baru, jangan update kolom foto
            unset($validated['foto']);
        }

        if ($this->skpd_id) {
            Skpd::findOrFail($this->skpd_id)->update($validated);
        } else {
            Skpd::create($validated);
        }

        $message = $this->skpd_id 
                ? 'SKPD berhasil diperbarui!' 
                : 'SKPD berhasil dibuat!';

        $this->dispatch('swal', 
            title   :$message,
            icon    :'success',
            toast   :true,
            position:'bottom-end',
            timer   : 3000
        );
        $this->dispatch('hide-modal', id:'skpd-modal');
        $this->resetPage();
    }

    public function render()
    {
        $query = Skpd::query()
            ->whereColumn('id', 'unor_induk_id')
            ->when($this->search !== '', fn($q) =>
                $q->where('nama','ilike',"%{$this->search}%")
                  ->orWhere('singkatan','ilike',"%{$this->search}%")
            );

         // 2) Batasi untuk role 'user' berdasarkan skpd milik user
        if (auth()->user()->hasRole('user')) {
            $query->where('id', auth()->user()->skpd_uuid);
        }
        $skpds = $query
            ->orderBy('nama')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.admin.skpd.index', compact('skpds'));
    }

    private function resetInput(): void
    {
        $this->reset([
            'skpd_id','nama','singkatan','alamat','telepon','foto'
        ]);
        $this->editingSkpd = null;
        
    }

    public function sinkronUnorFromSikon()
    {
        try {
            $api = new SikonSplpLibrary();
            $raw = $api->makeApiRequest('GET', 'unor/list');

            // Parsing hasil dari library
            if (is_string($raw)) {
                $result = json_decode($raw, true);
            } elseif (is_object($raw) && method_exists($raw, 'getBody')) {
                $result = json_decode($raw->getBody()->getContents(), true);
            } elseif (is_array($raw)) {
                $result = $raw;
            } else {
                throw new \Exception('Format response tidak valid dari API SIKON');
            }

            // Debug: Log response untuk troubleshooting
            \Log::info('SIKON API Response:', $result);

            // Validasi struktur hasil
            if (!is_array($result)) {
                throw new \Exception('Response API tidak berupa array');
            }

            // Cek status response
            if (isset($result['status']) && $result['status'] !== 'success') {
                throw new \Exception($result['message'] ?? 'API mengembalikan status error');
            }

            // Pastikan ada data
            if (!isset($result['data']) || !is_array($result['data'])) {
                throw new \Exception('Data tidak ditemukan dalam response API');
            }

            // Proses data
            $sukses = 0;
            $total = count($result['data']);

            foreach ($result['data'] as $item) {
                try {
                    if (!isset($item['id']) || !isset($item['nama_unor'])) {
                        \Log::warning('Data unor tidak lengkap:', $item);
                        continue;
                    }

                    // Cek apakah SKPD sudah ada
                    $skpd = Skpd::find($item['id']);

                    if ($skpd) {
                        // Jika sudah ada, update hanya field tertentu
                        $skpd->update([
                            'unor_induk_id' => $item['unor_induk_id'] ?? $skpd->unor_induk_id,
                            'nama'          => $item['nama_unor'], // nama boleh selalu update
                            'diatasan_id'   => $item['diatasan_id'] ?? $skpd->diatasan_id,
                        ]);
                    } else {
                        // Jika belum ada, buat baru dengan semua field
                        Skpd::create([
                            'id'            => $item['id'],
                            'unor_induk_id' => $item['unor_induk_id'] ?? null,
                            'nama'          => $item['nama_unor'],
                            'singkatan'     => $item['singkatan'] ?? null,
                            'alamat'        => $item['alamat'] ?? null,
                            'telepon'       => $item['telepon'] ?? null,
                            'diatasan_id'   => $item['diatasan_id'] ?? null,
                            'foto'          => asset('logo-hsu.png'),
                        ]);
                    }

                    $sukses++;
                } catch (\Exception $e) {
                    \Log::error('Error updating SKPD:', [
                        'item' => $item,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Kirim notifikasi sukses menggunakan Livewire dispatch
            $this->dispatch('swal', 
                title   : "Sinkronisasi berhasil! ($sukses/$total SKPD berhasil disinkronkan)",
                icon    : 'success',
                toast   : true,
                position: 'bottom-end',
                timer   : 5000
            );

            // Refresh data dengan reset halaman
            $this->resetPage();

        } catch (\Exception $e) {
            \Log::error('Error sinkron unor from SIKON:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Kirim notifikasi error
            $this->dispatch('swal', 
                title   : 'Gagal sinkronisasi: ' . $e->getMessage(),
                icon    : 'error',
                toast   : true,
                position: 'bottom-end',
                timer   : 5000
            );
        }
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();

        $this->skpd_id = '';
        $this->editingSkpd = null;

        $this->dispatch('show-modal', id:'skpd-modal');
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $skpd = Skpd::findOrFail($id);
        $this->skpd_id   = $id;
        $this->nama      = $skpd->nama;
        $this->singkatan = $skpd->singkatan ?? ' ';
        $this->alamat    = $skpd->alamat ?? ' ';
        $this->telepon   = $skpd->telepon ?? ' ';    

        $this->editingSkpd   = $skpd;
        $this->dispatch('show-modal', id:'skpd-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('hide-modal', id:'skpd-modal');
        $this->resetInput();
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId        = $id;
        $this->dispatch('show-modal', id:'delete-modal');
    }

    public function deleteSkpdConfirmed(): void
    {
        $skpd = Skpd::findOrFail($this->deleteId);
        $skpd->foto && Storage::disk('s3')->delete($skpd->foto);
        $skpd->delete();
        $this->dispatch('swal', 
            title   :'SKPD berhasil dihapus!',
            icon    :'success',
            toast   :true,
            position:'bottom-end',
            timer   : 3000
        );
        
        $this->dispatch('hide-modal', id:'delete-modal');
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->dispatch('hide-modal', id:'delete-modal');
    }

}
