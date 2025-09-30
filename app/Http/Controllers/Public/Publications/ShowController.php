<?php

namespace App\Http\Controllers\Public\Publications;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShowController extends Controller
{
    public function __invoke(string $publication)
    {
        $slug = $publication;
        $disk = Storage::disk('public');
        $demoPath = 'publications/sample.pdf'; // <- sesuaikan dengan folder kamu (bukan "publikasi")

        $exists = $disk->exists($demoPath);
        $size = $exists ? (int) $disk->size($demoPath) : 0;

        $pub = (object) [
            'title' => Str::of($slug)->headline(),
            'abstract' => 'Buku ini menekankan pentingnya',
            'authors' => ['Tim Publikasi'],
            'year' => now()->year,
            'categories' => ['Contoh', 'Demo'],
            'agency' => (object) ['name' => 'Diskominfo', 'logo_url' => null],

            // Simpan path RELATIF terhadap disk 'public'
            'file_path' => $exists ? $demoPath : null,
            'file_size' => $size,
            'pages' => 24,

            'doi' => null,
            'external_url' => null,
            'views' => 1234,
            'downloads' => 89,
        ];

        return view('public.publications.show', [
            'publication' => $pub,
            'fileSize' => $this->humanFileSize($size),
        ]);
    }

    private function humanFileSize(int $bytes, int $decimals = 1): string
    {
        if ($bytes <= 0) {
            return '—';
        }
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).' '.$units[$factor - 1];
    }
}
