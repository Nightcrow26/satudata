<div>
    <x-public.home.hero bg-image="{{ asset('images/public/home/hero_banner.jpg') }}" title="Satu" accent="Data"
        subtitle="Kebutuhan Data Terintegrasi dalam Satu Wadah · Pencarian Data HSU Lebih Mudah!" />

    <x-public.home.stats :items="[
        ['icon' => 'database',  'value' => $datasetCount, 'label' => 'Data'],
        ['icon' => 'book-open', 'value' => $publikasiCount, 'label' => 'Publikasi'],
        ['icon' => 'landmark',  'value' => $instansiCount, 'label' => 'Instansi'],
    ]" overlap />

    <livewire:public.home.search />

    <x-public.home.latest-datasets :items="$latestData" :more-url="route('public.data.index')" />

    <x-public.home.latest-publications :items="$latestPublikasi" :more-url="route('public.publications.index')" />

    <x-public.home.latest-indikators :items="$latestIndikator"/>


    <x-public.home.release-schedule :year="2025" :data-items="$latestData" :pub-items="$latestPublikasi" more-data-url="#"
        more-pub-url="#" />
</div>
