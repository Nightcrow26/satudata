@props([
  'active' => false,
  'href'   => '#',    {{-- pastikan ada default --}}
])

@php
    $classes = $active
        ? 'bg-green-100 dark:bg-teal-900 text-green-900 dark:text-green-100 border-l-4 border-teal-600'
        : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes . ' block px-3 py-2 rounded-md transition-colors']) }}>
    {{ $slot }}
</a>
