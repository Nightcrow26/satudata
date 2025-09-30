<?php

namespace App\Livewire\Public;

use App\Models\UserSurvey;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DownloadSurveyModal extends Component
{
    public $isOpen = false;
    public $rating = 0;
    public $feedback = '';
    public $downloadUrl = '';
    public $downloadType = 'dataset'; // dataset, walidata, publication
    public $itemId = null;
    public $isSubmitting = false;

    protected $listeners = [
        'openSurveyModal' => 'openModal'
    ];

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'nullable|string|max:1000'
    ];

    protected $messages = [
        'rating.required' => 'Silakan berikan rating untuk website ini.',
        'rating.min' => 'Rating minimal 1 bintang.',
        'rating.max' => 'Rating maksimal 5 bintang.',
        'feedback.max' => 'Masukan maksimal 1000 karakter.'
    ];

    public function mount()
    {
        // Check if we should show modal based on session flash
        if (session('show_survey')) {
            $this->downloadUrl = session('download_url', '');
            $this->isOpen = true;
        }
    }

    public function openModal($downloadUrl, $downloadType = 'dataset', $itemId = null)
    {
        $this->downloadUrl = $downloadUrl;
        $this->downloadType = $downloadType;
        $this->itemId = $itemId;
        $this->isOpen = true;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function submitSurvey()
    {
        $this->isSubmitting = true;
        
        $this->validate();

        try {
            // Create survey record
            UserSurvey::createSurvey([
                'session_id' => Session::getId(),
                'ip_address' => request()->ip(),
                'rating' => $this->rating,
                'feedback' => $this->feedback,
                'user_agent' => request()->userAgent(),
            ]);

            // Mark user as having completed survey for this session
            Session::put('survey_completed', true);

            // Close modal
            $this->isOpen = false;

            // Redirect to download
            $this->redirect($this->downloadUrl);

        } catch (\Exception $e) {
            $this->addError('submit', 'Terjadi kesalahan saat menyimpan survey. Silakan coba lagi.');
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['rating', 'feedback', 'downloadUrl', 'downloadType', 'itemId']);
    }

    public function render()
    {
        return view('livewire.public.download-survey-modal');
    }
}
