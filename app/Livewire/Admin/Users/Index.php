<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\User;
use App\Models\Skpd;
use App\Library\SikonSplpLibrary;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

#[Title('Users')]
class Index extends Component
{
    use WithPagination;

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

    public array  $availableRoles   = [];
    public $availableSkpds = [];

    protected string $paginationTheme = 'bootstrap';
    
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
            'skpd_uuid'  => 'nullable|exists:skpd,id'
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
            'role','skpd_uuid'
        ]);
        $this->editingUser = null;
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        $this->dispatch('show-modal', id: 'user-modal');
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
        $this->editingUser = $user;

        $this->dispatch('show-modal', id: 'user-modal');
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

        // 2. Lanjutkan simpan/update
        if ($this->user_id) {
            $user = User::findOrFail($this->user_id);
            $user->update([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'skpd_uuid' => $validated['skpd_uuid'],
                'nik'       => $validated['nik'],
            ]);
            $user->syncRoles($validated['role']);
            $message = 'User berhasil diperbarui!';
        } else {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'skpd_uuid' => $validated['skpd_uuid'],
                'nik'       => $validated['nik'],
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
        $this->dispatch('hide-modal', id: 'user-modal');
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('show-modal', id: 'delete-modal');
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
        $this->dispatch('hide-modal', id: 'delete-modal');
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->dispatch('hide-modal', id: 'delete-modal');
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
