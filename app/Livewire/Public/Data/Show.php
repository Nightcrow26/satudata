<?php

namespace App\Livewire\Public\Data;

use App\Models\Dataset;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Show extends Component
{
    public Dataset $dataset;
    
    public function mount(Dataset $dataset): void
    {
        // Load dataset dengan relasi yang diperlukan
        $this->dataset = $dataset->load(['skpd', 'aspek', 'user']);
        
        // Use ViewTracker trait untuk increment view dengan session tracking
        $this->dataset->incrementViewIfNotSeen();
    }

    /**
     * Format file size to human readable format
     */
    public function formatFileSize($filePath): string
    {
        if (!$filePath) {
            return '-';
        }

        try {
            // Try to get file size from S3
            $size = Storage::disk('s3')->size($filePath);
            
            if ($size === false || $size === null) {
                return 'Tidak diketahui';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $unitIndex = 0;
            
            while ($size >= 1024 && $unitIndex < count($units) - 1) {
                $size /= 1024;
                $unitIndex++;
            }
            
            return round($size, 1) . ' ' . $units[$unitIndex];
        } catch (\Exception $e) {
            return 'Tidak diketahui';
        }
    }

    public function render()
    {
        return view('livewire.public.data.show')
            ->title($this->dataset->nama . ' - Data')
            ->layout('components.layouts.public');
    }
}