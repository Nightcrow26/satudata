<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Skpd;
use Spatie\Permission\Models\Role;
use App\Library\SikonSplpLibrary;

class Userprofile extends Component
{
    public string $user_id = '';
    public ?User  $editingUser = null;

    public string $name = '';
    public string $email = '';
    public string $nik = '';
    public string $role = '';
    public string $deskripsi = '';
    public ?string $skpd_uuid = null;

    public array $availableRoles = [];
    public $availableSkpds = [];

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|min:3',
            'email'     => 'required|email|unique:users,email,' . ($this->user_id ?: 'NULL') . ',id',
            'nik'       => 'nullable|string',
            'role'      => 'nullable|exists:roles,name',
            'skpd_uuid' => 'nullable|exists:skpd,id',
        ];
    }

    public function mount(): void
    {
        $u = auth()->user();
        $this->loadUser($u->id);

        $this->availableRoles  = Role::pluck('name')->toArray();
        $this->availableSkpds  = Skpd::orderBy('nama')
                                    ->whereColumn('id', 'unor_induk_id')
                                    ->get();
    }

    private function loadUser(string $id): void
    {
        $u = User::findOrFail($id);
        $this->user_id     = (string) $u->id;
        $this->editingUser = $u;

        $this->name        = $u->name;
        $this->email       = $u->email;
        $this->nik         = $u->nik ?? '';
        $this->skpd_uuid   = $u->skpd_uuid;
        $this->role        = $u->getRoleNames()->first() ?? '';
    }

    public function showProfileModal(): void
    {
        $this->loadUser((string) auth()->id());
        $this->dispatch('show-modal', id: 'profile-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('hide-modal', id: 'profile-modal');
    }

    public function saveUser(): void
    {
        $validated = $this->validate();

        // larang user mengganti rolenya sendiri (opsional)
        if (auth()->id() === $this->editingUser?->id) {
            unset($validated['role']);
        }

        $u = User::findOrFail($this->user_id);
        $u->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'nik'       => $validated['nik'] ?? null,
            'skpd_uuid' => $validated['skpd_uuid'] ?? null,
        ]);

        if (!empty($validated['role'])) {
            $u->syncRoles($validated['role']);
        }

        $this->dispatch('swal',
            title: 'Profil berhasil diperbarui!',
            icon: 'success',
            toast: true,
            position: 'bottom-end',
            timer: 2500
        );

        $this->closeModal();
    }

    public function cariDataAsn(): void
    {
        if (empty($this->nik)) {
            $this->dispatch('swal', title:'Masukkan NIK dulu!', icon:'warning', toast:true, position:'bottom-end', timer:2000, keepModalOpen:true);
            return;
        }

        try {
            $api = new SikonSplpLibrary();
            $res = $api->makeApiRequest('GET', 'asn/cek-nik/' . $this->nik);
            if (($res['status'] ?? '') === 'success' && !empty($res['data'])) {
                $d = $res['data'];
                $this->name      = $d['nama'] ?? $this->name;
                $this->email     = $d['email_gov'] ?? $this->email;
                $this->skpd_uuid = Skpd::where('id', $d['unor_id'])->value('unor_induk_id');

                $this->dispatch('swal', keepModalOpen:true, title:'Data ASN berhasil ditemukan!', icon:'success', toast:true, position:'top-end', timer:2000);
            } else {
                $this->dispatch('swal', keepModalOpen:true, title:'Data ASN tidak ditemukan', icon:'error', toast:true, position:'top-end', timer:3000);
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal', keepModalOpen:true, title:'Gagal koneksi API', text:$e->getMessage(), icon:'error', toast:true, position:'top-end', timer:3000);
        }
    }

    public function render()
    {
        return view('livewire.admin.userprofile');
    }
}

