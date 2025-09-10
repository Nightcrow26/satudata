<?php

namespace App\Livewire\Admin;

use App\Models\Indikator;
use App\Models\Bidang;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
 
#[title('Indikator')]
class IndikatorCrud extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\Url(except: '')]
    public string $search = '';
    public int $perPage = 10;

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public string $indikator_id = '';
    public string $kode_indikator = '';
    public string $uraian_indikator = '';
    public string $bidang_id = '';

    public string $deleteId = '';

    public function updatedPerPage() { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'kode_indikator' => 'required|string',
            'uraian_indikator' => 'required|string',
            'bidang_id' => 'required|uuid|exists:bidangs,id',
        ];
    }

    public function render()
    {
        $indikators = Indikator::query()
            ->when($this->search !== '', fn($q) =>
                $q->where('kode_indikator', 'ilike', "%{$this->search}%")
                  ->orWhere('uraian_indikator', 'ilike', "%{$this->search}%")
            )
            ->orderBy('kode_indikator')
            ->with('bidang')
            ->paginate($this->perPage)
            ->onEachSide(1);

        $bidangs = Bidang::orderBy('kode_bidang')->get();

        return view('livewire.admin.indikator', compact('indikators', 'bidangs'));
    }

    private function resetInput(): void
    {
        $this->reset(['indikator_id', 'kode_indikator', 'uraian_indikator', 'bidang_id']);
    }

    public function showCreateModal(): void
    {
        $this->resetValidation();
        $this->resetInput();
        $this->showModal = true;

        $this->dispatch('show-modal', id: 'indikator-modal');
    }

    public function showEditModal(string $id): void
    {
        $this->resetValidation();
        $this->resetInput();

        $indikator = Indikator::findOrFail($id);
        $this->indikator_id = $indikator->id;
        $this->kode_indikator = $indikator->kode_indikator;
        $this->uraian_indikator = $indikator->uraian_indikator;
        $this->bidang_id = $indikator->bidang_id;

        $this->showModal = true;
        $this->dispatch('show-modal', id: 'indikator-modal');
    }

    public function saveIndikator(): void
    {
        $validated = $this->validate();

        if ($this->indikator_id) {
            Indikator::findOrFail($this->indikator_id)->update($validated);
        } else {
            Indikator::create(array_merge(['id' => (string) Str::uuid()], $validated));
        }

        $this->dispatch('swal',
            title: $this->indikator_id
                ? 'Indikator berhasil diperbarui!'
                : 'Indikator berhasil dibuat!',
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
        $this->dispatch('hide-modal', id: 'indikator-modal');
        $this->resetInput();
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('show-modal', id: 'delete-modal');
    }

    public function deleteIndikatorConfirmed(): void
    {
        Indikator::findOrFail($this->deleteId)->delete();

        $this->dispatch('swal',
            title: 'Indikator berhasil dihapus!',
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
