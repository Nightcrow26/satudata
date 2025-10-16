<?php

namespace App\Livewire\Admin;

use App\Models\UserSurvey;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;


#[Title('Survey Pengguna')]
class SurveyCrud extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';
    public int $perPage = 10;

    public bool $showDeleteModal = false;

    // Info untuk modal hapus
    public string $deleteId = '';
    public string $preview = '';

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $surveys = UserSurvey::query()
            ->when($this->search !== '', function ($q) {
                $s = "%{$this->search}%";
                // Jika pakai Postgres, Anda bisa ganti menjadi ilike
                $q->where('session_id', 'like', $s)
                  ->orWhere('ip_address', 'like', $s)
                  ->orWhere('user_agent', 'like', $s)
                  ->orWhere('feedback', 'like', $s)
                  ->orWhere('rating', 'like', $s);
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage)
            ->onEachSide(1);

        // Calculate statistics
        $totalSurveys = UserSurvey::count();
        $averageRating = UserSurvey::avg('rating');

        return view('livewire.admin.survey-crud', compact('surveys', 'totalSurveys', 'averageRating'));
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteId = $id;

        $survey = UserSurvey::findOrFail($id);
        $this->preview = sprintf(
            'Rating %s — IP %s — %s',
            $survey->rating,
            $survey->ip_address,
            Str::limit((string) $survey->feedback, 60)
        );

        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        UserSurvey::findOrFail($this->deleteId)->delete();

        $this->dispatch('swal',
            title: 'Data survey berhasil dihapus!',
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
}