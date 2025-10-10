<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\User;
use App\Models\Skpd;
use App\Library\SikonSplpLibrary;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

#[Title('Users')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';

    public bool   $showModal        = false;
    public bool   $showDeleteModal  = false;

    public string $user_id          = '';
    public string $deleteId         = '';
    public ?User  $editingUser      = null;

    public string $name             = '';
    public string $email            = '';
    public string $nik              = '';
    public string $role             = '';
    public ?string $skpd_uuid       = null;
    public $sk_penunjukan           = null; // File upload
    public ?string $current_sk_penunjukan = null; // Current file path

    public array  $availableRoles   = [];
    public $availableSkpds = [];
    
    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage(); // reset ke halaman pertama
    }

    protected array $validationAttributes = [
        'role'                  => 'role',
        'skpd_uuid'             => 'skpd',
    ];

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|min:3',
            'email' => [
                'required',
                'email',
                $this->user_id
                    ? 'unique:users,email,' . $this->user_id . ',id'
                    : 'unique:users,email'
            ],
            'role'       => 'required|exists:roles,name',
            'nik'        => 'nullable|string',
            'skpd_uuid'  => 'nullable|exists:skpd,id',
            'sk_penunjukan' => 'nullable|file|mimes:pdf|max:5120', // Max 5MB
        ];
    }

    public function mount(): void
    {
        $this->availableRoles = Role::pluck('name', 'name')->toArray();
        $this->availableSkpds = Skpd::orderBy('nama')
                                ->whereColumn('id', 'unor_induk_id')
                                ->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
                ->with('skpd') 
                ->when($this->search !== '', fn($q) =>
                    $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%")
                )
                ->orderBy('name')
                ->paginate($this->perPage)
                ->onEachSide(1);

        return view('livewire.admin.users.index', compact('users'));
    }

    private function resetInput(): void
    {
        $this->reset([
            'user_id','name','email','nik',
            'role','skpd_uuid','sk_penunjukan','current_sk_penunjukan'
        ]);
        $this->editingUser = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        $this->showModal = true;
        
        // Update Tom Select options dengan format yang benar
        $this->js("
            setTimeout(() => {
                // Update SKPD options
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'skpd_uuid',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . "
                    }
                }));
                
                // Update role options
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'role',
                        options: " . json_encode(collect($this->availableRoles)->map(function($role) {
                            return ['id' => $role, 'text' => ucfirst($role)];
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

        $user = User::findOrFail($id);
        $this->user_id     = $user->id;
        $this->name        = $user->name;
        $this->email       = $user->email;
        $this->nik         = $user->nik ?? '';
        $this->role        = $user->getRoleNames()->first() ?? '';
        $this->skpd_uuid   = $user->skpd_uuid;
        $this->current_sk_penunjukan = $user->sk_penunjukan;
        $this->editingUser = $user;

        $this->showModal = true;
        
        // Update Tom Select options dan set selected values
        $this->js("
            setTimeout(() => {
                // Update SKPD options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'skpd_uuid',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . ",
                        value: '" . $this->skpd_uuid . "'
                    }
                }));
                
                // Update role options dengan format [{id, text}]
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'role',
                        options: " . json_encode(collect($this->availableRoles)->map(function($role) {
                            return ['id' => $role, 'text' => ucfirst($role)];
                        })->values()) . ",
                        value: '" . $this->role . "'
                    }
                }));
            }, 500);
        ");
    }

    public function saveUser(): void
    {
        $validated = $this->validate();

        // 1. Cek apakah NIK sudah terdaftar di user lain
        if (!empty($validated['nik'])) {
            $nikExists = User::where('nik', $validated['nik'])
                // jika update, abaikan record yang sedang diedit
                ->when($this->user_id, fn($q) => $q->where('id', '!=', $this->user_id))
                ->exists();

            if ($nikExists) {
                $this->dispatch('swal',
                    title: "Pegawai dengan NIK {$validated['nik']} sudah ada!",
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    keepModalOpen: true
                );
                return;
            }
        }

        // 2. Handle SK penunjukan upload
        $skPenunjukanPath = null;
        if ($this->sk_penunjukan) {
            // Hapus file lama jika ada (untuk update)
            if ($this->user_id && $this->current_sk_penunjukan) {
                    delete_storage_object_if_key($this->current_sk_penunjukan);
            }
            
            // Generate filename like dataset pattern
            $originalName = $this->sk_penunjukan->getClientOriginalName();
            $extension = $this->sk_penunjukan->getClientOriginalExtension();
            $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;
            
            // Store file and save path only (not URL) - sama seperti dataset
            $skPenunjukanPath = $this->sk_penunjukan->storeAs('sk-penunjukan', $filename, 's3');
        }

        // 3. Lanjutkan simpan/update
        if ($this->user_id) {
            $user = User::findOrFail($this->user_id);
            $updateData = [
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'skpd_uuid' => $validated['skpd_uuid'],
                'nik'       => $validated['nik'],
            ];
            
            // Tambahkan SK penunjukan jika ada file baru
            if ($skPenunjukanPath) {
                $updateData['sk_penunjukan'] = $skPenunjukanPath;
            }
            
            $user->update($updateData);
            $user->syncRoles($validated['role']);
            $message = 'User berhasil diperbarui!';
        } else {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'skpd_uuid' => $validated['skpd_uuid'],
                'nik'       => $validated['nik'],
                'sk_penunjukan' => $skPenunjukanPath,
            ]);
            $user->assignRole($validated['role']);
            $message = 'User berhasil dibuat!';
        }

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
        $this->resetInput();
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->name = User::find($id)?->name ?? '';
        $this->showDeleteModal = true;
    }

    public function deleteUserConfirmed(): void
    {
        $user = User::findOrFail($this->deleteId);
        $user->delete();
        $this->dispatch('swal',
            title: 'User berhasil dihapus!',
            icon: 'success',
            toast: true,
            position: 'bottom-end',
            timer: 3000
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

    public function cariDataAsn()
    {
        if (empty($this->nik)) {
            $this->dispatch('swal',
                title:'Masukkan NIK dulu!',
                icon: 'warning',
                toast: true,
                position: 'bottom-end',
                timer: 2000,
                keepModalOpen: true, 
            );
            return;
        }

        try {
            $api = new SikonSplpLibrary();
            $res = $api->makeApiRequest('GET', 'asn/cek-nik/' . $this->nik);

            if (($res['status'] ?? '') === 'success' && !empty($res['data'])) {
                $d = $res['data'];
                $this->name  = $d['nama'] ?? '';
                $this->email = $d['email_gov'] ?? '';
                $this->skpd_uuid = Skpd::where('id', $d['unor_id'])
                                        ->value('unor_induk_id');

                // Berikan feedback sukses tanpa menutup modal
                $this->dispatch('swal',
                    keepModalOpen: true,
                    title: 'Data ASN berhasil ditemukan!',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    timer: 2000
                );
            } else {
                $this->dispatch('swal',
                    title: 'Data ASN tidak ditemukan',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    keepModalOpen: true,
                );
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal',
                title : 'Gagal koneksi API',
                text : $e->getMessage(),
                icon : 'error',
                toast : true,
                position : 'top-end',
                timer: 3000,
                keepModalOpen : true,
            );
        }
    }
}
