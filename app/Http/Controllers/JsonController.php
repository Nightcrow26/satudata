<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JsonController extends Controller
{
    public function index()
    {
        $datasets = Dataset::with(['skpd', 'aspek'])
            ->where('status', 'published')
            ->get();

        $result = [];

        foreach ($datasets as $ds) {
            $link = route('admin.dataset.show', $ds->id); // Halaman detail publik
            $identifier = sha1($link);
            $issuedDate = $ds->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');
            $modifiedDate = $ds->updated_at?->format('Y-m-d') ?? $issuedDate;
            $keywords = array_filter(array_map('trim', explode(',', $ds->keyword ?? '')));

            $distribution = [];

            if ($ds->excel) {
                $distribution[] = [
                    "@type" => "dcat:Distribution",
                    "downloadURL" => resolve_media_url($ds->excel),
                    "mediaType" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    "format" => "xlsx",
                    "description" => "Data utama: {$ds->nama}",
                    "title" => $ds->nama
                ];
            }

            if ($ds->metadata) {
                $distribution[] = [
                    "@type" => "dcat:Distribution",
                    "downloadURL" => resolve_media_url($ds->metadata),
                    "mediaType" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    "format" => "xlsx",
                    "description" => "Metadata dari: {$ds->nama}",
                    "title" => "Metadata - {$ds->nama}"
                ];
            }

            $result[] = [
                "@type" => "dcat:Dataset",
                "accessLevel" => "public",
                "identifier" => $identifier,
                "title" => $ds->nama,
                "description" => strip_tags($ds->deskripsi),
                "issued" => $issuedDate,
                "modified" => $modifiedDate,
                "keyword" => $keywords,
                "landingPage" => $link,
                "contactPoint" => [
                    "fn" => "Dinas Kominfo dan Persandian Kab. Hulu Sungai Utara",
                    "hasEmail" => "mailto:diskominfo@hsu.go.id"
                ],
                "publisher" => [
                    "@type" => "org:Organization",
                    "name" => optional($ds->skpd)->nama ?? 'Instansi Tidak Diketahui'
                ],
                "theme" => [ optional($ds->aspek)->nama ?? 'Umum' ],
                "distribution" => $distribution
            ];
        }

        $catalog = [
            "@context" => "https://project-open-data.cio.gov/v1.1/schema/catalog.jsonld",
            "@id" => url('/json'),
            "@type" => "dcat:Catalog",
            "conformsTo" => "https://project-open-data.cio.gov/v1.1/schema",
            "describedBy" => "https://project-open-data.cio.gov/v1.1/schema/catalog.json",
            "dataset" => $result
        ];

        return response()->json($catalog, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
