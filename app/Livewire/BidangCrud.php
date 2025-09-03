<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Bidang;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;

#[Title('Bidang')]
class BidangCrud extends Component
{
    use WithPagination;

    // URL search & pagination
    #[Url(except: '')]
    public string $search = '';
    public int $perPage = 10;

    // Modal state
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // Data field
    public string $bidang_id = '';
    public string $kode_bidang = '';
    public string $uraian_bidang = '';

    public string $deleteId = '';

    protected function rules(): array
    {
        return [
            'kode_bidang' => 'required|string',
            'uraian_bidang' => 'required|string',
        ];
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $bidangs = Bidang::query()
            ->when($this->search !== '', fn($q) =>
                $q->where('kode_bidang', 'like', "%{$this->search}%")
                  ->orWhere('uraian_bidang', 'ilike', "%{$this->search}%")
            )
            ->orderBy('kode_bidang')
            ->paginate($this->perPage)
            ->onEachSide(1);

        return view('livewire.bidang', compact('bidangs'));
    }

    private function resetInput(): void
    {
        $this->reset(['bidang_id', 'kode_bidang', 'uraian_bidang']);
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        $this->showModal = true;

        $this->dispatch('show-modal', id: 'bidang-modal');
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $bidang = Bidang::findOrFail($id);
        $this->bidang_id = $bidang->id;
        $this->kode_bidang = $bidang->kode_bidang;
        $this->uraian_bidang = $bidang->uraian_bidang;

        $this->showModal = true;
        $this->dispatch('show-modal', id: 'bidang-modal');
    }

    public function saveBidang(): void
    {
        $validated = $this->validate();

        if ($this->bidang_id) {
            Bidang::findOrFail($this->bidang_id)->update($validated);
        } else {
            Bidang::create(array_merge(
                ['id' => (string) Str::uuid()],
                $validated
            ));
        }

        $this->dispatch('swal',
            title: $this->bidang_id
                ? 'Bidang berhasil diperbarui!'
                : 'Bidang berhasil dibuat!',
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
        $this->dispatch('hide-modal', id: 'aspek-modal');
        $this->resetInput();
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('show-modal', id: 'delete-modal');
    }

    public function deleteBidangConfirmed(): void
    {
        Bidang::findOrFail($this->deleteId)->delete();

        $this->dispatch('swal',
            title: 'Bidang berhasil dihapus!',
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
