<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Skpd;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;
use App\Library\SikonSplpLibrary;
use Illuminate\Support\Facades\Storage;

// use Livewire\Attributes\Js; // tidak wajib

class Userprofile extends Component
{
    use WithFileUploads;
    
    public bool $showModal = false; // ★ state modal

    public string $user_id = '';
    public ?User  $editingUser = null;

    public string $name = '';
    public string $deskripsi = '';
    public string $email = '';
    public string $nik = '';
    public string $role = '';
    public ?string $skpd_uuid = null;
    public $sk_penunjukan = null; // File upload
    public ?string $current_sk_penunjukan = null; // Current file path

    public array $availableRoles = [];
    public $availableSkpds = [];

    protected function rules(): array 
    { 
        return [ 
            'name' => 'required|string|min:3', 
            'email' => 'required|email|unique:users,email,' . ($this->user_id ?: 'NULL') . ',id', 
            'nik' => 'nullable|string', 
            'role' => 'nullable|exists:roles,name', 
            'skpd_uuid' => 'nullable|exists:skpd,id',
            'sk_penunjukan' => 'nullable|file|mimes:pdf|max:5120', // Max 5MB 
        ]; 
    }

    public function mount(): void
    {
        $u = auth()->user();
        $this->loadUser($u->id);
        $this->availableRoles = Role::pluck('name')->toArray();
        $this->availableSkpds = Skpd::orderBy('nama')
            ->whereColumn('id', 'unor_induk_id')
            ->get();
    }

    private function loadUser(string $id): void
    {
        $u = User::findOrFail($id);
        $this->user_id   = (string) $u->id;
        $this->editingUser = $u;

        $this->name      = $u->name;
        $this->email     = $u->email;
        $this->nik       = $u->nik ?? '';
        $this->skpd_uuid = $u->skpd_uuid;
        $this->role      = $u->getRoleNames()->first() ?? '';
        $this->current_sk_penunjukan = $u->sk_penunjukan;
    }

    #[On('profile:open')] // ★ dengarkan event dari header
    public function showProfileModal(): void
    {
        $this->loadUser((string) auth()->id());
        $this->showModal = true; // ★ buka modal
        
        // Update Tom Select options dan set selected values dengan delay yang lebih lama
        $this->js("
            setTimeout(() => {
                
                // Update Produsen Data options dengan format [{id, text}]
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
            }, 1000);
            
            // Fallback dengan delay lebih lama
            setTimeout(() => {
                
                // Second attempt for Produsen Data
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'skpd_uuid',
                        options: " . json_encode($this->availableSkpds->map(function($skpd) {
                            return ['id' => $skpd->id, 'text' => $skpd->nama];
                        })->values()) . ",
                        value: '" . $this->skpd_uuid . "'
                    }
                }));
                
                // Second attempt for role  
                window.dispatchEvent(new CustomEvent('tom-update', {
                    detail: {
                        target: 'role',
                        options: " . json_encode(collect($this->availableRoles)->map(function($role) {
                            return ['id' => $role, 'text' => ucfirst($role)];
                        })->values()) . ",
                        value: '" . $this->role . "'
                    }
                }));
            }, 2000);
        ");
    }

    public function closeModal(): void
    {
        $this->showModal = false; // ★ tutup modal
    }

    public function saveUser(): void
    {
        $validated = $this->validate();
        if (auth()->id() === $this->editingUser?->id) unset($validated['role']);

        $u = User::findOrFail($this->user_id);
        
        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'nik'       => $validated['nik'] ?? null,
            'skpd_uuid' => $validated['skpd_uuid'] ?? null,
        ];

        // Handle SK Penunjukan upload
        if ($this->sk_penunjukan) {
            // Delete old file if exists
            if ($u->sk_penunjukan) {
                Storage::disk('s3')->delete($u->sk_penunjukan);
            }
            
            // Generate filename like dataset pattern
            $originalName = $this->sk_penunjukan->getClientOriginalName();
            $extension = $this->sk_penunjukan->getClientOriginalExtension();
            $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;
            
            // Store file and save path only (not URL) - sama seperti dataset
            $path = $this->sk_penunjukan->storeAs('sk-penunjukan', $filename, ['disk' => 's3', 'visibility' => 'public']);
            $updateData['sk_penunjukan'] = $path;
        }

        $u->update($updateData);
        if (!empty($validated['role'])) $u->syncRoles($validated['role']);

        // opsional: toast
        $this->dispatch('swal', title:'Profil berhasil diperbarui!', icon:'success', toast:true, position:'bottom-end', timer:2500);

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
