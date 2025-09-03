<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
#[Layout('components.layouts.guest')]

#[Title('Home')]
class HomeSlider extends Component
{
    // Data dummy; silakan ganti dengan query Eloquent sesuai kebutuhan
    public $dataTerbaru = [
        [
            'kategori'     => 'Kesehatan',
            'instansi'      => 'Dinkes',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Jumlah Peserta KB Baru Pertahun 2025',
            'sub_indikator' => 5,
            'icon'          => '🩺',
        ],
        [
            'kategori'     => 'Teknologi',
            'instansi'      => 'Diskominfo',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Jumlah BTS di Kab. HSU Per Tahun 2025',
            'sub_indikator' => 112,
            'icon'          => '💻',
        ],
        [
            'kategori'     => 'Infrastruktur',
            'instansi'      => 'Dinkes',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Data Jalan dalam Kondisi Mantap Per Tahun 2025',
            'sub_indikator' => 82,
            'icon'          => '🚧',
        ],
        [
            'kategori'     => 'Sosial & Lingkungan',
            'instansi'      => 'Dinsos',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Jumlah Peserta KB Baru Pertahun 2025',
            'sub_indikator' => 112,
            'icon'          => '🌳',
        ],
        [
            'kategori'     => 'Ekonomi',
            'instansi'      => 'Disperindag',
            'tanggal'       => '18 Januari 2025',
            'judul'         => 'Pertumbuhan UMKM Kab. HSU 2025',
            'sub_indikator' => 23,
            'icon'          => '💼',
        ],
        [
            'kategori'     => 'Pendidikan',
            'instansi'      => 'Disdikpora',
            'tanggal'       => '19 Januari 2025',
            'judul'         => 'Rasio Guru dan Siswa 2025',
            'sub_indikator' => 10,
            'icon'          => '🎓',
        ],
        // … tambahkan data sesuai kebutuhan …
    ];

    public $publikasiTerbaru = [
        [
            'kategori'     => 'Umum',
            'instansi'      => 'Dinkes',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Laporan Tahunan Kesehatan 2025',
            'sub_indikator' => 1,
            'icon'          => '📄',
        ],
        [
            'kategori'     => 'Kesehatan',
            'instansi'      => 'Dinkes',
            'tanggal'       => '17 Januari 2025',
            'judul'         => 'Buku Panduan Pencegahan Penyakit',
            'sub_indikator' => 1,
            'icon'          => '📄',
        ],
        [
            'kategori'     => 'Teknologi',
            'instansi'      => 'Diskominfo',
            'tanggal'       => '18 Januari 2025',
            'judul'         => 'Pedoman Smart City 2025',
            'sub_indikator' => 1,
            'icon'          => '📄',
        ],
        [
            'kategori'     => 'Pendidikan',
            'instansi'      => 'Disdikpora',
            'tanggal'       => '19 Januari 2025',
            'judul'         => 'Kurikulum Digital 2025',
            'sub_indikator' => 1,
            'icon'          => '📄',
        ],
        // … tambahkan data sesuai kebutuhan …
    ];

    // Jadwal rilis dummy
    public $jadwalData2025 = [
        ['judul' => 'Data Jumlah BTS di Kab. HSU Tahun 2025', 'rilis' => 'Mei 2025'],
        ['judul' => 'Data Pertumbuhan UMKM 2025', 'rilis' => 'Juni 2025'],
        ['judul' => 'Data Rasio Guru dan Siswa 2025', 'rilis' => 'Juli 2025'],
        ['judul' => 'Data Indeks Pembangunan Manusia 2025', 'rilis' => 'Agustus 2025'],
        ['judul' => 'Data Ketersediaan Air Bersih 2025', 'rilis' => 'September 2025'],
        ['judul' => 'Data Infrastruktur Jalan 2025', 'rilis' => 'Oktober 2025'],
        ['judul' => 'Data Listrik dan Energi 2025', 'rilis' => 'November 2025'],
        ['judul' => 'Data Parlayang 2025', 'rilis' => 'Desember 2025'],
        ['judul' => 'Data Kesehatan Masyarakat 2025', 'rilis' => 'Desember 2025'],
    ];

    public $jadwalPublikasi2025 = [
        ['judul' => 'Laporan Tahunan Kesehatan 2025', 'rilis' => 'Mei 2025'],
        ['judul' => 'Pedoman Smart City 2025', 'rilis' => 'Juni 2025'],
        ['judul' => 'Laporan Ekonomi Daerah 2025', 'rilis' => 'Juli 2025'],
        ['judul' => 'Kajian Pendidikan 2025', 'rilis' => 'Agustus 2025'],
        ['judul' => 'Laporan Infrastruktur 2025', 'rilis' => 'September 2025'],
        ['judul' => 'Publikasi Lingkungan 2025', 'rilis' => 'Oktober 2025'],
        ['judul' => 'Analisis Sosial 2025', 'rilis' => 'November 2025'],
        ['judul' => 'Laporan Teknologi Pemerintah 2025', 'rilis' => 'Desember 2025'],
        ['judul' => 'Publikasi Budaya 2025', 'rilis' => 'Desember 2025'],
    ];

    public function render()
    {
        return view('livewire.home-slider');
    }
}
