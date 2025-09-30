@props([
'org' => 'Dinas Komunikasi, Informatika dan Persandian Kab. HSU',
'year' => now()->year,
])

<footer class="bg-teal-700 dark:bg-teal-800 text-white transition-colors duration-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 text-center">
        <p class="text-sm sm:text-base font-semibold">
            Copyright {{ $year }} - {{ $org }}
        </p>
    </div>
</footer>
