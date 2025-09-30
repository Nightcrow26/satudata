<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Walidata;
use App\Models\Bidang;
use App\Models\Indikator;

class SinkronWalidata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'walidata:sync 
                            {tahun=2024 : Tahun data yang akan disinkronisasi}
                            {--batch-size=50 : Jumlah data per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data walidata dari API SIPD';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = $this->argument('tahun');
        $batchSize = $this->option('batch-size');
        
        $this->info("Memulai sinkronisasi walidata tahun {$tahun}...");
        
        try {
            // Fetch data dari API
            $this->info('Mengambil data dari API SIPD...');
            $response = Http::withToken('d71e88f811fdf46c2d3afc5ab7a3c41b')
                ->timeout(120)
                ->get('https://sipd.go.id/ewalidata/serv/get_dssd_final', [
                    'kodepemda' => '6308',
                    'tahun'     => $tahun,
                ]);

            if ($response->failed()) {
                $this->error("API error: " . $response->status());
                return Command::FAILURE;
            }

            $data = $response->json();
            if (!is_array($data)) {
                $this->error("Format data tidak valid");
                return Command::FAILURE;
            }

            $totalData = count($data);
            $this->info("Total data yang akan diproses: {$totalData}");

            // Load existing data
            $this->info('Loading existing data...');
            $existingBidangs = Bidang::pluck('id', 'kode_bidang')->toArray();
            $existingIndikators = Indikator::pluck('id', 'kode_indikator')->toArray();
            $existingWalidatas = Walidata::pluck('id', 'id')->toArray();

            // Process dalam batch
            $dataChunks = array_chunk($data, $batchSize);
            $totalBatches = count($dataChunks);
            $sukses = 0;

            $this->info("Memproses {$totalBatches} batch dengan ukuran {$batchSize}...");
            
            $progressBar = $this->output->createProgressBar($totalData);
            $progressBar->start();

            foreach ($dataChunks as $batchIndex => $chunk) {
                foreach ($chunk as $item) {
                    // Sinkron bidang
                    $bidangId = $existingBidangs[$item['kodebidang']] ?? null;
                    if (!$bidangId) {
                        $bidang = Bidang::create([
                            'id'            => (string) Str::uuid(),
                            'kode_bidang'   => $item['kodebidang'],
                            'uraian_bidang' => $item['uraibidang'] ?? null,
                        ]);
                        $existingBidangs[$item['kodebidang']] = $bidang->id;
                        $bidangId = $bidang->id;
                    }

                    // Sinkron indikator
                    $indikatorId = $existingIndikators[$item['kodeindikator']] ?? null;
                    if (!$indikatorId) {
                        $indikator = Indikator::create([
                            'id'                => (string) Str::uuid(),
                            'kode_indikator'    => $item['kodeindikator'],
                            'uraian_indikator'  => $item['uraian_indikator'] ?? null,
                            'bidang_id'         => $bidangId,
                        ]);
                        $existingIndikators[$item['kodeindikator']] = $indikator->id;
                        $indikatorId = $indikator->id;
                    }

                    // Parse timestamp
                    $apiTimestamp = $item['tanggal_verifikasi_pembinadata']
                        ?? $item['tanggal_verifikasi_walidata']
                        ?? null;
                    $parsedTimestamp = $apiTimestamp ? Carbon::parse($apiTimestamp) : now();

                    // Sinkron walidata
                    $walidataExists = isset($existingWalidatas[$item['idtransaksi']]);
                    
                    if ($walidataExists) {
                        Walidata::where('id', $item['idtransaksi'])->update([
                            'satuan'       => $item['satuan'] ?? '',
                            'tahun'        => $item['tahun'] ?? $tahun,
                            'data'         => $item['data'] ?? '0',
                            'indikator_id' => $indikatorId,
                            'bidang_id'    => $bidangId,
                            'verifikasi_data' => $parsedTimestamp,
                        ]);
                    } else {
                        Walidata::create([
                            'id'           => $item['idtransaksi'],
                            'satuan'       => $item['satuan'] ?? '',
                            'tahun'        => $item['tahun'] ?? $tahun,
                            'data'         => $item['data'] ?? '0',
                            'indikator_id' => $indikatorId,
                            'bidang_id'    => $bidangId,
                            'created_at'   => $parsedTimestamp,
                            'verifikasi_data' => $parsedTimestamp,
                        ]);
                        $existingWalidatas[$item['idtransaksi']] = true;
                    }

                    $sukses++;
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->newLine();
            $this->info("Sinkronisasi selesai! Total data berhasil: {$sukses}");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}