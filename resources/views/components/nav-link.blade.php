@props([
  'active' => false,
  'href'   => '#',    {{-- pastikan ada default --}}
])

@php
    $classes = $active
        ? 'nav-link active rounded'
        : 'nav-link';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
